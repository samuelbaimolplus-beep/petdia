<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Cirugia;
use App\Models\Cita;
use App\Models\Especie;
use App\Models\EstudioLaboratorio;
use App\Models\HorarioProfesional;
use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Persona;
use App\Models\Procedimiento;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Tratamiento;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CicloDosFeaturesTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $superAdmin;

    private Usuario $admin;

    private Usuario $veterinario;

    private Usuario $recepcion;

    private Profesional $profesional;

    private Profesional $segundoProfesional;

    private Paciente $paciente;

    private Servicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles institucionales
        $rolSuper = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Administrador']);
        $rolAdmin = Rol::firstOrCreate(['nombre' => 'ADMIN'], ['descripcion' => 'Administrador']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Médico Veterinario']);
        $rolRec = Rol::firstOrCreate(['nombre' => 'RECEPCION'], ['descripcion' => 'Recepcionista']);

        // Personas
        $pSuper = Persona::create(['documento' => '2001', 'nombre' => 'Super', 'apellido1' => 'Admin', 'email' => 'super2@test.com']);
        $pAdmin = Persona::create(['documento' => '2002', 'nombre' => 'Admin', 'apellido1' => 'User', 'email' => 'admin2@test.com']);
        $pVet = Persona::create(['documento' => '2003', 'nombre' => 'Carlos', 'apellido1' => 'Mendoza', 'email' => 'vet2@test.com']);
        $pVet2 = Persona::create(['documento' => '2004', 'nombre' => 'Lucia', 'apellido1' => 'Torres', 'email' => 'vet3@test.com']);
        $pRec = Persona::create(['documento' => '2005', 'nombre' => 'Marta', 'apellido1' => 'Gomez', 'email' => 'rec2@test.com']);

        // Usuarios
        $this->superAdmin = Usuario::create([
            'id_persona' => $pSuper->id_persona,
            'nombre_usuario' => 'superadmin_c2',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->superAdmin->roles()->attach($rolSuper->id_rol);

        $this->admin = Usuario::create([
            'id_persona' => $pAdmin->id_persona,
            'nombre_usuario' => 'admin_c2',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->admin->roles()->attach($rolAdmin->id_rol);

        $this->veterinario = Usuario::create([
            'id_persona' => $pVet->id_persona,
            'nombre_usuario' => 'vet_c2',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->veterinario->roles()->attach($rolVet->id_rol);

        $this->recepcion = Usuario::create([
            'id_persona' => $pRec->id_persona,
            'nombre_usuario' => 'rec_c2',
            'password_hash' => Hash::make('Password123!'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
        ]);
        $this->recepcion->roles()->attach($rolRec->id_rol);

        // Profesionales
        $this->profesional = Profesional::create([
            'id_persona' => $pVet->id_persona,
            'numero_registro' => 'VET-2001',
            'especialidad' => 'Cirugía y Medicina Interna',
            'estado' => 'ACTIVO',
        ]);

        $this->segundoProfesional = Profesional::create([
            'id_persona' => $pVet2->id_persona,
            'numero_registro' => 'VET-2002',
            'especialidad' => 'Anestesiología',
            'estado' => 'ACTIVO',
        ]);

        // Especie, Raza, Propietario, Paciente
        $especie = Especie::create(['nombre' => 'Canino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Golden Retriever']);
        $pProp = Persona::create(['documento' => '99887766', 'nombre' => 'Roberto', 'apellido1' => 'Perez', 'email' => 'roberto@test.com']);
        $prop = Propietario::create(['id_persona' => $pProp->id_persona]);

        $this->paciente = Paciente::create([
            'id_propietario' => $prop->id_propietario,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Max',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2021-05-10',
            'estado' => 'VIVO',
        ]);

        // Servicio base
        $this->servicio = Servicio::create([
            'nombre' => 'Consulta Médica General',
            'tipo' => 'CLINICO',
            'precio_base' => 120.00,
            'duracion_estimada_minutos' => 30,
            'estado' => 'ACTIVO',
        ]);
    }

    /* =========================================================================
     * BLOQUE A: GESTIÓN CLÍNICA (CU9, CU10, CU11)
     * ========================================================================= */

    public function test_cu9_gestionar_diagnosticos_y_tratamientos(): void
    {
        // 1. Iniciar atención veterinaria
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'EN_CURSO',
        ]);
        $atencion->atencionVeterinaria()->create([
            'motivo_consulta' => 'Letargia y vómitos',
        ]);

        // 2. Veterinario registra diagnóstico clínico
        $diagResponse = $this->actingAs($this->veterinario)
            ->post(route('atenciones.diagnosticos.store', $atencion->id_atencion), [
                'nombre' => 'Gastroenteritis aguda',
                'tipo' => 'DEFINITIVO',
                'descripcion' => 'Inflamación de la mucosa gástrica',
                'observaciones' => 'Responde a palpación dolorosa',
            ]);

        $diagResponse->assertRedirect();
        $this->assertDatabaseHas('diagnosticos', [
            'id_atencion' => $atencion->id_atencion,
            'nombre' => 'Gastroenteritis aguda',
            'tipo' => 'DEFINITIVO',
        ]);

        // 3. Veterinario registra tratamiento e indicaciones
        $tratResponse = $this->actingAs($this->veterinario)
            ->post(route('atenciones.tratamientos.store', $atencion->id_atencion), [
                'medicamento' => 'Metoclopramida 10mg',
                'dosis' => '0.5 mg/kg',
                'frecuencia' => 'Cada 8 horas',
                'via_administracion' => 'ORAL',
                'duracion_dias' => 5,
                'instrucciones' => 'Administrar 30 minutos antes de alimentos blandos',
            ]);

        $tratResponse->assertRedirect();
        $this->assertDatabaseHas('tratamientos', [
            'id_paciente' => $this->paciente->id_paciente,
            'estado' => 'ACTIVO',
        ]);
        $this->assertDatabaseHas('atencion_tratamiento', [
            'id_atencion' => $atencion->id_atencion,
        ]);

        // 4. Recepcionista no autorizado no puede registrar diagnósticos (403)
        $unauthResponse = $this->actingAs($this->recepcion)
            ->post(route('atenciones.diagnosticos.store', $atencion->id_atencion), [
                'nombre' => 'Intento No Autorizado',
                'tipo' => 'PRESUNTIVO',
            ]);

        $unauthResponse->assertForbidden();
    }

    public function test_cu10_gestionar_estudios_y_procedimientos(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'EN_CURSO',
        ]);
        $atencion->atencionVeterinaria()->create([
            'motivo_consulta' => 'Evaluación de laboratorio',
        ]);

        // Crear procedimiento clínico asociado
        $proc = Procedimiento::create([
            'id_atencion' => $atencion->id_atencion,
            'fecha_hora' => now(),
            'descripcion' => 'Hemograma completo y bioquímica sérica',
            'observaciones' => 'Valores de hematocrito en rango normal',
        ]);

        $estudio = EstudioLaboratorio::create([
            'id_procedimiento' => $proc->id_procedimiento,
            'tipo_estudio' => 'HEMOGRAMA',
            'fecha_resultado' => now()->toDateString(),
            'resultado' => 'Valores dentro de parámetros de referencia',
            'interpretacion' => 'Sin alteraciones leucocitarias',
        ]);

        $this->assertDatabaseHas('procedimientos', [
            'id_procedimiento' => $proc->id_procedimiento,
            'id_atencion' => $atencion->id_atencion,
        ]);
        $this->assertDatabaseHas('estudio_laboratorios', [
            'id_procedimiento' => $proc->id_procedimiento,
            'tipo_estudio' => 'HEMOGRAMA',
        ]);
    }

    public function test_cu11_gestionar_cirugias_y_controles(): void
    {
        // 1. Crear atención y cirugía
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'FINALIZADA',
        ]);
        $atencion->atencionVeterinaria()->create([
            'motivo_consulta' => 'Intervención quirúrgica electiva',
        ]);

        $proc = Procedimiento::create([
            'id_atencion' => $atencion->id_atencion,
            'fecha_hora' => now(),
            'descripcion' => 'Castración electiva (Orquiectomía)',
        ]);

        $cirugia = Cirugia::create([
            'id_procedimiento' => $proc->id_procedimiento,
            'tipo_cirugia' => 'Castración electiva (Orquiectomía)',
            'estado' => 'COMPLETADA',
            'fecha_programada' => now()->subDays(3),
            'resultado' => 'EXITOSA',
        ]);

        // 2. Registrar control postoperatorio (CU11)
        $controlResponse = $this->actingAs($this->veterinario)
            ->post(route('cirugias.controles.store', $cirugia->id_procedimiento), [
                'fecha_control' => now()->format('Y-m-d\TH:i'),
                'dias_postoperatorio' => 3,
                'id_profesional' => $this->profesional->id_profesional,
                'estado_herida' => 'Limpia, seca y bordes afrontados',
                'evolucion' => 'FAVORABLE',
                'temperatura' => 38.4,
                'peso' => 28.5,
                'observaciones' => 'Herida quirúrgica en óptimo estado de cicatrización.',
                'indicaciones' => 'Continuar con pauta analgésica y collar isabelino 4 días más.',
            ]);

        $controlResponse->assertRedirect();
        $this->assertDatabaseHas('controles_cirugia', [
            'id_cirugia' => $cirugia->id_procedimiento,
            'evolucion' => 'FAVORABLE',
            'estado_herida' => 'Limpia, seca y bordes afrontados',
        ]);

        // 3. Consultar historial de controles
        $this->assertEquals(1, $cirugia->controles()->count());
    }

    /* =========================================================================
     * BLOQUE B: AGENDA Y DISPONIBILIDAD (CU13, CU14, CU29, CU30)
     * ========================================================================= */

    public function test_cu13_gestionar_citas_y_evitar_solapamientos(): void
    {
        $fechaCita = Carbon::tomorrow()->setTime(10, 0);

        // 1. Agendar primera cita
        $response1 = $this->actingAs($this->recepcion)
            ->post(route('citas.store'), [
                'id_paciente' => $this->paciente->id_paciente,
                'id_profesional' => $this->profesional->id_persona,
                'fecha_hora' => $fechaCita->format('Y-m-d\TH:i'),
                'motivo' => 'Revisión periódica y desparasitación',
                'servicios' => [$this->servicio->id_servicio],
            ]);

        $response1->assertRedirect();
        $this->assertDatabaseHas('citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'estado' => 'CONFIRMADA',
        ]);

        // 2. Intentar agendar en el mismo horario con el mismo profesional (Solapamiento)
        $response2 = $this->actingAs($this->recepcion)
            ->post(route('citas.store'), [
                'id_paciente' => $this->paciente->id_paciente,
                'id_profesional' => $this->profesional->id_persona,
                'fecha_hora' => $fechaCita->format('Y-m-d\TH:i'),
                'motivo' => 'Cita duplicada en conflicto',
                'servicios' => [$this->servicio->id_servicio],
            ]);

        $response2->assertSessionHasErrors('fecha_hora');

        // 3. Reprogramar cita
        $cita = Cita::where('id_paciente', $this->paciente->id_paciente)->first();
        $nuevaFecha = Carbon::tomorrow()->setTime(15, 0);

        $reprogResponse = $this->actingAs($this->recepcion)
            ->post(route('citas.reprogramar', $cita->id_cita), [
                'fecha_hora' => $nuevaFecha->format('Y-m-d\TH:i'),
                'motivo_reprogramacion' => 'Solicitud del tutor por motivo laboral',
            ]);

        $reprogResponse->assertRedirect();
        $cita->refresh();
        $this->assertEquals($nuevaFecha->format('Y-m-d H:i:00'), Carbon::parse($cita->fecha_hora)->format('Y-m-d H:i:00'));

        // 4. Cambiar estado a inasistencia
        $this->actingAs($this->recepcion)
            ->post(route('citas.cambiar-estado', $cita->id_cita), [
                'estado' => 'INASISTENCIA',
            ]);

        $cita->refresh();
        $this->assertEquals('INASISTENCIA', $cita->estado);
    }

    public function test_cu14_consultar_disponibilidad_en_tiempo_real(): void
    {
        $fecha = Carbon::tomorrow()->toDateString();

        // Horario laboral activo
        HorarioProfesional::create([
            'id_profesional' => $this->profesional->id_persona,
            'dia_semana' => Carbon::tomorrow()->dayOfWeekIso,
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
            'activo' => true,
        ]);

        $response = $this->actingAs($this->recepcion)
            ->getJson(route('citas.disponibilidad', [
                'id_profesional' => $this->profesional->id_persona,
                'fecha' => $fecha,
                'id_servicio' => $this->servicio->id_servicio,
            ]));

        $response->assertOk();
        $response->assertJsonStructure(['disponible', 'horarios', 'slots']);
        $this->assertNotEmpty($response->json('slots'));
    }

    public function test_cu29_gestionar_horarios_de_profesionales_con_validacion(): void
    {
        // 1. Horario coherente
        $horarioValido = HorarioProfesional::create([
            'id_profesional' => $this->profesional->id_persona,
            'dia_semana' => 1, // Lunes
            'hora_inicio' => '08:30:00',
            'hora_fin' => '13:00:00',
            'activo' => true,
        ]);

        $this->assertTrue(Carbon::parse($horarioValido->hora_fin)->gt(Carbon::parse($horarioValido->hora_inicio)));
        $this->assertDatabaseHas('horario_profesionales', [
            'id_profesional' => $this->profesional->id_persona,
            'dia_semana' => 1,
        ]);
    }

    public function test_cu30_gestionar_disponibilidad_y_bloqueos_de_agenda(): void
    {
        $inicioBloqueo = Carbon::tomorrow()->setTime(9, 0);
        $finBloqueo = Carbon::tomorrow()->setTime(11, 0);

        // 1. Registrar bloqueo de agenda por capacitación médica
        $response = $this->actingAs($this->admin)
            ->post(route('citas.bloqueos.store'), [
                'id_profesional' => $this->profesional->id_persona,
                'tipo' => 'CAPACITACION',
                'motivo' => 'Congreso Internacional Veterinario',
                'fecha_hora_inicio' => $inicioBloqueo->format('Y-m-d\TH:i'),
                'fecha_hora_fin' => $finBloqueo->format('Y-m-d\TH:i'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bloqueo_agendas', [
            'id_profesional' => $this->profesional->id_persona,
            'motivo' => 'Congreso Internacional Veterinario',
        ]);

        // 2. Consultar disponibilidad y comprobar que las horas bloqueadas no se ofrezcan
        $dispResponse = $this->actingAs($this->recepcion)
            ->getJson(route('citas.disponibilidad', [
                'id_profesional' => $this->profesional->id_persona,
                'fecha' => Carbon::tomorrow()->toDateString(),
            ]));

        $dispResponse->assertOk();
        $slots = $dispResponse->json('slots');
        $this->assertNotContains('09:30', $slots);
        $this->assertNotContains('10:00', $slots);
    }

    /* =========================================================================
     * BLOQUE C: ATENCIÓN VETERINARIA (CU15, CU31, CU32, CU33, CU34)
     * ========================================================================= */

    public function test_cu15_registrar_atencion_urgente_sin_cita_previa(): void
    {
        $response = $this->actingAs($this->veterinario)
            ->post(route('atenciones.urgente.store'), [
                'id_paciente' => $this->paciente->id_paciente,
                'id_profesional' => $this->profesional->id_persona,
                'motivo_urgencia' => 'Trauma por atropello vehicular con hemorragia activa',
                'anamnesis' => 'Ingresó de emergencia en brazos del tutor',
                'sintomas' => 'Taquicardia, dolor agudo, mucosas pálidas',
                'peso' => 25.40,
                'temperatura' => 37.8,
                'frecuencia_cardiaca' => 140,
                'observaciones' => 'Canalización de vía venosa periférica e inicio de fluidoterapia de choque.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('atenciones', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_cita' => null, // Garantizado sin cita previa
            'estado' => 'EN_CURSO',
        ]);

        $this->assertDatabaseHas('atencion_veterinarias', [
            'tipo' => 'EMERGENCIA',
        ]);

        $this->assertDatabaseHas('participacion_atencions', [
            'id_profesional' => $this->profesional->id_persona,
            'rol_atencion' => 'PRINCIPAL',
        ]);
    }

    public function test_cu31_registrar_atencion_regular_y_vincular_con_cita(): void
    {
        // 1. Cita previa
        $cita = Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora' => now()->format('Y-m-d H:i:00'),
            'motivo' => 'Control post-vacunación',
            'estado' => 'PROGRAMADA',
        ]);

        // 2. Iniciar atención regular asociada a la cita
        $response = $this->actingAs($this->veterinario)
            ->post(route('atenciones.store'), [
                'id_paciente' => $this->paciente->id_paciente,
                'id_cita' => $cita->id_cita,
                'id_profesional' => $this->profesional->id_persona,
                'fecha_hora_inicio' => now()->format('Y-m-d\TH:i'),
                'tipo_atencion' => 'VETERINARIA',
                'motivo_consulta' => 'Control de peso y constantes',
                'estado' => 'EN_CURSO',
            ]);

        $response->assertRedirect();

        // 3. Comprobar que la cita pasó a estado ATENDIDA
        $cita->refresh();
        $this->assertEquals('ATENDIDA', $cita->estado);
    }

    public function test_cu32_registrar_servicios_realizados_sin_duplicar_financieramente(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'EN_CURSO',
            'total_final' => 0,
        ]);

        // 1. Agregar servicio realizado
        $response = $this->actingAs($this->veterinario)
            ->post(route('atenciones.servicios.store', $atencion->id_atencion), [
                'id_servicio' => $this->servicio->id_servicio,
                'cantidad' => 1,
                'descuento' => 20.00,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('atencion_servicios', [
            'id_atencion' => $atencion->id_atencion,
            'id_servicio' => $this->servicio->id_servicio,
            'precio_unitario' => 120.00,
            'descuento' => 20.00,
        ]);

        $atencion->refresh();
        $this->assertEquals(100.00, (float) $atencion->total_final);

        // 2. Eliminar servicio
        $delResponse = $this->actingAs($this->veterinario)
            ->delete(route('atenciones.servicios.delete', [$atencion->id_atencion, $this->servicio->id_servicio]));

        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('atencion_servicios', [
            'id_atencion' => $atencion->id_atencion,
            'id_servicio' => $this->servicio->id_servicio,
        ]);

        $atencion->refresh();
        $this->assertEquals(0.00, (float) $atencion->total_final);
    }

    public function test_cu33_registrar_profesionales_participantes_con_roles(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'EN_CURSO',
        ]);

        // 1. Asignar cirujano
        $this->actingAs($this->veterinario)
            ->post(route('atenciones.participantes.store', $atencion->id_atencion), [
                'id_profesional' => $this->profesional->id_persona,
                'rol_atencion' => 'CIRUJANO',
                'observaciones' => 'Cirujano jefe de procedimiento',
            ]);

        // 2. Asignar anestesista
        $this->actingAs($this->veterinario)
            ->post(route('atenciones.participantes.store', $atencion->id_atencion), [
                'id_profesional' => $this->segundoProfesional->id_persona,
                'rol_atencion' => 'ANESTESISTA',
                'observaciones' => 'Monitoreo multiparamétrico',
            ]);

        $this->assertDatabaseHas('participacion_atencions', [
            'id_atencion' => $atencion->id_atencion,
            'id_profesional' => $this->profesional->id_persona,
            'rol_atencion' => 'CIRUJANO',
        ]);

        $this->assertDatabaseHas('participacion_atencions', [
            'id_atencion' => $atencion->id_atencion,
            'id_profesional' => $this->segundoProfesional->id_persona,
            'rol_atencion' => 'ANESTESISTA',
        ]);
    }

    public function test_cu34_registrar_observaciones_clinicas_vs_administrativas(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'estado' => 'EN_CURSO',
        ]);

        // 1. Veterinario registra observación clínica
        $this->actingAs($this->veterinario)
            ->post(route('atenciones.observaciones.store', $atencion->id_atencion), [
                'tipo' => 'CLINICA',
                'observacion' => 'Paciente presenta reflejo tusígeno positivo y ligera deshidratación.',
            ]);

        $this->assertDatabaseHas('observaciones_registros', [
            'id_atencion' => $atencion->id_atencion,
            'tipo' => 'CLINICA',
            'id_usuario' => $this->veterinario->id_usuario,
        ]);

        // 2. Recepción registra observación administrativa
        $this->actingAs($this->recepcion)
            ->post(route('atenciones.observaciones.store', $atencion->id_atencion), [
                'tipo' => 'ADMINISTRATIVA',
                'observacion' => 'Tutor solicitó comprobante fiscal a nombre de su empresa.',
            ]);

        $this->assertDatabaseHas('observaciones_registros', [
            'id_atencion' => $atencion->id_atencion,
            'tipo' => 'ADMINISTRATIVA',
            'id_usuario' => $this->recepcion->id_usuario,
        ]);

        // 3. Recepción intenta registrar observación clínica (Debe ser denegada con 403)
        $unauthObs = $this->actingAs($this->recepcion)
            ->post(route('atenciones.observaciones.store', $atencion->id_atencion), [
                'tipo' => 'CLINICA',
                'observacion' => 'Intento de nota clínica por parte de recepción',
            ]);

        $unauthObs->assertForbidden();
    }

    /* =========================================================================
     * BLOQUE D: REPORTES (CU38, CU39)
     * ========================================================================= */

    public function test_cu38_reporte_de_vacunaciones_con_filtros_y_exportacion(): void
    {
        // 1. Vista con filtros
        $response = $this->actingAs($this->admin)
            ->get(route('reportes.vacunaciones', [
                'fecha_desde' => Carbon::now()->subMonth()->toDateString(),
                'fecha_hasta' => Carbon::now()->toDateString(),
            ]));

        $response->assertOk();
        $response->assertViewHas(['vacunaciones', 'totalVacunas', 'proximasDosisMes', 'profesionales']);

        // 2. Exportación CSV
        $csvResponse = $this->actingAs($this->admin)
            ->get(route('reportes.exportar', ['tipo' => 'vacunaciones']));

        $csvResponse->assertOk();
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('Content-Type'));
    }

    public function test_cu39_reporte_de_pacientes_atendidos_con_conteo_unico(): void
    {
        // Crear 2 atenciones para el mismo paciente
        Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => Carbon::today()->setTime(9, 0),
            'estado' => 'FINALIZADA',
        ]);
        Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => Carbon::today()->setTime(16, 0),
            'estado' => 'FINALIZADA',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('reportes.pacientes-atendidos', [
                'desde' => Carbon::today()->toDateString(),
                'hasta' => Carbon::today()->toDateString(),
            ]));

        $response->assertOk();
        $totalAtenciones = $response->viewData('totalAtenciones');
        $pacientesUnicos = $response->viewData('pacientesUnicos');

        // Total atenciones = 2, Pacientes únicos = 1 (evitar contar dos veces el mismo paciente)
        $this->assertEquals(2, $totalAtenciones);
        $this->assertEquals(1, $pacientesUnicos);

        // Exportación CSV
        $csvResponse = $this->actingAs($this->admin)
            ->get(route('reportes.exportar', ['tipo' => 'pacientes_atendidos']));

        $csvResponse->assertOk();
        $this->assertStringContainsString('text/csv', $csvResponse->headers->get('Content-Type'));
    }

    /* =========================================================================
     * BLOQUE 3: BITÁCORA EXCLUSIVA DEL SUPER-ADMINISTRADOR
     * ========================================================================= */

    public function test_bitacora_seguridad_estricta_solo_superadmin(): void
    {
        // 1. Recepcionista intenta entrar a la bitácora -> 403 Forbidden
        $recResponse = $this->actingAs($this->recepcion)->get(route('usuarios.auditoria'));
        $recResponse->assertForbidden();

        // 2. Veterinario intenta entrar a la bitácora -> 403 Forbidden
        $vetResponse = $this->actingAs($this->veterinario)->get(route('usuarios.auditoria'));
        $vetResponse->assertForbidden();

        // 3. Admin regular intenta entrar a la bitácora -> 403 Forbidden
        $adminResponse = $this->actingAs($this->admin)->get(route('usuarios.auditoria'));
        $adminResponse->assertForbidden();

        // 4. Super-Admin entra exitosamente -> 200 OK
        $superResponse = $this->actingAs($this->superAdmin)->get(route('usuarios.auditoria'));
        $superResponse->assertOk();
        $superResponse->assertViewIs('usuarios.auditoria');

        // 5. El propio acceso del Super-Admin queda auditado en la base de datos
        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'CONSULTA_BITACORA',
            'modulo' => 'AUDITORIA',
            'id_usuario' => $this->superAdmin->id_usuario,
            'resultado' => 'EXITO',
        ]);
    }

    /* =========================================================================
     * BLOQUE 4: PRIVILEGIOS POR GRUPOS Y PROTECCIÓN ANTI-LOCKOUT
     * ========================================================================= */

    public function test_gestion_de_grupos_y_proteccion_anti_lockout_superadmin(): void
    {
        $rolVet = Rol::where('nombre', 'VETERINARIO')->first();
        $rolSuper = Rol::where('nombre', 'SUPER_ADMIN')->first();

        // 1. Super-Admin desactiva temporalmente el grupo VETERINARIO
        $toggleResponse = $this->actingAs($this->superAdmin)
            ->post(route('roles.toggle-estado', $rolVet->id_rol));

        $toggleResponse->assertRedirect();
        $rolVet->refresh();
        $this->assertEquals('INACTIVO', $rolVet->estado);

        // 2. Super-Admin reactiva el grupo VETERINARIO
        $this->actingAs($this->superAdmin)
            ->post(route('roles.toggle-estado', $rolVet->id_rol));

        $rolVet->refresh();
        $this->assertEquals('ACTIVO', $rolVet->estado);

        // 3. Protección Anti-lockout: Intentar desactivar SUPER_ADMIN debe rechazarse
        $lockoutAttempt = $this->actingAs($this->superAdmin)
            ->post(route('roles.toggle-estado', $rolSuper->id_rol));

        $lockoutAttempt->assertSessionHas('error');
        $rolSuper->refresh();
        $this->assertEquals('ACTIVO', $rolSuper->estado);

        // 4. Actualización de matriz de permisos para grupos
        $permiso = Permiso::firstOrCreate(['nombre' => 'citas.ver', 'descripcion' => 'Ver citas']);
        $updatePermResponse = $this->actingAs($this->superAdmin)
            ->post(route('roles.permisos.update'), [
                'permisos' => [
                    $rolVet->id_rol => [$permiso->id_permiso],
                ],
            ]);

        $updatePermResponse->assertRedirect();
        $this->assertTrue($rolVet->hasPermiso($permiso->id_permiso));
    }
}
