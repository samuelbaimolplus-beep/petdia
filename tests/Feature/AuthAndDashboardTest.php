<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $user;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRol = Rol::create([
            'nombre' => 'ADMIN',
            'descripcion' => 'Administrador',
        ]);

        $this->user = Usuario::factory()->create([
            'nombre_usuario' => 'jperez',
            'password_hash' => bcrypt('12345678'),
        ]);

        $this->user->roles()->attach($adminRol->id_rol);
    }

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('La Petdiatría');
        $response->assertSee('Nombre de usuario');
        $response->assertSee('Contraseña');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'nombre_usuario' => 'jperez',
            'password' => '12345678',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->from('/login')->post('/login', [
            'nombre_usuario' => 'jperez',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['credenciales']);
        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('CLIENTES REGISTRADOS');
        $response->assertSee('MASCOTAS REGISTRADAS');
    }

    public function test_user_can_switch_demo_role(): void
    {
        $response = $this->actingAs($this->user)->post('/demo/switch-role', [
            'role' => 'VETERINARIO',
        ]);

        $response->assertRedirect();
        $this->assertEquals('VETERINARIO', session('demo_role'));

        $dashboard = $this->actingAs($this->user)->get('/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertSee('PACIENTES / MASCOTAS');
    }

    public function test_user_can_logout(): void
    {
        $response = $this->actingAs($this->user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
