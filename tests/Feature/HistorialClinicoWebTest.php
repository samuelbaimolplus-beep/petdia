<?php

namespace Tests\Feature;

use App\Models\Especie;
use App\Models\EstudioLaboratorio;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistorialClinicoWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $veterinario;

    private Usuario $recepcion;

    private Usuario $superAdmin;

    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $rolVet = Rol::create(['nombre' => 'VETERINARIO', 'descripcion' => 'Veterinario']);
        $rolRec = Rol::create(['nombre' => 'RECEPCION', 'descripcion' => 'Recepción']);
        $rolSuper = Rol::create(['nombre' => 'SUPER_ADMIN', 'descripcion' => 'Super Admin']);

        // Persona & Usuario Veterinario
        $personaVet = Persona::create([
            'documento' => '11223344',
            'nombre' => 'Dra. Andrea',
            'apellido1' => 'Sánchez',
            'email' => 'andrea@lapetdiatria.com',
        ]);
        Profesional::create([
            'id_persona' => $personaVet->id_persona,
            'especialidad' => 'Medicina Interna',
            'registro_profesional' => 'COLVET-987',
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario = Usuario::factory()->create([
            'id_persona' => $personaVet->id_persona,
            'nombre_usuario' => 'dra_andrea',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->attach($rolVet->id_rol);

        // Persona & Usuario Recepción
        $personaRec = Persona::create([
            'documento' => '55667788',
            'nombre' => 'Pedro',
            'apellido1' => 'Cajero',
            'email' => 'pedro@lapetdiatria.com',
        ]);
        $this->recepcion = Usuario::factory()->create([
            'id_persona' => $personaRec->id_persona,
            'nombre_usuario' => 'pedro_rec',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->recepcion->roles()->attach($rolRec->id_rol);

        // Persona & Usuario Super-Admin
        $personaSuper = Persona::create([
            'documento' => '99887766',
            'nombre' => 'Carlos',
            'apellido1' => 'Admin',
            'email' => 'carlos@lapetdiatria.com',
        ]);
        $this->superAdmin = Usuario::factory()->create([
            'id_persona' => $personaSuper->id_persona,
            'nombre_usuario' => 'carlos_super',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->attach($rolSuper->id_rol);

        // Paciente con Propietario
        $personaCliente = Persona::create([
            'documento' => '44332211',
            'nombre' => 'Valeria',
            'apellido1' => 'Molina',
            'email' => 'valeria@email.com',
        ]);
        $propietario = Propietario::create(['id_persona' => $personaCliente->id_persona]);

        $especie = Especie::create(['nombre' => 'Canino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Golden Retriever']);

        $this->paciente = Paciente::create([
            'id_raza' => $raza->id_raza,
            'id_propietario' => $propietario->id_persona,
            'nombre' => 'Simba',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2021-03-20',
            'tamanio' => 'GRANDE',
            'condicion_reproductiva' => 'INTACTO',
            'color' => 'Dorado',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_historial(): void
    {
        $response = $this->get('/historial-clinico');
        $response->assertRedirect('/login');
    }

    public function test_unauthorized_role_sees_restricted_access_screen(): void
    {
        // El personal de recepción ve la pantalla de acceso restringido
        $response = $this->actingAs($this->recepcion)->get('/historial-clinico/'.$this->paciente->id_paciente);
        $response->assertStatus(200);
        $response->assertSee('Información Médica Restringida');
        $response->assertSee('Expediente Clínico Reservado');
        $response->assertSee('Simba');
        // No debe mostrar el formulario médico ni notas confidenciales
        $response->assertDontSee('Nueva Entrada Clínica');
    }

    public function test_veterinario_can_view_historial_index(): void
    {
        $response = $this->actingAs($this->veterinario)->get('/historial-clinico');
        $response->assertStatus(200);
        $response->assertSee('Historial Clínico Veterinario');
        $response->assertSee('Simba');
        $response->assertSee('Valeria Molina');
        $response->assertSee('Abrir Expediente');
    }

    public function test_veterinario_can_view_patient_medical_record_with_tabs(): void
    {
        $response = $this->actingAs($this->veterinario)->get('/historial-clinico/'.$this->paciente->id_paciente);
        $response->assertStatus(200);
        $response->assertSee('Simba');
        $response->assertSee('Línea de Tiempo');
        $response->assertSee('Diagnósticos');
        $response->assertSee('Tratamientos');
        $response->assertSee('Estudios y Lab');
        $response->assertSee('Cirugías e Internación');
        $response->assertSee('Vacunas y Desparasitación');
        $response->assertSee('Recetas');
        $response->assertSee('Nueva Entrada Clínica');
    }

    public function test_veterinario_can_store_clinical_entry_with_vitals_diagnosis_and_treatment(): void
    {
        $response = $this->actingAs($this->veterinario)->post('/historial-clinico/'.$this->paciente->id_paciente.'/entradas', [
            'tipo' => 'CONSULTA',
            'motivo_consulta' => 'Control de peso y decaimiento leve',
            'anamnesis' => 'El paciente presenta apetito disminuido desde hace 2 días tras cambio de alimento.',
            'sintomas' => 'Letargia moderada, sin vómitos.',
            'examen_fisico' => 'Mucosas rosadas, tiempo de llenado capilar normal, abdomen indoloro.',
            'conclusion' => 'Indigestión leve por cambio brusco de dieta.',
            'fecha_reconsulta' => '2026-09-30',
            'observaciones_clinicas' => 'Mantener hidratación.',
            'peso' => 28.5,
            'temperatura' => 38.6,
            'frecuencia_cardiaca' => 105,
            'frecuencia_respiratoria' => 22,
            'diagnostico_nombre' => 'Gastroenteropatía dietética leve',
            'diagnostico_tipo' => 'PRESUNTIVO',
            'diagnostico_descripcion' => 'Sensibilidad alimentaria transitoria.',
            'tratamiento_descripcion' => 'Dieta blanda gastroentérica y probióticos.',
            'tratamiento_indicaciones' => '1 sobre diario con alimento por 7 días.',
        ]);

        $response->assertRedirect('/historial-clinico/'.$this->paciente->id_paciente);
        $response->assertSessionHas('success');

        // Verificar inserciones en base de datos
        $this->assertDatabaseHas('atenciones', [
            'id_paciente' => $this->paciente->id_paciente,
            'estado' => 'COMPLETADA',
        ]);

        $this->assertDatabaseHas('atencion_veterinarias', [
            'motivo_consulta' => 'Control de peso y decaimiento leve',
            'tipo' => 'CONSULTA',
        ]);

        $this->assertDatabaseHas('registro_signos_vitales', [
            'peso' => 28.5,
            'temperatura' => 38.6,
        ]);

        $this->assertDatabaseHas('diagnosticos', [
            'nombre' => 'Gastroenteropatía dietética leve',
            'tipo' => 'PRESUNTIVO',
        ]);

        $this->assertDatabaseHas('tratamientos', [
            'id_paciente' => $this->paciente->id_paciente,
            'descripcion' => 'Dieta blanda gastroentérica y probióticos.',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_unauthorized_user_cannot_store_clinical_entry(): void
    {
        $response = $this->actingAs($this->recepcion)->post('/historial-clinico/'.$this->paciente->id_paciente.'/entradas', [
            'tipo' => 'CONSULTA',
            'motivo_consulta' => 'Intento de registro sin rol veterinario',
            'anamnesis' => 'Sin autorización.',
        ]);

        $response->assertStatus(403);
    }

    public function test_veterinario_can_upload_laboratory_study_with_pdf_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('hemograma_simba.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->veterinario)->post('/historial-clinico/'.$this->paciente->id_paciente.'/estudios', [
            'tipo_estudio' => 'Hemograma Completo',
            'fecha_resultado' => '2026-09-27',
            'resultado' => 'Glóbulos rojos y blancos normales.',
            'interpretacion' => 'Sin signos de anemia ni leucocitosis.',
            'archivo' => $file,
        ]);

        $response->assertRedirect('/historial-clinico/'.$this->paciente->id_paciente.'?tab=estudios');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('estudio_laboratorios', [
            'tipo_estudio' => 'Hemograma Completo',
            'resultado' => 'Glóbulos rojos y blancos normales.',
            'interpretacion' => 'Sin signos de anemia ni leucocitosis.',
            'nombre_archivo_original' => 'hemograma_simba.pdf',
        ]);

        $estudio = EstudioLaboratorio::first();
        $this->assertNotNull($estudio);
        Storage::disk('public')->assertExists($estudio->archivo_adjunto);
    }

    public function test_veterinario_can_upload_laboratory_study_with_image_attachment(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('radiografia_torax.png', 800, 'image/png');

        $response = $this->actingAs($this->veterinario)->post('/historial-clinico/'.$this->paciente->id_paciente.'/estudios', [
            'tipo_estudio' => 'Radiografía de Tórax',
            'fecha_resultado' => '2026-09-27',
            'resultado' => 'Campos pulmonares libres.',
            'interpretacion' => 'Silueta cardíaca normal.',
            'archivo' => $file,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/historial-clinico/'.$this->paciente->id_paciente.'?tab=estudios');

        $this->assertDatabaseHas('estudio_laboratorios', [
            'tipo_estudio' => 'Radiografía de Tórax',
            'nombre_archivo_original' => 'radiografia_torax.png',
        ]);

        $estudio = EstudioLaboratorio::where('tipo_estudio', 'Radiografía de Tórax')->first();
        $this->assertNotNull($estudio);
        $this->assertTrue($estudio->esImagen());
    }

    public function test_veterinario_can_delete_laboratory_study(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('coprologico.pdf', 500, 'application/pdf');

        $postRes = $this->actingAs($this->veterinario)->post('/historial-clinico/'.$this->paciente->id_paciente.'/estudios', [
            'tipo_estudio' => 'Coproparasitológico',
            'archivo' => $file,
        ]);

        $postRes->assertSessionHasNoErrors();
        $estudio = EstudioLaboratorio::where('tipo_estudio', 'Coproparasitológico')->first();
        $this->assertNotNull($estudio);
        $path = $estudio->archivo_adjunto;
        $idProcedimiento = $estudio->id_procedimiento;

        $deleteResponse = $this->actingAs($this->veterinario)
            ->delete("/historial-clinico/{$this->paciente->id_paciente}/estudios/{$idProcedimiento}");

        $deleteResponse->assertRedirect('/historial-clinico/'.$this->paciente->id_paciente.'?tab=estudios');
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('estudio_laboratorios', [
            'id_procedimiento' => $idProcedimiento,
        ]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_unauthorized_user_cannot_upload_laboratory_study(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('analisis.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->recepcion)->post('/historial-clinico/'.$this->paciente->id_paciente.'/estudios', [
            'tipo_estudio' => 'Análisis Clínico',
            'archivo' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_file_validation_rejects_invalid_file_type(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('archivo.exe', 500, 'application/octet-stream');

        $response = $this->actingAs($this->veterinario)->post('/historial-clinico/'.$this->paciente->id_paciente.'/estudios', [
            'tipo_estudio' => 'Estudio',
            'archivo' => $file,
        ]);

        $response->assertSessionHasErrors(['archivo']);
    }
}
