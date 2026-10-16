<?php

namespace Tests\Feature;

use App\Models\Permiso;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsuarioWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $superAdmin;

    private Usuario $veterinario;

    private Rol $rolSuperAdmin;

    private Rol $rolVet;

    private Permiso $permisoCrearCita;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rolSuperAdmin = Rol::firstOrCreate(
            ['nombre' => 'SUPER_ADMIN'],
            ['descripcion' => 'Super Administrador con acceso total']
        );

        $this->rolVet = Rol::firstOrCreate(
            ['nombre' => 'VETERINARIO'],
            ['descripcion' => 'Médico Veterinario']
        );

        $this->permisoCrearCita = Permiso::firstOrCreate(
            ['nombre' => 'CREAR_CITA'],
            ['descripcion' => 'Permite programar citas']
        );

        $personaAdmin = Persona::create([
            'documento' => '10000001',
            'nombre' => 'Super',
            'apellido1' => 'Admin',
            'email' => 'super@lapetdiatria.com',
        ]);

        $this->superAdmin = Usuario::factory()->create([
            'id_persona' => $personaAdmin->id_persona,
            'nombre_usuario' => 'superadmin',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->attach($this->rolSuperAdmin->id_rol);

        $personaVet = Persona::create([
            'documento' => '10000002',
            'nombre' => 'Dra. María',
            'apellido1' => 'Gonzales',
            'email' => 'maria@lapetdiatria.com',
        ]);

        $this->veterinario = Usuario::factory()->create([
            'id_persona' => $personaVet->id_persona,
            'nombre_usuario' => 'mgonzales',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->attach($this->rolVet->id_rol);
    }

    public function test_non_super_admin_cannot_access_usuarios_module(): void
    {
        $response = $this->actingAs($this->veterinario)->get('/usuarios');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_usuarios_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/usuarios');

        $response->assertStatus(200);
        $response->assertSee('Usuarios, Roles y Permisos');
        $response->assertSee('superadmin');
        $response->assertSee('mgonzales');
        $response->assertSee('Listado de Usuarios');
        $response->assertSee('Roles y Matriz de Permisos');
    }

    public function test_super_admin_can_filter_and_search_usuarios(): void
    {
        $personaOtro = Persona::create([
            'documento' => '99991111',
            'nombre' => 'Rodrigo',
            'apellido1' => 'Paredes',
            'email' => 'rodrigo@lapetdiatria.com',
        ]);
        $otroUser = Usuario::factory()->create([
            'id_persona' => $personaOtro->id_persona,
            'nombre_usuario' => 'rparedes',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $otroUser->roles()->attach($this->rolSuperAdmin->id_rol);

        $response = $this->actingAs($this->superAdmin)->get('/usuarios?search=Gonzales');
        $response->assertStatus(200);
        $response->assertSee('mgonzales');
        $response->assertDontSee('rparedes');

        $responseByRole = $this->actingAs($this->superAdmin)->get('/usuarios?rol='.$this->rolVet->id_rol);
        $responseByRole->assertStatus(200);
        $responseByRole->assertSee('mgonzales');
        $responseByRole->assertDontSee('rparedes');
    }

    public function test_super_admin_can_create_new_usuario_with_persona(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/usuarios', [
            'documento' => '88887777',
            'nombre' => 'Carlos',
            'apellido1' => 'Ramírez',
            'apellido2' => 'Soto',
            'telefono' => '71122334',
            'email' => 'carlos.r@lapetdiatria.com',
            'direccion' => 'Av. San Martín #450',
            'nombre_usuario' => 'cramirez',
            'password' => 'Secret123!',
            'password_confirmation' => 'Secret123!',
            'id_rol' => $this->rolVet->id_rol,
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect('/usuarios');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('personas', [
            'documento' => '88887777',
            'nombre' => 'Carlos',
            'apellido1' => 'Ramírez',
        ]);

        $this->assertDatabaseHas('usuarios', [
            'nombre_usuario' => 'cramirez',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_super_admin_can_update_existing_usuario(): void
    {
        $response = $this->actingAs($this->superAdmin)->put('/usuarios/'.$this->veterinario->id_usuario, [
            'documento' => '10000002',
            'nombre' => 'María Elena',
            'apellido1' => 'Gonzales',
            'apellido2' => 'Vargas',
            'telefono' => '70011223',
            'email' => 'maria.elena@lapetdiatria.com',
            'direccion' => 'Calle Cochabamba #12',
            'nombre_usuario' => 'mgonzales_editado',
            'id_rol' => $this->rolVet->id_rol,
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect('/usuarios');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('usuarios', [
            'id_usuario' => $this->veterinario->id_usuario,
            'nombre_usuario' => 'mgonzales_editado',
        ]);

        $this->assertDatabaseHas('personas', [
            'documento' => '10000002',
            'nombre' => 'María Elena',
            'apellido2' => 'Vargas',
        ]);
    }

    public function test_super_admin_can_toggle_usuario_estado_baja_logica(): void
    {
        $this->assertEquals('ACTIVO', $this->veterinario->estado);

        // Desactivar (baja lógica)
        $response = $this->actingAs($this->superAdmin)->post('/usuarios/'.$this->veterinario->id_usuario.'/toggle-estado');
        $response->assertRedirect('/usuarios');
        $response->assertSessionHas('success');

        $this->veterinario->refresh();
        $this->assertEquals('INACTIVO', $this->veterinario->estado);

        // Reactivar
        $response2 = $this->actingAs($this->superAdmin)->post('/usuarios/'.$this->veterinario->id_usuario.'/toggle-estado');
        $response2->assertRedirect('/usuarios');

        $this->veterinario->refresh();
        $this->assertEquals('ACTIVO', $this->veterinario->estado);
    }

    public function test_super_admin_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/usuarios/'.$this->superAdmin->id_usuario.'/toggle-estado');
        $response->assertSessionHas('error');

        $this->superAdmin->refresh();
        $this->assertEquals('ACTIVO', $this->superAdmin->estado);
    }

    public function test_super_admin_can_update_permission_matrix(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/roles/permisos', [
            'permisos' => [
                $this->rolVet->id_rol => [$this->permisoCrearCita->id_permiso],
            ],
        ]);

        $response->assertRedirect('/usuarios?tab=roles');
        $response->assertSessionHas('success');

        $this->assertTrue($this->rolVet->refresh()->hasPermiso($this->permisoCrearCita->id_permiso));
    }

    public function test_super_admin_can_create_and_update_roles(): void
    {
        // Crear rol
        $response = $this->actingAs($this->superAdmin)->post('/roles', [
            'nombre' => 'ASISTENTE_QUIRURGICO',
            'descripcion' => 'Apoyo en cirugías e instrumentación',
        ]);

        $response->assertRedirect('/usuarios?tab=roles');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', [
            'nombre' => 'ASISTENTE_QUIRURGICO',
        ]);

        $nuevoRol = Rol::where('nombre', 'ASISTENTE_QUIRURGICO')->firstOrFail();

        // Actualizar rol
        $updateResponse = $this->actingAs($this->superAdmin)->put('/roles/'.$nuevoRol->id_rol, [
            'nombre' => 'ASISTENTE_CIRUGIA',
            'descripcion' => 'Instrumentista y recuperación post-quirúrgica',
        ]);

        $updateResponse->assertRedirect('/usuarios?tab=roles');
        $this->assertDatabaseHas('roles', [
            'id_rol' => $nuevoRol->id_rol,
            'nombre' => 'ASISTENTE_CIRUGIA',
        ]);
    }
}
