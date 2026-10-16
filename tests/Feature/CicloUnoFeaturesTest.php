<?php

namespace Tests\Feature;

use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\RegistroAcceso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CicloUnoFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $superAdmin;

    private Usuario $admin;

    private Usuario $recepcion;

    private Usuario $veterinario;

    private Usuario $spa;

    private Usuario $transporte;

    private Usuario $inactivo;

    protected function setUp(): void
    {
        parent::setUp();

        $rolSuper = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Administrador']);
        $rolAdmin = Rol::firstOrCreate(['nombre' => 'ADMIN'], ['descripcion' => 'Administrador']);
        $rolRec = Rol::firstOrCreate(['nombre' => 'RECEPCION'], ['descripcion' => 'Recepcionista']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Veterinario']);
        $rolSpa = Rol::firstOrCreate(['nombre' => 'SPA'], ['descripcion' => 'Personal SPA']);
        $rolTrans = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Personal Transporte']);

        // Crear personas
        $p1 = Persona::create(['documento' => '1001', 'nombre' => 'Super', 'apellido1' => 'Admin', 'email' => 'super@test.com']);
        $p2 = Persona::create(['documento' => '1002', 'nombre' => 'Admin', 'apellido1' => 'User', 'email' => 'admin@test.com']);
        $p3 = Persona::create(['documento' => '1003', 'nombre' => 'Recep', 'apellido1' => 'User', 'email' => 'recep@test.com']);
        $p4 = Persona::create(['documento' => '1004', 'nombre' => 'Vet', 'apellido1' => 'User', 'email' => 'vet@test.com']);
        $p5 = Persona::create(['documento' => '1005', 'nombre' => 'Spa', 'apellido1' => 'User', 'email' => 'spa@test.com']);
        $p6 = Persona::create(['documento' => '1006', 'nombre' => 'Trans', 'apellido1' => 'User', 'email' => 'trans@test.com']);
        $p7 = Persona::create(['documento' => '1007', 'nombre' => 'Inactivo', 'apellido1' => 'User', 'email' => 'inactivo@test.com']);

        // Crear usuarios con password conocido y email verificado
        $this->superAdmin = Usuario::create([
            'id_persona' => $p1->id_persona,
            'nombre_usuario' => 'superadmin_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->superAdmin->roles()->attach($rolSuper->id_rol);

        $this->admin = Usuario::create([
            'id_persona' => $p2->id_persona,
            'nombre_usuario' => 'admin_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->admin->roles()->attach($rolAdmin->id_rol);

        $this->recepcion = Usuario::create([
            'id_persona' => $p3->id_persona,
            'nombre_usuario' => 'recepcion_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->recepcion->roles()->attach($rolRec->id_rol);

        $this->veterinario = Usuario::create([
            'id_persona' => $p4->id_persona,
            'nombre_usuario' => 'veterinario_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->veterinario->roles()->attach($rolVet->id_rol);

        $this->spa = Usuario::create([
            'id_persona' => $p5->id_persona,
            'nombre_usuario' => 'spa_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->spa->roles()->attach($rolSpa->id_rol);

        $this->transporte = Usuario::create([
            'id_persona' => $p6->id_persona,
            'nombre_usuario' => 'transporte_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->transporte->roles()->attach($rolTrans->id_rol);

        $this->inactivo = Usuario::create([
            'id_persona' => $p7->id_persona,
            'nombre_usuario' => 'inactivo_test',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'INACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->inactivo->roles()->attach($rolRec->id_rol);
    }

    /**
     * Criterio 1 & 2: Los 6 roles A1-A6 pueden iniciar sesión con sus credenciales.
     */
    public function test_all_six_roles_can_login_with_their_credentials(): void
    {
        $users = [
            'superadmin_test' => $this->superAdmin,
            'admin_test' => $this->admin,
            'recepcion_test' => $this->recepcion,
            'veterinario_test' => $this->veterinario,
            'spa_test' => $this->spa,
            'transporte_test' => $this->transporte,
        ];

        foreach ($users as $username => $user) {
            $response = $this->post('/login', [
                'nombre_usuario' => $username,
                'password' => 'Password123!',
            ]);

            $response->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs($user);

            // Cerrar sesión para el siguiente rol
            $this->post('/logout');
            $this->assertGuest();
        }
    }

    /**
     * Criterio 16: Los usuarios desactivados NO pueden iniciar sesión (CU6).
     */
    public function test_deactivated_user_cannot_login(): void
    {
        $response = $this->from('/login')->post('/login', [
            'nombre_usuario' => 'inactivo_test',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['credenciales']);
        $this->assertGuest();
    }

    /**
     * Criterio 14 & 15: Registro automático de accesos con fecha y hora consultable por Super-Admin.
     */
    public function test_system_records_login_access_automatically_and_super_admin_can_view_it(): void
    {
        $this->post('/login', [
            'nombre_usuario' => 'admin_test',
            'password' => 'Password123!',
        ]);

        // Verificar que se registró el acceso en la base de datos
        $this->assertDatabaseHas('registro_accesos', [
            'nombre_usuario' => 'admin_test',
            'rol' => 'ADMIN',
            'estado' => 'ACTIVA',
        ]);

        $acceso = RegistroAcceso::where('nombre_usuario', 'admin_test')->latest('id_acceso')->first();
        $this->assertNotNull($acceso->fecha_ingreso);
        $this->assertNotNull($acceso->hora_ingreso);

        // Al cerrar sesión se registra la fecha y hora de cierre
        $this->post('/logout');
        $acceso->refresh();
        $this->assertEquals('CERRADA', $acceso->estado);
        $this->assertNotNull($acceso->fecha_hora_cierre);

        // Super-Admin consulta el módulo de accesos
        $response = $this->actingAs($this->superAdmin)->get('/usuarios/accesos');
        $response->assertStatus(200);
        $response->assertSee('Registro de Accesos al Sistema');
        $response->assertSee('admin_test');
        $response->assertSee($acceso->fecha_ingreso->format('d/m/Y'));
    }

    /**
     * Criterio 4 & 5: Restricción en backend - Usuario sin rol Super-Admin recibe 403 en /usuarios y /usuarios/accesos.
     */
    public function test_backend_authorization_blocks_non_super_admin_from_administering_users_and_access_logs(): void
    {
        $nonSuperAdmins = [
            $this->admin,
            $this->recepcion,
            $this->veterinario,
            $this->spa,
            $this->transporte,
        ];

        foreach ($nonSuperAdmins as $user) {
            $resUsuarios = $this->actingAs($user)->get('/usuarios');
            $resUsuarios->assertStatus(403);

            $resAccesos = $this->actingAs($user)->get('/usuarios/accesos');
            $resAccesos->assertStatus(403);
        }
    }

    /**
     * Criterio 12: Control de acceso al Historial Clínico (CU20).
     */
    public function test_historial_clinico_access_control(): void
    {
        // Veterinario y Super-Admin tienen acceso al índice clínico
        $resVet = $this->actingAs($this->veterinario)->get('/historial-clinico');
        $resVet->assertStatus(200);
        $resVet->assertSee('Historial Clínico Veterinario');

        $resSuper = $this->actingAs($this->superAdmin)->get('/historial-clinico');
        $resSuper->assertStatus(200);

        // Recepción y Transporte ven la pantalla de acceso restringido
        $resRec = $this->actingAs($this->recepcion)->get('/historial-clinico');
        $resRec->assertSee('privilegios');

        $resTrans = $this->actingAs($this->transporte)->get('/historial-clinico');
        $resTrans->assertSee('privilegios');
    }

    /**
     * Criterio 11: Asociar y reasociar mascota con propietario (CU11).
     */
    public function test_can_associate_and_reassociate_pet_to_owner(): void
    {
        $p1 = Persona::create(['documento' => '5001', 'nombre' => 'Dueño', 'apellido1' => 'Uno', 'email' => 'uno@test.com']);
        $p2 = Persona::create(['documento' => '5002', 'nombre' => 'Dueño', 'apellido1' => 'Dos', 'email' => 'dos@test.com']);
        $c1 = Propietario::create(['id_persona' => $p1->id_persona]);
        $c2 = Propietario::create(['id_persona' => $p2->id_persona]);

        $especie = Especie::firstOrCreate(['nombre' => 'Canino']);
        $raza = Raza::firstOrCreate(['nombre' => 'Mestizo'], ['id_especie' => $especie->id_especie]);

        $mascota = Paciente::create([
            'id_propietario' => $c1->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Firulais',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2022-01-01',
            'estado' => 'ACTIVO',
        ]);

        $this->assertEquals($c1->id_persona, $mascota->id_propietario);

        // Reasociar al propietario 2 (CU11)
        $response = $this->actingAs($this->admin)
            ->from('/mascotas/'.$mascota->id_paciente)
            ->put('/mascotas/'.$mascota->id_paciente, [
                'nombre' => 'Firulais',
                'id_propietario' => $c2->id_persona,
                'id_raza' => $raza->id_raza,
                'sexo' => 'MACHO',
                'fecha_nacimiento' => '2022-01-01',
                'estado' => 'ACTIVO',
            ]);

        $response->assertRedirect('/mascotas/'.$mascota->id_paciente);
        $this->assertEquals($c2->id_persona, $mascota->fresh()->id_propietario);
    }
}
