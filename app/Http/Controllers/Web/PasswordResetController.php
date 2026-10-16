<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Muestra la pantalla para solicitar recuperación de contraseña.
     */
    public function showForgotForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Genera y envía el código OTP de 6 dígitos al correo enlazado del usuario.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'identificador' => ['required', 'string'],
        ], [
            'identificador.required' => 'Ingresa tu nombre de usuario o tu correo electrónico.',
        ]);

        $input = trim($request->input('identificador'));
        $throttleKey = 'otp-request:'.Str::transliterate(Str::lower($input)).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 4)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()->withInput()->withErrors([
                'identificador' => "Demasiadas solicitudes. Por favor espera {$minutes} minuto(s) antes de intentar nuevamente.",
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        // Buscar usuario por nombre_usuario o por email de su persona vinculada
        $usuario = Usuario::with('persona')
            ->where('nombre_usuario', $input)
            ->orWhereHas('persona', function ($q) use ($input) {
                $q->where('email', $input);
            })
            ->first();

        if (! $usuario) {
            // Mitigación anti-enumeración de usuarios (OWASP CWE-204)
            $fallbackEmail = filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : 'usuario@sistema.local';
            session([
                'password_reset_email' => $fallbackEmail,
                'password_reset_username' => $input,
            ]);

            return redirect()->route('password.otp')
                ->with('status', 'Si los datos ingresados corresponden a una cuenta activa con correo vinculado, hemos enviado un código de verificación OTP de 6 dígitos.');
        }

        $email = $usuario->persona?->email;

        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Mitigación anti-enumeración de usuarios: no revelar si la cuenta carece de correo
            session([
                'password_reset_email' => 'usuario@sistema.local',
                'password_reset_username' => $usuario->nombre_usuario,
            ]);

            return redirect()->route('password.otp')
                ->with('status', 'Si los datos ingresados corresponden a una cuenta activa con correo vinculado, hemos enviado un código de verificación OTP de 6 dígitos.');
        }

        // Generar código OTP numérico de 6 dígitos
        $otp = sprintf('%06d', random_int(100000, 999999));

        // Almacenar en password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        // Enviar correo
        try {
            $nombrePersona = trim(($usuario->persona->nombre ?? '').' '.($usuario->persona->apellido1 ?? ''));
            Mail::to($email)->send(new PasswordResetOtpMail(
                $otp,
                $usuario->nombre_usuario,
                $nombrePersona ?: $usuario->nombre_usuario,
                15
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        $sessionData = [
            'password_reset_email' => $email,
            'password_reset_username' => $usuario->nombre_usuario,
        ];

        if (app()->isLocal() && config('mail.default') === 'log') {
            $sessionData['dev_otp'] = $otp;
        }

        session($sessionData);

        return redirect()->route('password.otp')
            ->with('status', 'Si los datos ingresados corresponden a una cuenta activa con correo vinculado, hemos enviado un código de verificación OTP de 6 dígitos.');
    }

    /**
     * Muestra la pantalla para ingresar el OTP y la nueva contraseña.
     */
    public function showOtpForm(Request $request): View|RedirectResponse
    {
        $email = $request->query('email', session('password_reset_email'));

        if (! $email) {
            return redirect()->route('password.request')
                ->withErrors(['identificador' => 'Por favor indica tu usuario o correo electrónico para continuar.']);
        }

        $maskedEmail = $this->maskEmail($email);

        return view('auth.reset-password-otp', [
            'email' => $email,
            'maskedEmail' => $maskedEmail,
        ]);
    }

    /**
     * Verifica el código OTP y actualiza la contraseña del usuario.
     */
    public function resetPasswordWithOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'otp.required' => 'El código de verificación OTP es obligatorio.',
            'otp.size' => 'El código OTP debe ser de 6 dígitos.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
        ]);

        $throttleKey = 'otp-verify:'.Str::transliterate(Str::lower($request->input('email'))).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = ceil($seconds / 60);

            return back()->withInput($request->only('email'))->withErrors([
                'otp' => "Demasiados intentos fallidos. Intenta nuevamente en {$minutes} minuto(s).",
            ]);
        }

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->input('email'))
            ->first();

        if (! $record) {
            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'No existe una solicitud de restablecimiento activa o el código ya fue utilizado.',
            ]);
        }

        // Expiración a los 15 minutos
        if (Carbon::parse($record->created_at)->addMinutes(15)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();

            return redirect()->route('password.request')->withErrors([
                'identificador' => 'El código OTP ha expirado (límite de 15 minutos). Por favor solicita uno nuevo.',
            ]);
        }

        // Verificación del hash del código OTP
        if (! Hash::check($request->input('otp'), $record->token)) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withInput($request->only('email'))->withErrors([
                'otp' => 'El código OTP ingresado es incorrecto. Verifica el correo recibido.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Buscar usuario y actualizar su contraseña
        $usuario = Usuario::whereHas('persona', function ($q) use ($request) {
            $q->where('email', $request->input('email'));
        })->first();

        if (! $usuario) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'No se encontró un usuario vinculado a este correo.',
            ]);
        }

        $usuario->update([
            'password_hash' => Hash::make($request->input('password')),
        ]);

        // Invalidar token de restablecimiento y limpiar sesión
        DB::table('password_reset_tokens')->where('email', $request->input('email'))->delete();
        $request->session()->forget(['password_reset_email', 'password_reset_username', 'dev_otp']);

        return redirect()->route('login')
            ->with('status', '¡Tu contraseña ha sido restablecida exitosamente! Ya puedes iniciar sesión.');
    }

    /**
     * Reenvía un nuevo código OTP al correo.
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $email = $request->input('email', session('password_reset_email'));

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('password.request');
        }

        $throttleKey = 'otp-resend:'.Str::transliterate(Str::lower($email)).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()->withErrors([
                'resend' => "Espera {$seconds} segundos antes de solicitar otro código.",
            ]);
        }

        RateLimiter::hit($throttleKey, 60);

        $usuario = Usuario::with('persona')
            ->whereHas('persona', function ($q) use ($email) {
                $q->where('email', $email);
            })
            ->first();

        if (! $usuario) {
            return back()->with('status', 'Si la cuenta existe, hemos enviado un nuevo código OTP a tu correo.');
        }

        $otp = sprintf('%06d', random_int(100000, 999999));

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        try {
            $nombrePersona = trim(($usuario->persona->nombre ?? '').' '.($usuario->persona->apellido1 ?? ''));
            Mail::to($email)->send(new PasswordResetOtpMail(
                $otp,
                $usuario->nombre_usuario,
                $nombrePersona ?: $usuario->nombre_usuario,
                15
            ));
        } catch (\Throwable $e) {
            report($e);
        }

        if (app()->isLocal() && config('mail.default') === 'log') {
            session(['dev_otp' => $otp]);
        }

        return back()->with('status', 'Hemos enviado un nuevo código OTP a tu correo.');
    }

    /**
     * Enmascara el correo electrónico para privacidad (ej. j***n@dominio.com).
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);

        if ($len <= 2) {
            $maskedName = substr($name, 0, 1).'*';
        } else {
            $maskedName = substr($name, 0, 1).str_repeat('*', min(4, $len - 2)).substr($name, -1);
        }

        return $maskedName.'@'.$domain;
    }
}
