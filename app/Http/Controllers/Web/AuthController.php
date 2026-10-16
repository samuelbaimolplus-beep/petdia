<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\VerifyEmailMailable;
use App\Models\RegistroAcceso;
use App\Models\Usuario;
use App\Services\AuditService;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Muestra la pantalla de inicio de sesión.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Generar desafío anti-fuerza bruta si hay intentos previos
        $captchaNum1 = random_int(2, 9);
        $captchaNum2 = random_int(1, 9);
        session(['login_challenge_result' => $captchaNum1 + $captchaNum2]);

        return view('auth.login', [
            'captchaQuestion' => "{$captchaNum1} + {$captchaNum2}",
        ]);
    }

    /**
     * Procesa el inicio de sesión con rate limiting exponencial,
     * verificación de correo y soporte para 2FA TOTP.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'nombre_usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha_answer' => ['nullable', 'numeric'],
        ], [
            'nombre_usuario.required' => 'El nombre de usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.',
        ]);

        $username = trim($credentials['nombre_usuario']);
        $throttleKey = 'login:'.Str::transliterate(Str::lower($username)).'|'.$request->ip();
        $lockoutCountKey = 'login_lockout_count:'.Str::transliterate(Str::lower($username)).'|'.$request->ip();

        // 1. Verificación de bloqueo por Rate Limiting
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $minutes = (int) ceil($seconds / 60);

            Log::alert("ALERTA DE SEGURIDAD: Intento de acceso a cuenta bloqueada por rate limiting. Usuario: '{$username}' desde IP: {$request->ip()}.");

            return back()
                ->withInput($request->only('nombre_usuario'))
                ->withErrors([
                    'lockout' => "Demasiados intentos fallidos. Por seguridad, la cuenta está bloqueada temporalmente. Espera {$minutes} minuto(s) antes de intentar nuevamente.",
                ]);
        }

        $attempts = RateLimiter::attempts($throttleKey);

        // 2. Verificación de desafío de seguridad tras 3 intentos consecutivos
        if ($attempts >= 3) {
            $expectedResult = session('login_challenge_result');
            $userAnswer = $request->input('captcha_answer');

            if ($expectedResult === null || $userAnswer === null || (int) $userAnswer !== (int) $expectedResult) {
                return back()
                    ->withInput($request->only('nombre_usuario'))
                    ->withErrors([
                        'captcha_answer' => 'Por favor resuelve correctamente el desafío de seguridad anti-fuerza bruta.',
                        'show_captcha' => true,
                    ]);
            }
        }

        // 3. Búsqueda y autenticación en servidor
        $user = Usuario::with(['persona', 'roles'])->where('nombre_usuario', $username)->first();

        // Si el usuario no existe o la contraseña no coincide -> Error genérico
        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            $currentAttempts = $attempts + 1;

            // Bloqueo exponencial tras 5 intentos: 1 min, 2 min, 4 min, 8 min...
            if ($currentAttempts >= 5) {
                $consecutiveLockouts = (int) Cache::get($lockoutCountKey, 0) + 1;
                Cache::put($lockoutCountKey, $consecutiveLockouts, now()->addHours(24));

                $decaySeconds = 60 * (2 ** min($consecutiveLockouts - 1, 3)); // 60s, 120s, 240s, 480s
                RateLimiter::hit($throttleKey, $decaySeconds);

                Log::alert("ALERTA DE SEGURIDAD: Ataque de fuerza bruta bloqueado. Usuario: '{$username}' desde IP: {$request->ip()}. Bloqueo aplicado: {$decaySeconds} segundos.");

                $minutes = (int) ceil($decaySeconds / 60);

                return back()
                    ->withInput($request->only('nombre_usuario'))
                    ->withErrors([
                        'lockout' => "Acceso bloqueado por seguridad tras 5 intentos fallidos. Intenta nuevamente en {$minutes} minuto(s).",
                    ]);
            }

            RateLimiter::hit($throttleKey, 300);

            Log::warning("Fallo de autenticación para usuario '{$username}' desde IP: {$request->ip()}. Intento fallido #{$currentAttempts}.");

            AuditService::log(
                'LOGIN_FALLIDO',
                'AUTENTICACION',
                ['nombre_usuario' => $username, 'intento' => $currentAttempts],
                null,
                null,
                'FALLIDO',
                "Intento fallido de inicio de sesión para el usuario '{$username}'"
            );

            $remaining = RateLimiter::remaining($throttleKey, 5);

            return back()
                ->withInput($request->only('nombre_usuario'))
                ->withErrors([
                    // Mensaje genérico de seguridad: nunca revelar si el usuario o contraseña es el incorrecto
                    'credenciales' => 'Credenciales incorrectas. Verifique su usuario y contraseña.',
                    'attempts' => $remaining > 0 ? "Te quedan {$remaining} intento(s) antes del bloqueo de seguridad." : null,
                    'show_captcha' => $currentAttempts >= 3,
                ]);
        }

        // 4. Verificación de estado activo de la cuenta
        if ($user->estado !== 'ACTIVO') {
            Log::warning("Intento de acceso a cuenta inactiva: '{$username}' desde IP {$request->ip()}.");

            AuditService::log(
                'LOGIN_BLOQUEADO',
                'AUTENTICACION',
                ['nombre_usuario' => $username, 'motivo' => 'Cuenta inactiva'],
                $user,
                $user->id_usuario,
                'RECHAZADO',
                "Intento de inicio de sesión en cuenta inactiva '{$username}'"
            );

            return back()
                ->withInput($request->only('nombre_usuario'))
                ->withErrors([
                    'credenciales' => 'Esta cuenta se encuentra inactiva. Comunícate con un administrador de la clínica.',
                ]);
        }

        // 5. Verificación de Correo Electrónico obligatorio mediante Código OTP
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('activacion.form')
                ->with('nombre_usuario', $user->nombre_usuario)
                ->with('warning', 'Tu cuenta requiere ser activada. Ingresa tu código OTP recibido por correo para completar la activación.');
        }

        $remember = $request->boolean('remember');

        // 6. Autenticación de Dos Factores (2FA TOTP)
        if ($user->hasTwoFactorEnabled()) {
            $request->session()->put('auth_2fa_user_id', $user->id_usuario);
            $request->session()->put('auth_2fa_remember', $remember);

            return redirect()->route('login.2fa');
        }

        // 7. Inicio de sesión exitoso sin 2FA
        Auth::login($user, $remember);
        RateLimiter::clear($throttleKey);
        Cache::forget($lockoutCountKey);

        // Regeneración y rotación de sesión para mitigar session fixation
        $request->session()->regenerate(true);
        $request->session()->forget(['demo_role', 'login_challenge_result']);
        $request->session()->put('auth_last_activity', time());
        $request->session()->put('auth_session_created_at', time());

        $user->update(['ultimo_acceso' => now()]);
        $this->registrarInicioSesion($user, $request);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Muestra la pantalla de ingreso del código 2FA TOTP.
     */
    public function showTwoFactor(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth_2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Valida el código TOTP o código de recuperación para completar el login.
     */
    public function verifyTwoFactor(Request $request, TotpService $totpService): RedirectResponse
    {
        $userId = $request->session()->get('auth_2fa_user_id');
        if (! $userId) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['required', 'string'],
        ], [
            'code.required' => 'Ingresa el código de 6 dígitos de tu aplicación autenticadora o un código de recuperación.',
        ]);

        /** @var Usuario|null $user */
        $user = Usuario::find($userId);
        if (! $user) {
            return redirect()->route('login');
        }

        $inputCode = trim($request->input('code'));
        $isValid = false;

        // Comprobar TOTP de 6 dígitos
        if ($totpService->verifyCode($user->two_factor_secret, $inputCode)) {
            $isValid = true;
        } else {
            // Comprobar código de recuperación
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];
            $upperCode = strtoupper($inputCode);

            if (($key = array_search($upperCode, $recoveryCodes, true)) !== false) {
                unset($recoveryCodes[$key]);
                $user->update(['two_factor_recovery_codes' => array_values($recoveryCodes)]);
                $isValid = true;
            }
        }

        if (! $isValid) {
            return back()->withErrors([
                'code' => 'El código 2FA ingresado es incorrecto o ha expirado. Verifica la hora de tu dispositivo.',
            ]);
        }

        $remember = (bool) $request->session()->get('auth_2fa_remember', false);

        Auth::login($user, $remember);
        $request->session()->forget(['auth_2fa_user_id', 'auth_2fa_remember', 'login_challenge_result']);

        $request->session()->regenerate(true);
        $request->session()->put('auth_last_activity', time());
        $request->session()->put('auth_session_created_at', time());

        $user->update(['ultimo_acceso' => now()]);
        $this->registrarInicioSesion($user, $request);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Muestra la interfaz para ingresar usuario y código OTP de activación de cuenta.
     */
    public function showActivationForm(Request $request): View
    {
        return view('auth.activar-cuenta');
    }

    /**
     * Valida el usuario y el código OTP de 6 dígitos para activar la cuenta.
     * Tras la activación exitosa, entrega la pantalla de configuración de contraseña personal.
     */
    public function verifyActivationOtp(Request $request): View|RedirectResponse
    {
        $request->validate([
            'nombre_usuario' => ['required', 'string'],
            'codigo_otp' => ['required', 'string'],
        ], [
            'nombre_usuario.required' => 'Ingresa tu nombre de usuario.',
            'codigo_otp.required' => 'Ingresa el código OTP de 6 dígitos recibido por correo.',
        ]);

        $username = trim($request->input('nombre_usuario'));
        $otp = trim($request->input('codigo_otp'));

        $user = Usuario::where('nombre_usuario', $username)->first();

        if (! $user) {
            return back()
                ->withInput($request->only('nombre_usuario'))
                ->withErrors(['codigo_otp' => 'Usuario o código de activación incorrecto.']);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')
                ->with('status', 'Esta cuenta ya se encuentra activada. Puedes iniciar sesión con tus credenciales.');
        }

        if (! $user->email_verification_token || trim($user->email_verification_token) !== $otp) {
            return back()
                ->withInput($request->only('nombre_usuario'))
                ->withErrors(['codigo_otp' => 'El código OTP ingresado es incorrecto.']);
        }

        if ($user->email_verification_expires_at && $user->email_verification_expires_at->isPast()) {
            return back()
                ->withInput($request->only('nombre_usuario'))
                ->withErrors(['codigo_otp' => 'El código OTP ha expirado (válido durante 24 horas). Por favor solicita uno nuevo.']);
        }

        // 1. Activar la cuenta
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
        ]);

        // 2. Guardar token temporal en sesión para permitir el cambio de contraseña
        $setupToken = Str::random(64);
        $request->session()->put('password_setup_user_id', $user->id_usuario);
        $request->session()->put('password_setup_token', $setupToken);

        // 3. Mostrar pantalla para configurar contraseña personal
        return view('auth.setup-password', [
            'username' => $user->nombre_usuario,
            'setupToken' => $setupToken,
        ]);
    }

    /**
     * Reenvía un nuevo código OTP de activación al correo registrado.
     */
    public function resendActivationOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre_usuario' => ['required', 'string'],
        ], [
            'nombre_usuario.required' => 'Ingresa tu nombre de usuario para reenviar el código OTP.',
        ]);

        $username = trim($request->input('nombre_usuario'));
        $throttleKey = 'resend-otp:'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $minutes = (int) ceil(RateLimiter::availableIn($throttleKey) / 60);

            return back()->withErrors([
                'codigo_otp' => "Demasiadas solicitudes. Por favor espera {$minutes} minuto(s) antes de solicitar otro código.",
            ]);
        }

        RateLimiter::hit($throttleKey, 300);

        $user = Usuario::with('persona')->where('nombre_usuario', $username)->first();

        if ($user && ! $user->hasVerifiedEmail() && $user->persona?->email) {
            $otp = sprintf('%06d', random_int(100000, 999999));
            $user->update([
                'email_verification_token' => $otp,
                'email_verification_expires_at' => now()->addHours(24),
            ]);

            try {
                Mail::to($user->persona->email)->send(new VerifyEmailMailable(
                    $otp,
                    $user->nombre_usuario,
                    $user->nombre_completo,
                    24
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('activacion.form')
            ->with('nombre_usuario', $username)
            ->with('status', 'Si la cuenta existe y está pendiente de activación, se ha enviado un nuevo código OTP a tu correo.');
    }

    /**
     * Verifica el correo electrónico mediante token o código OTP (compatibilidad).
     * Redirige al usuario a la página de configuración de contraseña personal.
     */
    public function verifyEmail(Request $request, string $token): View|RedirectResponse
    {
        $user = Usuario::where('email_verification_token', $token)->first();

        if (! $user) {
            return redirect()->route('login')->withErrors([
                'credenciales' => 'El enlace o código de activación no es válido o ya fue utilizado.',
            ]);
        }

        if ($user->email_verification_expires_at && $user->email_verification_expires_at->isPast()) {
            return redirect()->route('activacion.form')
                ->with('nombre_usuario', $user->nombre_usuario)
                ->withErrors(['codigo_otp' => 'El código de activación ha expirado. Solicita uno nuevo.']);
        }

        // Verificar la cuenta inmediatamente
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
        ]);

        $setupToken = Str::random(64);
        $request->session()->put('password_setup_user_id', $user->id_usuario);
        $request->session()->put('password_setup_token', $setupToken);

        return view('auth.setup-password', [
            'username' => $user->nombre_usuario,
            'setupToken' => $setupToken,
        ]);
    }

    /**
     * Procesa el cambio de contraseña tras la verificación de correo y activación por OTP.
     */
    public function setupPassword(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('password_setup_user_id');
        $sessionToken = $request->session()->get('password_setup_token');

        if (! $userId || ! $sessionToken || $sessionToken !== $request->input('setup_token')) {
            return redirect()->route('login')->withErrors([
                'credenciales' => 'La sesión de configuración ha expirado. Inicia sesión con tu usuario y contraseña.',
            ]);
        }

        $request->validate([
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ], [
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ]);

        $user = Usuario::findOrFail($userId);
        $user->update([
            'password_hash' => Hash::make($request->input('password')),
        ]);

        // Limpiar tokens de sesión
        $request->session()->forget(['password_setup_user_id', 'password_setup_token']);

        return redirect()->route('login')->with('success', '¡Cuenta activada y contraseña configurada exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.');
    }

    /**
     * Reenvía el enlace/código de verificación de correo.
     */
    public function resendVerification(Request $request): RedirectResponse
    {
        return $this->resendActivationOtp($request);
    }

    /**
     * Cierra la sesión activa con invalidación completa de cookies y tokens.
     */
    public function logout(Request $request): RedirectResponse
    {
        $accesoId = $request->session()->get('registro_acceso_id');
        /** @var Usuario|null $user */
        $user = Auth::user();

        if ($accesoId) {
            RegistroAcceso::where('id_acceso', $accesoId)->update([
                'fecha_hora_cierre' => now(),
                'estado' => 'CERRADA',
            ]);
        } elseif ($user) {
            RegistroAcceso::where('id_usuario', $user->id_usuario)
                ->where('estado', 'ACTIVA')
                ->latest('id_acceso')
                ->limit(1)
                ->update([
                    'fecha_hora_cierre' => now(),
                    'estado' => 'CERRADA',
                ]);
        }

        if ($user) {
            AuditService::log(
                'LOGOUT',
                'AUTENTICACION',
                ['nombre_usuario' => $user->nombre_usuario, 'rol' => $user->rol_principal],
                $user,
                $user->id_usuario,
                'EXITO',
                "Cierre de sesión del usuario '{$user->nombre_usuario}'"
            );
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Registra automáticamente el inicio de sesión del usuario en PostgreSQL.
     */
    private function registrarInicioSesion(Usuario $user, Request $request): void
    {
        try {
            $ahora = now();
            $registro = RegistroAcceso::create([
                'id_usuario' => $user->id_usuario,
                'nombre_usuario' => $user->nombre_usuario,
                'rol' => $user->rol_principal,
                'fecha_ingreso' => $ahora->toDateString(),
                'hora_ingreso' => $ahora->format('H:i:s'),
                'fecha_hora_cierre' => null,
                'estado' => 'ACTIVA',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);

            $request->session()->put('registro_acceso_id', $registro->id_acceso);

            AuditService::log(
                'LOGIN',
                'AUTENTICACION',
                ['nombre_usuario' => $user->nombre_usuario, 'rol' => $user->rol_principal, 'ip' => $request->ip()],
                $user,
                $user->id_usuario,
                'EXITO',
                "Inicio de sesión exitoso del usuario '{$user->nombre_usuario}'"
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Permite cambiar de rol en modo simulación respetando la jerarquía de roles:
     * - SUPER_ADMIN puede simular cualquier rol inferior o igual.
     * - ADMIN NO puede simular al SUPER_ADMIN.
     * - RECEPCION, TRANSPORTE, SPA, VETERINARIO NO tienen acceso a simulación de roles.
     */
    public function switchDemoRole(Request $request): RedirectResponse
    {
        if (! (app()->isLocal() || app()->runningUnitTests())) {
            abort(404);
        }

        /** @var Usuario|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        // Obtener los roles REALES del usuario en la base de datos (ignorando la sesión de simulación activa)
        $normalize = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', '_', '_'], (string) $str));
        $realRoles = $user->roles->pluck('nombre')->map($normalize)->all();

        $isRealSuperAdmin = in_array('SUPER_ADMIN', $realRoles, true);
        $isRealAdmin = in_array('ADMIN', $realRoles, true) || in_array('ADMINISTRADOR', $realRoles, true);

        // Control de acceso: solo Super-Admin y Administradores tienen acceso al simulador
        if (! $isRealSuperAdmin && ! $isRealAdmin) {
            abort(403, 'No tienes permisos para acceder a la simulación de roles.');
        }

        $targetRole = $normalize($request->input('role'));

        // Jerarquía estricta: un Administrador NO puede simular a un Super-Administrador
        if (! $isRealSuperAdmin && $targetRole === 'SUPER_ADMIN') {
            abort(403, 'Un Administrador no puede simular el rol de Super-Administrador.');
        }

        $validRolesForSuperAdmin = ['SUPER_ADMIN', 'ADMIN', 'RECEPCION', 'VETERINARIO', 'SPA', 'TRANSPORTE'];
        $validRolesForAdmin = ['ADMIN', 'RECEPCION', 'VETERINARIO', 'SPA', 'TRANSPORTE'];

        $allowedRoles = $isRealSuperAdmin ? $validRolesForSuperAdmin : $validRolesForAdmin;

        if (in_array($targetRole, $allowedRoles, true)) {
            session(['demo_role' => $targetRole]);
        }

        return back();
    }
}
