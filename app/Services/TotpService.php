<?php

namespace App\Services;

use Illuminate\Support\Str;

class TotpService
{
    private const BASE32_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Genera un secreto Base32 criptográficamente seguro (16 caracteres).
     */
    public function generateSecret(int $length = 16): string
    {
        $secret = '';
        $charCount = strlen(self::BASE32_CHARS);
        $randomBytes = random_bytes($length);

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::BASE32_CHARS[ord($randomBytes[$i]) % $charCount];
        }

        return $secret;
    }

    /**
     * Genera el URI estandarizado otpauth:// para escanear en Google Authenticator.
     */
    public function getOtpAuthUri(string $company, string $holder, string $secret): string
    {
        $companyEncoded = rawurlencode($company);
        $holderEncoded = rawurlencode($holder);

        return "otpauth://totp/{$companyEncoded}:{$holderEncoded}?secret={$secret}&issuer={$companyEncoded}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Genera una URL de código QR para escanear en aplicaciones móviles de 2FA.
     */
    public function getQrCodeUrl(string $company, string $holder, string $secret): string
    {
        $otpauth = $this->getOtpAuthUri($company, $holder, $secret);

        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='.rawurlencode($otpauth);
    }

    /**
     * Valida un código TOTP de 6 dígitos bajo el estándar RFC 6238.
     *
     * @param  string  $secret  Secreto Base32
     * @param  string  $code  Código de 6 dígitos proporcionado por el usuario
     * @param  int  $discrepancy  Ventana de tolerancia (±1 = ±30 segundos para desfase de reloj)
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool
    {
        $code = trim($code);
        if (strlen($code) !== 6 || ! ctype_digit($code)) {
            return false;
        }

        $currentTimeSlice = (int) floor(time() / 30);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->calculateCode($secret, $currentTimeSlice + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Calcula el código TOTP para un intervalo de tiempo específico.
     */
    public function calculateCode(string $secret, int $timeSlice): string
    {
        $secretKey = $this->base32Decode($secret);

        // Empaquetar el contador de tiempo en formato binario de 64 bits Big-Endian
        $timeBytes = pack('N*', 0).pack('N*', $timeSlice);

        // Calcular HMAC-SHA1
        $hmac = hash_hmac('sha1', $timeBytes, $secretKey, true);

        // Truncamiento dinámico según RFC 4226 / 6238
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);

        $value = unpack('N', $hashPart)[1] & 0x7FFFFFFF;
        $modulo = $value % 1000000;

        return str_pad((string) $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Genera códigos de recuperación de un solo uso.
     *
     * @return array<string>
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(4).'-'.Str::random(4));
        }

        return $codes;
    }

    /**
     * Decodifica una cadena Base32 a binario nativo.
     */
    private function base32Decode(string $b32): string
    {
        $b32 = strtoupper(trim($b32));
        $buffer = 0;
        $bitsLeft = 0;
        $binary = '';

        for ($i = 0; $i < strlen($b32); $i++) {
            $char = $b32[$i];
            $val = strpos(self::BASE32_CHARS, $char);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $binary .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $binary;
    }
}
