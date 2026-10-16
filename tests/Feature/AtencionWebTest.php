<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\AtencionServicio;
use App\Models\AtencionVeterinaria;
use App\Models\Cita;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtencionWebTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioConRol(string $nombreRol): Usuario
    {
        $persona = Persona::create([
            'documento' => 'DOC-'.uniqid(),
            'nombre' => 'Test',
            'apellido1' => $nombreRol,
            'email' => strtolower(str_replace(['-', ' '], '_', $nombreRol)).'_'.uniqid().'@petdiatria.com',
        ]);

        $rol = Rol::firstOrCreate(['nombre' => $nombreRol], ['descripcion' => 'Rol '.$nombreRol]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'user_'.strtolower(str_replace(['-', ' '], '_', $nombreRol)).'_'.uniqid(),
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);

        $usuario->roles()->sync([$rol->id_rol]);

        return $usuario;
    }

    private function crearEntornoBase(): array
    {
        $especie = Especie::create(['nombre' => 'Canino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Labrador']);

        $personaProp = Persona::create(['documento' => 'PROP-1', 'nombre' => 'María', 'apellido1' => 'Suárez']);
        $propietario = Propietario::create(['id_persona' => $personaProp->id_persona]);

        $paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_especie' => $especie->id_especie,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Toby',
            'estado' => 'ACTIVO',
        ]);

        $personaProf = Persona::create(['documento' => 'VET-1', 'nombre' => 'Carlos', 'apellido1' => 'Mendoza']);
        $profesional = Profesional::create([
            'id_persona' => $personaProf->id_persona,
            'especialidad' => 'Cirugía',
            'registro_profesional' => 'MAT-1234',
            'estado' => 'ACTIVO',
        ]);

        $servicio = Servicio::create([
            'nombre' => 'Consulta General',
            'precio_base' => 120,
            'tipo_servicio' => 'CLINICO',
            'estado' => 'ACTIVO',
        ]);

        return compact('paciente', 'profesional', 'servicio');
    }

    public function test_veterinario_puede_ver_listado_de_atenciones(): void
    {
        $vet = $this->crearUsuarioConRol('Veterinario');
        $base = $this->crearEntornoBase();

        $atencion = Atencion::create([
            'id_paciente' => $base['paciente']->id_paciente,
            'fecha_hora_inicio' => Carbon::now(),
            'estado' => 'EN_CURSO',
            'total_final' => 120,
        ]);
        AtencionVeterinaria::create([
            'id_atencion' => $atencion->id_atencion,
            'motivo_consulta' => 'Control de peso',
        ]);

        $response = $this->actingAs($vet)->get(route('atenciones.index'));

        $response->assertStatus(200);
        $response->assertSee('Atenciones Clínicas y Estéticas');
        $response->assertSee('Toby');
        $response->assertSee('Iniciar Atención');
    }

    public function test_veterinario_puede_iniciar_atencion_medica_directa(): void
    {
        $vet = $this->crearUsuarioConRol('Veterinario');
        $base = $this->crearEntornoBase();

        $data = [
            'id_paciente' => $base['paciente']->id_paciente,
            'id_profesional' => $base['profesional']->id_persona,
            'tipo_atencion' => 'VETERINARIA',
            'fecha_hora_inicio' => Carbon::now()->format('Y-m-d\TH:i'),
            'estado' => 'EN_CURSO',
            'motivo_consulta' => 'Fiebre y decaimiento',
            'anamnesis' => 'Comenzó ayer por la tarde',
            'sintomas' => 'Temperatura 39.5C',
            'conclusion' => 'Infección respiratoria leve',
            'servicios' => [
                [
                    'id_servicio' => $base['servicio']->id_servicio,
                    'cantidad' => 1,
                    'descuento' => 10,
                ],
            ],
        ];

        $response = $this->actingAs($vet)->post(route('atenciones.store'), $data);

        $atencion = Atencion::where('id_paciente', $base['paciente']->id_paciente)->first();
        $this->assertNotNull($atencion);
        $this->assertEquals('EN_CURSO', $atencion->estado);
        $this->assertEquals(110.0, (float) $atencion->total_final); // 120 - 10 = 110
        $this->assertNotNull($atencion->atencionVeterinaria);
        $this->assertEquals('Fiebre y decaimiento', $atencion->atencionVeterinaria->motivo_consulta);

        $response->assertRedirect(route('atenciones.show', $atencion->id_atencion));
    }

    public function test_spa_puede_iniciar_atencion_estetica(): void
    {
        $spa = $this->crearUsuarioConRol('SPA');
        $base = $this->crearEntornoBase();

        $servicioGrooming = Servicio::create([
            'nombre' => 'Baño y Corte Canino',
            'precio_base' => 150,
            'tipo_servicio' => 'ESTETICO',
            'estado' => 'ACTIVO',
        ]);

        $data = [
            'id_paciente' => $base['paciente']->id_paciente,
            'id_profesional' => $base['profesional']->id_persona,
            'tipo_atencion' => 'ESTETICA',
            'fecha_hora_inicio' => Carbon::now()->format('Y-m-d\TH:i'),
            'estado' => 'EN_CURSO',
            'modalidad' => 'BAÑO Y CORTE',
            'estado_pelaje' => 'ENREDADO_LEVE',
            'caracteristicas_observadas' => 'Corte estilo cachorro',
            'servicios' => [
                [
                    'id_servicio' => $servicioGrooming->id_servicio,
                    'cantidad' => 1,
                    'descuento' => 0,
                ],
            ],
        ];

        $response = $this->actingAs($spa)->post(route('atenciones.store'), $data);

        $atencion = Atencion::where('id_paciente', $base['paciente']->id_paciente)->first();
        $this->assertNotNull($atencion);
        $this->assertNotNull($atencion->atencionEstetica);
        $this->assertEquals('BAÑO Y CORTE', $atencion->atencionEstetica->modalidad);
        $this->assertEquals('ENREDADO_LEVE', $atencion->atencionEstetica->estado_pelaje);

        $response->assertRedirect(route('atenciones.show', $atencion->id_atencion));
    }

    public function test_iniciar_atencion_desde_cita_actualiza_cita_a_atendida(): void
    {
        $vet = $this->crearUsuarioConRol('Veterinario');
        $base = $this->crearEntornoBase();

        $cita = Cita::create([
            'id_paciente' => $base['paciente']->id_paciente,
            'id_profesional' => $base['profesional']->id_persona,
            'fecha_hora_inicio' => Carbon::now()->addHour(),
            'fecha_hora_fin' => Carbon::now()->addHours(2),
            'estado' => 'CONFIRMADA',
        ]);

        $data = [
            'id_paciente' => $base['paciente']->id_paciente,
            'id_cita' => $cita->id_cita,
            'id_profesional' => $base['profesional']->id_persona,
            'tipo_atencion' => 'VETERINARIA',
            'fecha_hora_inicio' => Carbon::now()->format('Y-m-d\TH:i'),
            'estado' => 'EN_CURSO',
        ];

        $response = $this->actingAs($vet)->post(route('atenciones.store'), $data);

        $response->assertSessionHas('success');
        $this->assertEquals('ATENDIDA', $cita->fresh()->estado);
    }

    public function test_veterinario_puede_finalizar_atencion_en_curso(): void
    {
        $vet = $this->crearUsuarioConRol('Veterinario');
        $base = $this->crearEntornoBase();

        $atencion = Atencion::create([
            'id_paciente' => $base['paciente']->id_paciente,
            'fecha_hora_inicio' => Carbon::now()->subMinutes(30),
            'estado' => 'EN_CURSO',
            'total_final' => 120,
        ]);
        AtencionServicio::create([
            'id_atencion' => $atencion->id_atencion,
            'id_servicio' => $base['servicio']->id_servicio,
            'cantidad' => 1,
            'precio_unitario' => 120,
            'descuento' => 0,
        ]);

        $response = $this->actingAs($vet)->post(route('atenciones.finalizar', $atencion->id_atencion), [
            'observaciones' => 'Paciente respondió bien al tratamiento',
            'recomendaciones' => 'Reposo por 48 horas',
        ]);

        $response->assertSessionHas('success');
        $atencionFresca = $atencion->fresh();
        $this->assertEquals('FINALIZADA', $atencionFresca->estado);
        $this->assertNotNull($atencionFresca->fecha_hora_fin);
        $this->assertEquals(120.0, (float) $atencionFresca->total_final);
    }

    public function test_veterinario_puede_cancelar_atencion_con_motivo(): void
    {
        $vet = $this->crearUsuarioConRol('Veterinario');
        $base = $this->crearEntornoBase();

        $atencion = Atencion::create([
            'id_paciente' => $base['paciente']->id_paciente,
            'fecha_hora_inicio' => Carbon::now(),
            'estado' => 'EN_CURSO',
            'total_final' => 0,
        ]);

        $response = $this->actingAs($vet)->post(route('atenciones.cancelar', $atencion->id_atencion), [
            'motivo' => 'Tutor debió retirarse por urgencia familiar',
        ]);

        $response->assertSessionHas('success');
        $atencionFresca = $atencion->fresh();
        $this->assertEquals('CANCELADA', $atencionFresca->estado);
        $this->assertStringContainsString('urgencia familiar', $atencionFresca->observaciones);
    }

    public function test_admin_tiene_acceso_de_solo_lectura_a_atenciones(): void
    {
        $admin = $this->crearUsuarioConRol('Admin');
        $base = $this->crearEntornoBase();

        $atencion = Atencion::create([
            'id_paciente' => $base['paciente']->id_paciente,
            'fecha_hora_inicio' => Carbon::now(),
            'estado' => 'EN_CURSO',
            'total_final' => 100,
        ]);

        $response = $this->actingAs($admin)->get(route('atenciones.index'));
        $response->assertStatus(200);
        $response->assertDontSee('Iniciar Atención');

        $responseShow = $this->actingAs($admin)->get(route('atenciones.show', $atencion->id_atencion));
        $responseShow->assertStatus(200);
        $responseShow->assertDontSee('Finalizar Atención');
        $responseShow->assertDontSee('Cancelar Atención');
    }

    public function test_transporte_no_tiene_acceso_a_modulo_atenciones(): void
    {
        $transporte = $this->crearUsuarioConRol('Transporte');

        $response = $this->actingAs($transporte)->get(route('atenciones.index'));
        $response->assertStatus(403);
    }
}
