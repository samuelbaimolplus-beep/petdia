<?php

namespace Tests\Feature;

use App\Models\HorarioProfesional;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfesionalWebTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioConRol(string $nombreRol): Usuario
    {
        $persona = Persona::create([
            'documento' => 'DOC-'.uniqid(),
            'nombre' => 'Test',
            'apellido1' => $nombreRol,
            'email' => strtolower($nombreRol).'_'.uniqid().'@petdiatria.com',
        ]);

        $rol = Rol::firstOrCreate(['nombre' => $nombreRol], ['descripcion' => 'Rol '.$nombreRol]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'user_'.strtolower(str_replace('-', '_', $nombreRol)).'_'.uniqid(),
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);

        $usuario->roles()->sync([$rol->id_rol]);

        return $usuario;
    }

    public function test_super_admin_puede_ver_listado_profesionales(): void
    {
        $admin = $this->crearUsuarioConRol('Super-Admin');

        $persona = Persona::create([
            'documento' => 'DOC-PROF-1',
            'nombre' => 'Carlos',
            'apellido1' => 'Mendoza',
        ]);
        Profesional::create([
            'id_persona' => $persona->id_persona,
            'especialidad' => 'Cirugía General',
            'registro_profesional' => 'MAT-9988',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($admin)->get(route('profesionales.index'));

        $response->assertStatus(200);
        $response->assertSee('Equipo Profesional y Disponibilidad');
        $response->assertSee('Carlos');
        $response->assertSee('Cirugía General');
    }

    public function test_admin_puede_crear_profesional_con_horarios_por_defecto(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $servicio = Servicio::create([
            'nombre' => 'Consulta Médica General',
            'precio_base' => 100,
            'tipo_servicio' => 'CLINICO',
            'estado' => 'ACTIVO',
        ]);

        $data = [
            'documento' => 'DOC-PROF-NEW',
            'nombre' => 'Laura',
            'apellido1' => 'Torres',
            'apellido2' => 'Ríos',
            'telefono' => '71234567',
            'email' => 'laura@petdiatria.com',
            'direccion' => 'Av. Siempre Viva 123',
            'especialidad' => 'Dermatología',
            'registro_profesional' => 'MAT-5544',
            'estado' => 'ACTIVO',
            'servicios' => [$servicio->id_servicio],
        ];

        $response = $this->actingAs($admin)->post(route('profesionales.store'), $data);

        $profesional = Profesional::where('registro_profesional', 'MAT-5544')->first();
        $this->assertNotNull($profesional);
        $this->assertEquals('Dermatología', $profesional->especialidad);
        $this->assertCount(1, $profesional->servicios);
        $this->assertCount(5, $profesional->horarios); // 5 días de lun a vie

        $response->assertRedirect(route('profesionales.show', $profesional->id_persona));
    }

    public function test_recepcion_tiene_acceso_de_solo_lectura(): void
    {
        $recepcion = $this->crearUsuarioConRol('Recepción');

        $persona = Persona::create([
            'documento' => 'DOC-PROF-REC',
            'nombre' => 'Andrés',
            'apellido1' => 'Gómez',
        ]);
        $profesional = Profesional::create([
            'id_persona' => $persona->id_persona,
            'especialidad' => 'Oftalmología',
            'registro_profesional' => 'MAT-1122',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($recepcion)->get(route('profesionales.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Nuevo Profesional');

        $responseShow = $this->actingAs($recepcion)->get(route('profesionales.show', $profesional->id_persona));
        $responseShow->assertStatus(200);
        $responseShow->assertDontSee('Guardar Cambios');
        $responseShow->assertDontSee('Actualizar Servicios Habilitados');
    }

    public function test_veterinario_no_tiene_acceso_a_modulo_profesionales(): void
    {
        $vet = $this->crearUsuarioConRol('Veterinario');

        $response = $this->actingAs($vet)->get(route('profesionales.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_actualizar_servicios_habilitados(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $persona = Persona::create([
            'documento' => 'DOC-SRV',
            'nombre' => 'Marta',
            'apellido1' => 'Suárez',
        ]);
        $profesional = Profesional::create([
            'id_persona' => $persona->id_persona,
            'especialidad' => 'Odontología',
            'registro_profesional' => 'MAT-ODON',
            'estado' => 'ACTIVO',
        ]);

        $srv1 = Servicio::create(['nombre' => 'Limpieza Dental', 'precio_base' => 150, 'estado' => 'ACTIVO']);
        $srv2 = Servicio::create(['nombre' => 'Extracción', 'precio_base' => 200, 'estado' => 'ACTIVO']);

        $response = $this->actingAs($admin)->put(route('profesionales.update-servicios', $profesional->id_persona), [
            'servicios' => [$srv1->id_servicio, $srv2->id_servicio],
        ]);

        $response->assertSessionHas('success');
        $this->assertCount(2, $profesional->fresh()->servicios);
    }

    public function test_admin_puede_agregar_y_eliminar_turno_horario(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $persona = Persona::create([
            'documento' => 'DOC-HOR',
            'nombre' => 'Esteban',
            'apellido1' => 'Paredes',
        ]);
        $profesional = Profesional::create([
            'id_persona' => $persona->id_persona,
            'especialidad' => 'Traumatología',
            'registro_profesional' => 'MAT-TRAUM',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($admin)->post(route('profesionales.store-horario', $profesional->id_persona), [
            'dia_semana' => 'Sábado',
            'hora_inicio' => '09:00',
            'hora_fin' => '13:00',
            'estado' => 'ACTIVO',
        ]);

        $response->assertSessionHas('success');
        $horario = HorarioProfesional::where('id_profesional', $profesional->id_persona)->where('dia_semana', 6)->first();
        $this->assertNotNull($horario);

        // Eliminar turno
        $responseDelete = $this->actingAs($admin)->delete(route('profesionales.delete-horario', $horario->id_horario));
        $responseDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('horario_profesionales', ['id_horario' => $horario->id_horario]);
    }

    public function test_admin_puede_hacer_baja_logica_de_profesional(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $persona = Persona::create([
            'documento' => 'DOC-BAJA',
            'nombre' => 'Romina',
            'apellido1' => 'Paz',
        ]);
        $profesional = Profesional::create([
            'id_persona' => $persona->id_persona,
            'especialidad' => 'Ecografía',
            'registro_profesional' => 'MAT-ECO',
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($admin)->post(route('profesionales.toggle-estado', $profesional->id_persona));
        $response->assertSessionHas('success');
        $this->assertEquals('INACTIVO', $profesional->fresh()->estado);

        // Reactivar
        $this->actingAs($admin)->post(route('profesionales.toggle-estado', $profesional->id_persona));
        $this->assertEquals('ACTIVO', $profesional->fresh()->estado);
    }

    public function test_admin_puede_crear_profesional_vinculando_usuario_existente(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $usuarioExistente = $this->crearUsuarioConRol('Veterinario');

        $data = [
            'id_usuario_existente' => $usuarioExistente->id_usuario,
            'especialidad' => 'Medicina Felina',
            'registro_profesional' => 'MAT-FEL-01',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($admin)->post(route('profesionales.store'), $data);

        $response->assertRedirect(route('profesionales.show', $usuarioExistente->id_persona));
        $response->assertSessionHas('success');

        $profesional = Profesional::where('id_persona', $usuarioExistente->id_persona)->first();
        $this->assertNotNull($profesional);
        $this->assertEquals('MAT-FEL-01', $profesional->registro_profesional);
        $this->assertEquals('Medicina Felina', $profesional->especialidad);

        // Verificar que la relación usuario -> profesional y profesional -> usuario funciona
        $this->assertEquals($usuarioExistente->id_usuario, $profesional->usuario->id_usuario);
    }

    public function test_admin_puede_crear_profesional_y_cuenta_usuario_simultaneamente(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $rol = Rol::firstOrCreate(['nombre' => 'Cirujano'], ['descripcion' => 'Rol de Cirujano']);

        $data = [
            'documento' => 'DOC-CIR-99',
            'nombre' => 'Rodrigo',
            'apellido1' => 'Molina',
            'email' => 'rodrigo.molina@petdiatria.com',
            'telefono' => '78912345',
            'especialidad' => 'Cirugía Ortopédica',
            'registro_profesional' => 'MAT-CIR-99',
            'estado' => 'ACTIVO',
            'crear_usuario' => '1',
            'nombre_usuario' => 'dr.rodrigo',
            'password' => 'Cirujano2026!',
            'id_rol' => $rol->id_rol,
        ];

        $response = $this->actingAs($admin)->post(route('profesionales.store'), $data);

        $profesional = Profesional::where('registro_profesional', 'MAT-CIR-99')->first();
        $this->assertNotNull($profesional);
        $response->assertRedirect(route('profesionales.show', $profesional->id_persona));

        // Verificar que se creó el usuario y se vinculó al profesional
        $usuario = Usuario::where('nombre_usuario', 'dr.rodrigo')->first();
        $this->assertNotNull($usuario);
        $this->assertEquals($profesional->id_persona, $usuario->id_persona);
        $this->assertTrue(Hash::check('Cirujano2026!', $usuario->password_hash));
        $this->assertTrue($usuario->roles->contains('id_rol', $rol->id_rol));
    }

    public function test_admin_puede_crear_usuario_para_profesional_desde_vista_detalle(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $persona = Persona::create([
            'documento' => 'DOC-NOPASS',
            'nombre' => 'Valeria',
            'apellido1' => 'Cáceres',
            'email' => 'valeria.caceres@petdiatria.com',
        ]);
        $profesional = Profesional::create([
            'id_persona' => $persona->id_persona,
            'especialidad' => 'Cardiología',
            'registro_profesional' => 'MAT-CARDIO',
            'estado' => 'ACTIVO',
        ]);

        $rol = Rol::firstOrCreate(['nombre' => 'Especialista'], ['descripcion' => 'Rol Especialista']);

        $response = $this->actingAs($admin)->post(route('profesionales.store-usuario', $profesional->id_persona), [
            'nombre_usuario' => 'dra.valeria',
            'password' => 'ValeriaPass123!',
            'id_rol' => $rol->id_rol,
        ]);

        $response->assertSessionHas('success');

        $usuario = Usuario::where('nombre_usuario', 'dra.valeria')->first();
        $this->assertNotNull($usuario);
        $this->assertEquals($profesional->id_persona, $usuario->id_persona);
        $this->assertTrue(Hash::check('ValeriaPass123!', $usuario->password_hash));
        $this->assertTrue($usuario->roles->contains('id_rol', $rol->id_rol));
    }
}
