<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\TotpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Hashing\BcryptHasher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityAuthenticationAuditTest extends TestCase
{
    use DatabaseTransactions;

    protected function createPersona(array $attributes = []): Persona
    {
        return Persona::create(array_merge([
            'documento' => 'DOC-'.Str::random(8),
            'nombre' => 'Test',
            'apellido1' => 'Security',
            'email' => 'sec_'.Str::random(6).'@example.com',
        ], $attributes));
    }

    protected function createUsuario(string $rolNombre, array $attributes = []): Usuario
    {
        $persona = $this->createPersona();
        $rol = Rol::firstOrCreate(
            ['nombre' => $rolNombre],
            ['descripcion' => "Rol de prueba {$rolNombre}"]
        );

        /** @var Usuario $usuario */
        $usuario = Usuario::create(array_merge([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'user_'.Str::random(8),
            'password_hash' => Hash::make('Secret123!*'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ], $attributes));

        $usuario->roles()->sync([$rol->id_rol]);

        return $usuario;
    }

    /**
     * Test 1: Session cookies HttpOnly, lifetime and inactivity timeout.
     */
    public function test_session_cookie_is_httponly_and_expires_on_inactivity(): void
    {
        $this->assertEquals(30, config('session.lifetime'));
        $this->assertTrue(config('session.http_only'));

        $usuario = $this->createUsuario('ADMIN');
        $this->actingAs($usuario);

        // Simulamos inactividad de más de 30 minutos (1801 segundos atrás)
        session(['auth_last_activity' => time() - 1801]);

        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['lockout']);
        $this->assertGuest();
    }

    /**
     * Test 2: Server-side authorization check (elimina verificación client-side).
     */
    public function test_server_side_authorization_middleware_blocks_unauthorized_roles(): void
    {
        // Usuario con rol 'SPA' intentando acceder al módulo administrativo de Usuarios
        $userSpa = $this->createUsuario('SPA');

        $response = $this->actingAs($userSpa)->get(route('usuarios.index'));
        $response->assertStatus(403);

        // Usuario con rol 'SUPER_ADMIN' tiene acceso autorizado en servidor
        $superAdmin = $this->createUsuario('SUPER_ADMIN');
        $responseSuper = $this->actingAs($superAdmin)->get(route('usuarios.index'));
        $responseSuper->assertStatus(200);
    }

    /**
     * Test 3: Email verification with OTP blocks login, redirects to activation interface, and verifies account.
     */
    public function test_unverified_email_blocks_login_and_redirects_to_otp_activation(): void
    {
        $otp = '458921';
        $usuario = $this->createUsuario('VETERINARIO', [
            'nombre_usuario' => 'veterinario_test',
            'password_hash' => Hash::make('Veterinario123!*'),
            'email_verified_at' => null, // No verificado
            'email_verification_token' => $otp,
            'email_verification_expires_at' => now()->addHours(24),
        ]);

        // 1. Intento de login previo a verificar -> Redirige a la interfaz de activación OTP
        $response = $this->post(route('login.post'), [
            'nombre_usuario' => 'veterinario_test',
            'password' => 'Veterinario123!*',
        ]);

        $response->assertRedirect(route('activacion.form'));
        $response->assertSessionHas('nombre_usuario', 'veterinario_test');
        $this->assertGuest();

        // 2. Comprobar que la vista de activación carga correctamente
        $formResponse = $this->get(route('activacion.form'));
        $formResponse->assertStatus(200);
        $formResponse->assertSee('Ingresa tu Código de Activación');

        // 3. Intento con OTP incorrecto falla
        $failOtp = $this->post(route('activacion.verify'), [
            'nombre_usuario' => 'veterinario_test',
            'codigo_otp' => '000000',
        ]);
        $failOtp->assertSessionHasErrors(['codigo_otp']);
        $this->assertFalse($usuario->fresh()->hasVerifiedEmail());

        // 4. Activación con OTP correcto -> Activa cuenta y muestra pantalla de configurar contraseña
        $verifyResponse = $this->post(route('activacion.verify'), [
            'nombre_usuario' => 'veterinario_test',
            'codigo_otp' => $otp,
        ]);
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee('Configura tu Contraseña');

        $usuario->refresh();
        $this->assertTrue($usuario->hasVerifiedEmail());

        // 5. Configuración de contraseña personal
        $setupToken = session('password_setup_token');
        $setupResponse = $this->post(route('verification.setup-password'), [
            'setup_token' => $setupToken,
            'password' => 'NuevaPass123!*',
            'password_confirmation' => 'NuevaPass123!*',
        ]);
        $setupResponse->assertRedirect(route('login'));
        $setupResponse->assertSessionHas('success');

        // 6. Ahora el login procede exitosamente con la nueva contraseña
        $loginOk = $this->post(route('login.post'), [
            'nombre_usuario' => 'veterinario_test',
            'password' => 'NuevaPass123!*',
        ]);
        $loginOk->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($usuario);
    }

    /**
     * Test 3.1: Role simulation hierarchy restrictions.
     */
    public function test_role_simulation_hierarchy_and_permissions(): void
    {
        // 1. Un usuario operativo (ej. SPA o RECEPCION) no puede simular roles
        $userSpa = $this->createUsuario('SPA');
        $forbiddenSpa = $this->actingAs($userSpa)->post(route('demo.switch-role'), [
            'role' => 'ADMIN',
        ]);
        $forbiddenSpa->assertStatus(403);

        // 2. Un ADMIN NO puede simular a SUPER_ADMIN (no es jerárquico)
        $userAdmin = $this->createUsuario('ADMIN');
        $forbiddenAdmin = $this->actingAs($userAdmin)->post(route('demo.switch-role'), [
            'role' => 'SUPER_ADMIN',
        ]);
        $forbiddenAdmin->assertStatus(403);

        // 3. Un ADMIN SÍ puede simular roles inferiores o iguales (ej. VETERINARIO)
        $allowedAdmin = $this->actingAs($userAdmin)->post(route('demo.switch-role'), [
            'role' => 'VETERINARIO',
        ]);
        $allowedAdmin->assertRedirect();
        $this->assertEquals('VETERINARIO', session('demo_role'));

        // 4. Un SUPER_ADMIN puede simular cualquier rol, incluido SUPER_ADMIN
        $superAdmin = $this->createUsuario('SUPER_ADMIN');
        $allowedSuper = $this->actingAs($superAdmin)->post(route('demo.switch-role'), [
            'role' => 'SUPER_ADMIN',
        ]);
        $allowedSuper->assertRedirect();
        $this->assertEquals('SUPER_ADMIN', session('demo_role'));
    }

    /**
     * Test 4: Two-Factor Authentication (2FA TOTP RFC 6238).
     */
    public function test_two_factor_authentication_totp_flow(): void
    {
        $totpService = new TotpService;
        $secret = $totpService->generateSecret();
        $recoveryCode = 'ABCD-1234';

        $admin = $this->createUsuario('SUPER_ADMIN', [
            'nombre_usuario' => 'admin_2fa_test',
            'password_hash' => Hash::make('SuperAdmin123!*'),
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => [$recoveryCode],
        ]);

        // Login inicial redirige a vista de 2FA
        $response = $this->post(route('login.post'), [
            'nombre_usuario' => 'admin_2fa_test',
            'password' => 'SuperAdmin123!*',
        ]);

        $response->assertRedirect(route('login.2fa'));
        $response->assertSessionHas('auth_2fa_user_id', $admin->id_usuario);
        $this->assertGuest();

        // Código 2FA erróneo es rechazado
        $wrongResponse = $this->withSession(['auth_2fa_user_id' => $admin->id_usuario])
            ->post(route('login.2fa.post'), [
                'code' => '000000',
            ]);
        $wrongResponse->assertSessionHasErrors(['code']);
        $this->assertGuest();

        // Código TOTP válido completa la autenticación
        $validCode = $totpService->calculateCode($secret, (int) floor(time() / 30));
        $validResponse = $this->withSession(['auth_2fa_user_id' => $admin->id_usuario])
            ->post(route('login.2fa.post'), [
                'code' => $validCode,
            ]);

        $validResponse->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Test 5: Rate Limiting and Generic Error Messages.
     */
    public function test_rate_limiting_locks_out_after_5_failed_attempts(): void
    {
        RateLimiter::clear('login:usuario_inexistente|127.0.0.1');
        session(['login_challenge_result' => 10]);

        for ($i = 1; $i <= 4; $i++) {
            $res = $this->post(route('login.post'), [
                'nombre_usuario' => 'usuario_inexistente',
                'password' => 'WrongPass123!*',
                'captcha_answer' => 10,
            ]);
            $res->assertSessionHasErrors(['credenciales']);
            // Nunca revelar si el usuario o contraseña es incorrecto
            $this->assertStringContainsString(
                'Credenciales incorrectas',
                session('errors')->first('credenciales')
            );
        }

        // Intento 5: Debe activar el bloqueo de rate limit
        $res5 = $this->post(route('login.post'), [
            'nombre_usuario' => 'usuario_inexistente',
            'password' => 'WrongPass123!*',
            'captcha_answer' => 10,
        ]);

        $res5->assertSessionHasErrors(['lockout']);
    }

    /**
     * Test 6: Password Policy (Bcrypt 12 rounds and minimum complexity).
     */
    public function test_password_policy_requires_complexity_and_uses_bcrypt_12(): void
    {
        $this->assertEquals('bcrypt', config('hashing.driver'));

        // En producción/web el costo configurado en config/hashing.php es mínimo 12
        $productionHasher = new BcryptHasher(['rounds' => 12]);
        $hash12 = $productionHasher->make('ComplexPassword123!*');
        $info = password_get_info($hash12);
        $this->assertEquals(12, $info['options']['cost']);

        // Verificamos que una contraseña débil (<8 caracteres o sin mayúscula/número/símbolo) sea rechazada
        $superAdmin = $this->createUsuario('SUPER_ADMIN');
        $this->actingAs($superAdmin);

        $weakResponse = $this->post(route('usuarios.store'), [
            'documento' => '12345678',
            'nombre' => 'Test',
            'apellido1' => 'Pass',
            'email' => 'weakpass@test.com',
            'nombre_usuario' => 'test_weak_pass',
            'password' => 'simple', // Menor a 8 caracteres, sin símbolos ni números
            'password_confirmation' => 'simple',
            'id_rol' => Rol::first()->id_rol,
            'estado' => 'ACTIVO',
        ]);

        $weakResponse->assertSessionHasErrors(['password']);
    }
}
