<?php

namespace Tests\Feature;

use App\Models\BloqueoAgenda;
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

class CitaWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $recepcionUser;

    private Usuario $vetUser;

    private Usuario $transporteUser;

    private Profesional $profesional;

    private Paciente $paciente;

    private Servicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        // Roles
        $rolRecepcion = Rol::create(['nombre' => 'RECEPCION']);
        $rolVet = Rol::create(['nombre' => 'VETERINARIO']);
        $rolTransporte = Rol::create(['nombre' => 'TRANSPORTE']);

        // Persona & Usuario Recepción
        $pRecepcion = Persona::create([
            'documento' => '11112222',
            'nombre' => 'Marta',
            'apellido1' => 'Lopez',
            'email' => 'marta@lapetdiatria.com',
        ]);
        $this->recepcionUser = Usuario::factory()->create([
            'id_persona' => $pRecepcion->id_persona,
            'nombre_usuario' => 'mlopez_test',
            'estado' => 'ACTIVO',
        ]);
        $this->recepcionUser->roles()->attach($rolRecepcion->id_rol);

        // Persona, Profesional & Usuario Veterinario
        $pVet = Persona::create([
            'documento' => '33334444',
            'nombre' => 'Carlos',
            'apellido1' => 'Mendoza',
            'email' => 'carlos@lapetdiatria.com',
        ]);
        $this->profesional = Profesional::create([
            'id_persona' => $pVet->id_persona,
            'especialidad' => 'Cirugía Veterinaria',
            'registro_profesional' => 'VET-999',
            'estado' => 'ACTIVO',
        ]);
        $this->vetUser = Usuario::factory()->create([
            'id_persona' => $pVet->id_persona,
            'nombre_usuario' => 'cmendoza_test',
            'estado' => 'ACTIVO',
        ]);
        $this->vetUser->roles()->attach($rolVet->id_rol);

        // Persona & Usuario Transporte
        $pTrans = Persona::create([
            'documento' => '55556666',
            'nombre' => 'Hugo',
            'apellido1' => 'Vargas',
            'email' => 'hugo@lapetdiatria.com',
        ]);
        $this->transporteUser = Usuario::factory()->create([
            'id_persona' => $pTrans->id_persona,
            'nombre_usuario' => 'hvargas_test',
            'estado' => 'ACTIVO',
        ]);
        $this->transporteUser->roles()->attach($rolTransporte->id_rol);

        // Propietario y Paciente
        $pCliente = Persona::create([
            'documento' => '77778888',
            'nombre' => 'Andrea',
            'apellido1' => 'Suarez',
            'email' => 'andrea@gmail.com',
        ]);
        $propietario = Propietario::create(['id_persona' => $pCliente->id_persona]);

        $especie = Especie::create(['nombre' => 'Canino']);

        $raza = Raza::create([
            'id_especie' => $especie->id_especie,
            'nombre' => 'Golden Retriever',
        ]);

        $this->paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Max',
            'sexo' => 'MACHO',
            'estado' => 'ACTIVO',
        ]);

        // Servicio
        $this->servicio = Servicio::create([
            'nombre' => 'Consulta General Canina',
            'categoria' => 'VETERINARIA',
            'precio_base' => 120.00,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_citas(): void
    {
        $response = $this->get('/citas');
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_role_cannot_access_citas(): void
    {
        $response = $this->actingAs($this->transporteUser)->get('/citas');
        $response->assertStatus(403);
    }

    public function test_recepcion_can_view_citas_index(): void
    {
        $response = $this->actingAs($this->recepcionUser)->get('/citas');
        $response->assertStatus(200);
        $response->assertSee('Agenda y Citas');
        $response->assertSee('Nueva Cita');
        $response->assertSee('Atención Urgente');
    }

    public function test_veterinario_can_view_citas_in_readonly_mode(): void
    {
        $response = $this->actingAs($this->vetUser)->get('/citas');
        $response->assertStatus(200);
        $response->assertSee('Agenda y Citas');
        $response->assertDontSee('Nueva Cita');
        $response->assertDontSee('Atención Urgente');
    }

    public function test_user_can_schedule_new_cita_with_services(): void
    {
        $fecha = Carbon::tomorrow()->format('Y-m-d');

        $response = $this->actingAs($this->recepcionUser)->post('/citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha' => $fecha,
            'hora' => '10:00',
            'duracion_minutos' => 30,
            'id_servicio' => [$this->servicio->id_servicio],
            'origen' => 'PRESENCIAL',
        ]);

        $response->assertRedirect('/citas?fecha='.$fecha);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'estado' => 'CONFIRMADA',
        ]);

        $cita = Cita::where('id_paciente', $this->paciente->id_paciente)->latest('id_cita')->first();
        $this->assertDatabaseHas('detalle_citas', [
            'id_cita' => $cita->id_cita,
            'id_servicio' => $this->servicio->id_servicio,
        ]);
    }

    public function test_cannot_schedule_conflicting_cita_for_same_professional(): void
    {
        $fecha = Carbon::tomorrow()->format('Y-m-d');

        // Primera cita: 10:00 a 10:45
        Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::parse("{$fecha} 10:00:00"),
            'fecha_hora_fin' => Carbon::parse("{$fecha} 10:45:00"),
            'estado' => 'CONFIRMADA',
        ]);

        // Intento de solapamiento: 10:30 a 11:00
        $response = $this->actingAs($this->recepcionUser)->post('/citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha' => $fecha,
            'hora' => '10:30',
            'duracion_minutos' => 30,
            'id_servicio' => [$this->servicio->id_servicio],
        ]);

        $response->assertSessionHasErrors(['hora']);
    }

    public function test_cannot_schedule_during_bloqueo_agenda(): void
    {
        $fecha = Carbon::tomorrow()->format('Y-m-d');

        BloqueoAgenda::create([
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::parse("{$fecha} 08:00:00"),
            'fecha_hora_fin' => Carbon::parse("{$fecha} 12:00:00"),
            'motivo' => 'Capacitación Quirúrgica',
        ]);

        $response = $this->actingAs($this->recepcionUser)->post('/citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha' => $fecha,
            'hora' => '09:30',
            'duracion_minutos' => 30,
            'id_servicio' => [$this->servicio->id_servicio],
        ]);

        $response->assertSessionHasErrors(['hora']);
    }

    public function test_user_can_schedule_urgent_attention(): void
    {
        $response = $this->actingAs($this->recepcionUser)->post('/citas/urgente', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'id_servicio' => $this->servicio->id_servicio,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'estado' => 'EN_ATENCION',
            'origen' => 'URGENCIA',
        ]);
    }

    public function test_user_can_reschedule_cita(): void
    {
        $cita = Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::tomorrow()->setHour(9)->setMinute(0),
            'fecha_hora_fin' => Carbon::tomorrow()->setHour(9)->setMinute(30),
            'estado' => 'CONFIRMADA',
        ]);

        $nuevaFecha = Carbon::tomorrow()->addDay()->format('Y-m-d');

        $response = $this->actingAs($this->recepcionUser)->put('/citas/'.$cita->id_cita.'/reprogramar', [
            'id_profesional' => $this->profesional->id_persona,
            'fecha' => $nuevaFecha,
            'hora' => '14:00',
            'duracion_minutos' => 45,
        ]);

        $response->assertRedirect('/citas?fecha='.$nuevaFecha);
        $response->assertSessionHas('success');

        $cita->refresh();
        $this->assertEquals("{$nuevaFecha} 14:00:00", $cita->fecha_hora_inicio->format('Y-m-d H:i:s'));
    }

    public function test_user_can_cancel_cita_with_reason(): void
    {
        $cita = Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::tomorrow()->setHour(11)->setMinute(0),
            'fecha_hora_fin' => Carbon::tomorrow()->setHour(11)->setMinute(30),
            'estado' => 'CONFIRMADA',
        ]);

        $response = $this->actingAs($this->recepcionUser)->post('/citas/'.$cita->id_cita.'/cancelar', [
            'motivo_cancelacion' => 'Dueño no puede asistir por viaje',
        ]);

        $response->assertSessionHas('success');

        $cita->refresh();
        $this->assertEquals('CANCELADA', $cita->estado);
        $this->assertEquals('Dueño no puede asistir por viaje', $cita->motivo_cancelacion);
    }

    public function test_user_can_change_cita_status(): void
    {
        $cita = Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'id_profesional' => $this->profesional->id_persona,
            'fecha_hora_inicio' => Carbon::now(),
            'fecha_hora_fin' => Carbon::now()->addMinutes(30),
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->actingAs($this->recepcionUser)->post('/citas/'.$cita->id_cita.'/cambiar-estado', [
            'estado' => 'CONFIRMADA',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('CONFIRMADA', $cita->refresh()->estado);

        // Cambiar a EN_ATENCION
        $this->actingAs($this->recepcionUser)->post('/citas/'.$cita->id_cita.'/cambiar-estado', [
            'estado' => 'EN_ATENCION',
        ]);
        $this->assertEquals('EN_ATENCION', $cita->refresh()->estado);
    }

    public function test_user_can_create_and_delete_bloqueo_agenda(): void
    {
        $response = $this->actingAs($this->recepcionUser)->post('/citas/bloqueos', [
            'id_profesional' => $this->profesional->id_persona,
            'fecha_inicio' => Carbon::tomorrow()->format('Y-m-d'),
            'hora_inicio' => '13:00',
            'fecha_fin' => Carbon::tomorrow()->format('Y-m-d'),
            'hora_fin' => '16:00',
            'motivo' => 'Reunión médica institucional',
        ]);

        $response->assertSessionHas('success');

        $bloqueo = BloqueoAgenda::where('id_profesional', $this->profesional->id_persona)->firstOrFail();
        $this->assertEquals('Reunión médica institucional', $bloqueo->motivo);

        // Eliminar
        $delResponse = $this->actingAs($this->recepcionUser)->delete('/citas/bloqueos/'.$bloqueo->id_bloqueo);
        $delResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('bloqueo_agendas', ['id_bloqueo' => $bloqueo->id_bloqueo]);
    }
}
