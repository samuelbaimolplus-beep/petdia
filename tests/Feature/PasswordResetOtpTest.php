<?php

namespace Tests\Feature;

use App\Mail\PasswordResetOtpMail;
use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_page_does_not_contain_demo_passwords_or_quick_access_cards(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Contraseña demo: 12345678');
        $response->assertDontSee('Acceso Rápido por Rol (Demostración)');
        $response->assertDontSee('fillUser');
        $response->assertSee('¿Olvidaste tu contraseña?');
    }

    public function test_forgot_password_page_is_accessible(): void
    {
        $response = $this->get('/recuperar-contrasena');

        $response->assertStatus(200);
        $response->assertSee('¿Olvidaste tu contraseña?');
        $response->assertSee('Enviar Código de Seguridad');
    }

    public function test_user_can_request_otp_with_username_and_receives_email(): void
    {
        Mail::fake();

        $persona = Persona::create([
            'documento' => '9988776611',
            'nombre' => 'Test',
            'apellido1' => 'Doctor',
            'email' => 'doctor.test@lapetdiatria.com',
        ]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'doctortest',
            'password_hash' => Hash::make('password123'),
            'estado' => 'ACTIVO',
        ]);

        $response = $this->post('/recuperar-contrasena', [
            'identificador' => 'doctortest',
        ]);

        $response->assertRedirect(route('password.otp'));
        $response->assertSessionHas('status');

        Mail::assertSent(PasswordResetOtpMail::class, function ($mail) use ($persona) {
            return $mail->hasTo($persona->email);
        });

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $persona->email,
        ]);
    }

    public function test_user_can_reset_password_with_valid_otp(): void
    {
        $persona = Persona::create([
            'documento' => '9988776622',
            'nombre' => 'Veterinario',
            'apellido1' => 'Clinico',
            'email' => 'vet.clinico@lapetdiatria.com',
        ]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'vetclinico',
            'password_hash' => Hash::make('oldpassword123'),
            'estado' => 'ACTIVO',
        ]);

        $otp = '749201';

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $persona->email],
            [
                'token' => Hash::make($otp),
                'created_at' => now(),
            ]
        );

        $response = $this->post('/restablecer-contrasena', [
            'email' => $persona->email,
            'otp' => $otp,
            'password' => 'NuevaClaveSegura2026!',
            'password_confirmation' => 'NuevaClaveSegura2026!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $usuario->refresh();
        $this->assertTrue(Hash::check('NuevaClaveSegura2026!', $usuario->password_hash));

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $persona->email,
        ]);
    }

    public function test_otp_verification_fails_with_invalid_code(): void
    {
        $persona = Persona::create([
            'documento' => '9988776633',
            'nombre' => 'Test',
            'apellido1' => 'Invalido',
            'email' => 'invalido@lapetdiatria.com',
        ]);

        Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'testinvalido',
            'password_hash' => Hash::make('password123'),
            'estado' => 'ACTIVO',
        ]);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $persona->email],
            [
                'token' => Hash::make('123456'),
                'created_at' => now(),
            ]
        );

        $response = $this->from(route('password.otp'))->post('/restablecer-contrasena', [
            'email' => $persona->email,
            'otp' => '999999',
            'password' => 'NuevaClaveSegura2026!',
            'password_confirmation' => 'NuevaClaveSegura2026!',
        ]);

        $response->assertRedirect(route('password.otp'));
        $response->assertSessionHasErrors(['otp']);
    }

    public function test_authenticated_user_can_update_email_and_change_password(): void
    {
        $persona = Persona::create([
            'documento' => '9988776644',
            'nombre' => 'Usuario',
            'apellido1' => 'Perfil',
            'email' => 'perfil.inicial@lapetdiatria.com',
        ]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'perfiluser',
            'password_hash' => Hash::make('claveactual123'),
            'estado' => 'ACTIVO',
        ]);

        // Probar acceso a /perfil
        $response = $this->actingAs($usuario)->get('/perfil');
        $response->assertStatus(200);
        $response->assertSee('Perfil y Seguridad de la Cuenta');
        $response->assertSee('perfil.inicial@lapetdiatria.com');

        // Probar actualización de email enlazado
        $updateEmailResponse = $this->actingAs($usuario)->put('/perfil', [
            'email' => 'perfil.nuevo@lapetdiatria.com',
            'telefono' => '71234567',
        ]);
        $updateEmailResponse->assertRedirect();
        $persona->refresh();
        $this->assertEquals('perfil.nuevo@lapetdiatria.com', $persona->email);

        // Probar cambio de contraseña
        $updatePasswordResponse = $this->actingAs($usuario)->put('/perfil/password', [
            'current_password' => 'claveactual123',
            'password' => 'NuevaClaveCambiada2026!',
            'password_confirmation' => 'NuevaClaveCambiada2026!',
        ]);
        $updatePasswordResponse->assertRedirect();
        $usuario->refresh();
        $this->assertTrue(Hash::check('NuevaClaveCambiada2026!', $usuario->password_hash));
    }
}
