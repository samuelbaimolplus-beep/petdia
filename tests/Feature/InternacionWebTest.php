<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Especie;
use App\Models\Internacion;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternacionWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $superAdmin;

    protected Usuario $veterinario;

    protected Usuario $sinPermiso;

    protected Profesional $profesional;

    protected Paciente $paciente;

    protected AtencionVeterinaria $atencionVet;

    protected function setUp(): void
    {
        parent::setUp();

        $rolSuperAdmin = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Admin']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Veterinario']);
        $rolTransporte = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Transporte']);

        $pAdmin = Persona::create(['nombre' => 'Admin', 'apellido1' => 'Hosp', 'documento' => '8001', 'email' => 'admin_hosp@test.com', 'estado' => 'ACTIVO']);
        $pVet = Persona::create(['nombre' => 'Dra. Laura', 'apellido1' => 'Mendoza', 'documento' => '8002', 'email' => 'vet_hosp@test.com', 'estado' => 'ACTIVO']);
        $pSin = Persona::create(['nombre' => 'Chofer', 'apellido1' => 'Gomez', 'documento' => '8003', 'email' => 'chofer@test.com', 'estado' => 'ACTIVO']);

        $this->superAdmin = Usuario::create([
            'id_persona' => $pAdmin->id_persona,
            'nombre_usuario' => 'admin_hosp',
            'password_hash' => bcrypt('secret'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->sync([$rolSuperAdmin->id_rol]);

        $this->veterinario = Usuario::create([
            'id_persona' => $pVet->id_persona,
            'nombre_usuario' => 'vet_hosp',
            'password_hash' => bcrypt('secret'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->sync([$rolVet->id_rol]);

        $this->sinPermiso = Usuario::create([
            'id_persona' => $pSin->id_persona,
            'nombre_usuario' => 'chofer_hosp',
            'password_hash' => bcrypt('secret'),
            'estado' => 'ACTIVO',
        ]);
        $this->sinPermiso->roles()->sync([$rolTransporte->id_rol]);

        $this->profesional = Profesional::create([
            'id_persona' => $pVet->id_persona,
            'numero_colegiado' => 'VET-8822',
            'especialidad' => 'Cirugía y Cuidados Intensivos',
            'estado' => 'ACTIVO',
        ]);

        $pCliente = Persona::create(['nombre' => 'Carlos', 'apellido1' => 'Guzman', 'documento' => '99001', 'email' => 'carlos@test.com', 'telefono' => '70011223', 'estado' => 'ACTIVO']);
        $prop = Propietario::create(['id_persona' => $pCliente->id_persona, 'estado' => 'ACTIVO']);

        $especie = Especie::create(['nombre' => 'Canino', 'estado' => 'ACTIVO']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Golden Retriever', 'estado' => 'ACTIVO']);

        $this->paciente = Paciente::create([
            'id_propietario' => $prop->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Rocky',
            'sexo' => 'M',
            'estado' => 'ACTIVO',
        ]);

        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'EN_PROCESO',
            'tipo' => 'CLINICA',
        ]);

        $this->atencionVet = AtencionVeterinaria::create([
            'id_atencion' => $atencion->id_atencion,
            'motivo_consulta' => 'Gastroenteritis aguda y letargia severa',
            'conclusion' => 'Requiere fluidoterapia y monitoreo hospitalario',
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_internaciones(): void
    {
        $response = $this->actingAs($this->sinPermiso)->get(route('internaciones.index'));
        $response->assertStatus(403);
    }

    public function test_veterinario_puede_ver_tablero_de_caniles_y_kpis(): void
    {
        $response = $this->actingAs($this->veterinario)->get(route('internaciones.index'));

        $response->assertStatus(200);
        $response->assertSee('Hospitalización e Internación');
        $response->assertSee('Tablero de Caniles y Áreas Hospitalarias');
        $response->assertSee('Canil C-1');
        $response->assertSee('Caniles Libres');
    }

    public function test_puede_ingresar_paciente_a_hospitalizacion(): void
    {
        $datos = [
            'id_atencion_ingreso' => $this->atencionVet->id_atencion,
            'jaula_numero' => 'Canil C-1 (Caninos Gdes)',
            'motivo' => 'Gastroenteritis con deshidratación 7%',
            'observaciones' => 'Ringer Lactato a 30 gotas/min. Ayuno primeras 12h.',
        ];

        $response = $this->actingAs($this->veterinario)->post(route('internaciones.store'), $datos);

        $this->assertDatabaseHas('internaciones', [
            'id_atencion_ingreso' => $this->atencionVet->id_atencion,
            'jaula_numero' => 'Canil C-1 (Caninos Gdes)',
            'motivo' => 'Gastroenteritis con deshidratación 7%',
            'estado' => 'ACTIVA',
        ]);

        $internacion = Internacion::where('id_atencion_ingreso', $this->atencionVet->id_atencion)->first();
        $response->assertRedirect(route('internaciones.show', $internacion->id_internacion));
    }

    public function test_puede_ver_ficha_de_internacion_y_linea_de_tiempo(): void
    {
        $internacion = Internacion::create([
            'id_atencion_ingreso' => $this->atencionVet->id_atencion,
            'fecha_hora_ingreso' => now(),
            'motivo' => 'Monitoreo postquirúrgico',
            'estado' => 'ACTIVA',
            'jaula_numero' => 'UCI-1 (Cuidados Críticos)',
        ]);

        $response = $this->actingAs($this->veterinario)->get(route('internaciones.show', $internacion->id_internacion));

        $response->assertStatus(200);
        $response->assertSee('Rocky');
        $response->assertSee('UCI-1 (Cuidados Críticos)');
        $response->assertSee('Bitácora de Evolución por Turnos');
        $response->assertSee('Registrar Evolución de Turno');
    }

    public function test_puede_registrar_evolucion_de_turno_con_signos_vitales(): void
    {
        $internacion = Internacion::create([
            'id_atencion_ingreso' => $this->atencionVet->id_atencion,
            'fecha_hora_ingreso' => now()->subDay(),
            'motivo' => 'Tratamiento parvovirus',
            'estado' => 'ACTIVA',
            'jaula_numero' => 'Aislamiento A-1 (Infecciosos)',
        ]);

        $datosSeg = [
            'id_profesional' => $this->profesional->id_persona,
            'temperatura' => 38.6,
            'frecuencia_cardiaca' => 115,
            'frecuencia_respiratoria' => 26,
            'peso' => 28.5,
            'estado_animo' => 'Alerta y Activo',
            'alimentacion' => 'Come voluntario',
            'hidratacion' => 'Hidratado',
            'deposiciones' => 'Blandas',
            'orina' => 'Normal',
            'cuidados' => 'Metronidazol 500mg IV + Ondansetrón 4mg SC',
            'observaciones' => 'Paciente responde favorablemente, buen ánimo y alerta a estímulos.',
        ];

        $response = $this->actingAs($this->veterinario)
            ->post(route('internaciones.seguimiento.store', $internacion->id_internacion), $datosSeg);

        $response->assertRedirect(route('internaciones.show', $internacion->id_internacion));

        $this->assertDatabaseHas('seguimiento_internaciones', [
            'id_internacion' => $internacion->id_internacion,
            'id_profesional' => $this->profesional->id_persona,
            'temperatura' => 38.6,
            'frecuencia_cardiaca' => 115,
            'estado_animo' => 'Alerta y Activo',
        ]);
    }

    public function test_puede_dar_alta_medica_y_liberar_canil(): void
    {
        $internacion = Internacion::create([
            'id_atencion_ingreso' => $this->atencionVet->id_atencion,
            'fecha_hora_ingreso' => now()->subDays(3),
            'motivo' => 'Control postquirúrgico',
            'estado' => 'ACTIVA',
            'jaula_numero' => 'Canil C-3 (Caninos Med)',
        ]);

        $response = $this->actingAs($this->veterinario)
            ->post(route('internaciones.alta', $internacion->id_internacion), [
                'observaciones_alta' => 'Herida quirúrgica sana y cicatrizando. Continúa reposo en casa 5 días.',
            ]);

        $response->assertRedirect(route('internaciones.show', $internacion->id_internacion));

        $internacion->refresh();
        $this->assertEquals('ALTA', $internacion->estado);
        $this->assertNotNull($internacion->fecha_hora_alta);
        $this->assertStringContainsString('ALTA MÉDICA', $internacion->observaciones);
    }

    public function test_puede_reubicar_canil(): void
    {
        $internacion = Internacion::create([
            'id_atencion_ingreso' => $this->atencionVet->id_atencion,
            'fecha_hora_ingreso' => now(),
            'motivo' => 'UCI inicial',
            'estado' => 'ACTIVA',
            'jaula_numero' => 'UCI-1 (Cuidados Críticos)',
        ]);

        $response = $this->actingAs($this->veterinario)
            ->post(route('internaciones.cambiar-jaula', $internacion->id_internacion), [
                'jaula_numero' => 'Canil C-2 (Caninos Gdes)',
            ]);

        $response->assertRedirect();
        $internacion->refresh();
        $this->assertEquals('Canil C-2 (Caninos Gdes)', $internacion->jaula_numero);
    }
}
