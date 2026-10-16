<?php

namespace Tests\Feature;

use App\Models\Cirugia;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CirugiaWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $veterinario;

    private Usuario $recepcion;

    private Paciente $paciente;

    private Profesional $cirujano;

    protected function setUp(): void
    {
        parent::setUp();

        $rolVet = Rol::create(['nombre' => 'VETERINARIO', 'descripcion' => 'Médico Veterinario']);
        $rolRec = Rol::create(['nombre' => 'RECEPCION', 'descripcion' => 'Recepción']);

        $personaVet = Persona::create([
            'nombre' => 'Dr. Carlos',
            'apellido1' => 'Mendoza',
            'documento' => 'VET-998877',
            'telefono' => '70011223',
            'email' => 'carlos@lapetdiatria.com',
        ]);

        $this->cirujano = Profesional::create([
            'id_persona' => $personaVet->id_persona,
            'especialidad' => 'Cirugía y Traumatología',
            'registro_profesional' => 'MP-8833',
        ]);

        $this->veterinario = Usuario::factory()->create([
            'id_persona' => $personaVet->id_persona,
            'nombre_usuario' => 'dr_carlos',
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->attach($rolVet->id_rol);

        $personaRec = Persona::create([
            'nombre' => 'Ana',
            'apellido1' => 'Recepción',
            'documento' => 'REC-112233',
        ]);
        $this->recepcion = Usuario::factory()->create([
            'id_persona' => $personaRec->id_persona,
            'nombre_usuario' => 'ana_rec',
            'estado' => 'ACTIVO',
        ]);
        $this->recepcion->roles()->attach($rolRec->id_rol);

        $personaProp = Persona::create([
            'nombre' => 'Mateo',
            'apellido1' => 'Gutiérrez',
            'documento' => '6543210-CB',
            'telefono' => '71234567',
        ]);
        $propietario = Propietario::create([
            'id_persona' => $personaProp->id_persona,
        ]);

        $especie = Especie::create(['nombre' => 'Canino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Golden Retriever']);

        $this->paciente = Paciente::create([
            'id_raza' => $raza->id_raza,
            'id_propietario' => $propietario->id_persona,
            'nombre' => 'Rocky',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2022-05-10',
            'tamanio' => 'GRANDE',
            'condicion_reproductiva' => 'INTACTO',
            'color' => 'Dorado',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_cirugias(): void
    {
        $response = $this->get('/cirugias');
        $response->assertRedirect('/login');
    }

    public function test_veterinario_can_view_cirugias_index(): void
    {
        $response = $this->actingAs($this->veterinario)->get('/cirugias');
        $response->assertStatus(200);
        $response->assertSee('Quirófano y Cirugías');
        $response->assertSee('Programar Cirugía');
        $response->assertSee('Cirugías Hoy');
        $response->assertSee('En Quirófano');
    }

    public function test_veterinario_can_schedule_surgery_with_asa_evaluation(): void
    {
        $response = $this->actingAs($this->veterinario)->post('/cirugias', [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo_cirugia' => 'Ovariohisterectomía (OVH)',
            'fecha_programada' => '2026-10-01 09:30:00',
            'diagnostico_preoperatorio' => 'Esterilización electiva de rutina',
            'riesgo_asa' => 'ASA I',
            'tipo_anestesia' => 'Inhalatoria (Isoflurano)',
            'ayuno_solidos_horas' => 8,
            'ayuno_liquidos_horas' => 2,
            'id_cirujano' => $this->cirujano->id_persona,
            'observaciones_prequirurgicas' => 'Canino en excelente estado de carnes.',
        ]);

        $cirugia = Cirugia::where('tipo_cirugia', 'Ovariohisterectomía (OVH)')->first();
        $this->assertNotNull($cirugia);

        $response->assertRedirect('/cirugias/'.$cirugia->id_procedimiento);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cirugias', [
            'id_procedimiento' => $cirugia->id_procedimiento,
            'tipo_cirugia' => 'Ovariohisterectomía (OVH)',
            'riesgo_asa' => 'ASA I',
            'estado' => 'PROGRAMADA',
            'ayuno_solidos_horas' => 8,
        ]);
    }

    public function test_veterinario_can_view_surgical_sheet(): void
    {
        // Programar una cirugía previa
        $this->actingAs($this->veterinario)->post('/cirugias', [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo_cirugia' => 'Castración / Orquiectomía',
            'fecha_programada' => '2026-10-02 10:00:00',
            'riesgo_asa' => 'ASA I',
        ]);

        $cirugia = Cirugia::where('tipo_cirugia', 'Castración / Orquiectomía')->first();

        $response = $this->actingAs($this->veterinario)->get('/cirugias/'.$cirugia->id_procedimiento);
        $response->assertStatus(200);
        $response->assertSee('Rocky');
        $response->assertSee('Castración / Orquiectomía');
        $response->assertSee('Evaluación Pre-Quirúrgica y Anestésica');
        $response->assertSee('Consentimiento Informado');
    }

    public function test_veterinario_can_update_surgical_status_to_en_quirofano_and_completada(): void
    {
        $this->actingAs($this->veterinario)->post('/cirugias', [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo_cirugia' => 'Cistotomía',
            'fecha_programada' => '2026-10-03 11:00:00',
            'riesgo_asa' => 'ASA II',
        ]);

        $cirugia = Cirugia::where('tipo_cirugia', 'Cistotomía')->first();

        // 1. Ingresar a quirófano
        $resEnQuirofano = $this->actingAs($this->veterinario)->post("/cirugias/{$cirugia->id_procedimiento}/estado", [
            'estado' => 'EN_QUIROFANO',
        ]);
        $resEnQuirofano->assertRedirect('/cirugias/'.$cirugia->id_procedimiento);
        $this->assertDatabaseHas('cirugias', [
            'id_procedimiento' => $cirugia->id_procedimiento,
            'estado' => 'EN_QUIROFANO',
        ]);

        // 2. Finalizar cirugía con reporte
        $resCompletar = $this->actingAs($this->veterinario)->post("/cirugias/{$cirugia->id_procedimiento}/estado", [
            'estado' => 'COMPLETADA',
            'resultado' => 'EXITOSA',
            'complicaciones' => 'Sin complicaciones ni pérdidas hemáticas significativas.',
            'indicaciones_postoperatorias' => 'Amoxicilina + Clavulánico 250mg cada 12h por 7 días. Collar isabelino.',
        ]);
        $resCompletar->assertRedirect('/cirugias/'.$cirugia->id_procedimiento);

        $this->assertDatabaseHas('cirugias', [
            'id_procedimiento' => $cirugia->id_procedimiento,
            'estado' => 'COMPLETADA',
            'resultado' => 'EXITOSA',
        ]);
    }

    public function test_veterinario_can_record_informed_consent_signature(): void
    {
        $this->actingAs($this->veterinario)->post('/cirugias', [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo_cirugia' => 'Limpieza Dental',
            'fecha_programada' => '2026-10-04 14:00:00',
            'riesgo_asa' => 'ASA I',
        ]);

        $cirugia = Cirugia::where('tipo_cirugia', 'Limpieza Dental')->first();
        $this->assertFalse($cirugia->autorizacion_firmada);

        $response = $this->actingAs($this->veterinario)->post("/cirugias/{$cirugia->id_procedimiento}/consentimiento", [
            'firma_propietario_nombre' => 'Mateo Gutiérrez',
            'firma_propietario_documento' => '6543210-CB',
        ]);

        $response->assertRedirect('/cirugias/'.$cirugia->id_procedimiento);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cirugias', [
            'id_procedimiento' => $cirugia->id_procedimiento,
            'autorizacion_firmada' => true,
            'firma_propietario_nombre' => 'Mateo Gutiérrez',
            'firma_propietario_documento' => '6543210-CB',
        ]);
    }

    public function test_veterinario_can_generate_informed_consent_pdf(): void
    {
        $this->actingAs($this->veterinario)->post('/cirugias', [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo_cirugia' => 'Osteosíntesis de Fémur',
            'fecha_programada' => '2026-10-05 16:00:00',
            'riesgo_asa' => 'ASA III',
            'id_cirujano' => $this->cirujano->id_persona,
        ]);

        $cirugia = Cirugia::where('tipo_cirugia', 'Osteosíntesis de Fémur')->first();

        $response = $this->actingAs($this->veterinario)->get("/cirugias/{$cirugia->id_procedimiento}/consentimiento/pdf");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
