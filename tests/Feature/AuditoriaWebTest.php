<?php

namespace Tests\Feature;

use App\Models\AuditoriaLog;
use App\Models\Cita;
use App\Models\Cuenta;
use App\Models\DetallePago;
use App\Models\Especie;
use App\Models\MetodoPago;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Profesional;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $superAdmin;

    private Usuario $veterinario;

    private Usuario $recepcion;

    private Rol $rolSuperAdmin;

    private Rol $rolVet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->rolSuperAdmin = Rol::create(['nombre' => 'SUPER_ADMIN', 'descripcion' => 'Super Administrador']);
        $this->rolVet = Rol::create(['nombre' => 'VETERINARIO', 'descripcion' => 'Veterinario']);
        $rolRec = Rol::create(['nombre' => 'RECEPCION', 'descripcion' => 'Recepción']);

        $personaSA = Persona::create([
            'nombre' => 'Super',
            'apellido1' => 'Admin',
            'documento' => 'SA-001',
            'email' => 'sa@lapetdiatria.com',
        ]);
        $this->superAdmin = Usuario::factory()->create([
            'id_persona' => $personaSA->id_persona,
            'nombre_usuario' => 'root_admin',
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->attach($this->rolSuperAdmin->id_rol);

        $personaVet = Persona::create([
            'nombre' => 'Dra. María',
            'apellido1' => 'López',
            'documento' => 'VET-002',
            'email' => 'maria@lapetdiatria.com',
        ]);
        $this->veterinario = Usuario::factory()->create([
            'id_persona' => $personaVet->id_persona,
            'nombre_usuario' => 'dra_maria',
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->attach($this->rolVet->id_rol);

        $personaRec = Persona::create([
            'nombre' => 'Carla',
            'apellido1' => 'Recepción',
            'documento' => 'REC-003',
        ]);
        $this->recepcion = Usuario::factory()->create([
            'id_persona' => $personaRec->id_persona,
            'nombre_usuario' => 'carla_rec',
            'estado' => 'ACTIVO',
        ]);
        $this->recepcion->roles()->attach($rolRec->id_rol);
    }

    public function test_unauthenticated_user_cannot_access_auditoria(): void
    {
        $response = $this->get('/usuarios/auditoria');
        $response->assertRedirect('/login');
    }

    public function test_non_super_admin_cannot_access_auditoria(): void
    {
        $response = $this->actingAs($this->veterinario)->get('/usuarios/auditoria');
        $response->assertStatus(403);
    }

    public function test_super_admin_can_view_auditoria_dashboard(): void
    {
        AuditService::log('LOGIN', 'USUARIOS', ['ip' => '127.0.0.1'], null, $this->superAdmin->id_usuario);

        $response = $this->actingAs($this->superAdmin)->get('/usuarios/auditoria');
        $response->assertStatus(200);
        $response->assertSee('Bitácora de Auditoría y Trazabilidad');
        $response->assertSee('LOGIN');
        $response->assertSee('USUARIOS');
    }

    public function test_audit_service_records_voided_payment_event(): void
    {
        $personaProp = Persona::create([
            'nombre' => 'Javier',
            'apellido1' => 'Molina',
            'documento' => '55443322',
        ]);
        $propietario = Propietario::create(['id_persona' => $personaProp->id_persona]);
        $cuenta = Cuenta::create([
            'id_propietario' => $propietario->id_persona,
            'fecha_apertura' => now()->toDateString(),
            'estado' => 'ACTIVA',
        ]);
        $pago = Pago::create([
            'id_cuenta' => $cuenta->id_cuenta,
            'fecha_hora' => now(),
            'estado' => 'COMPLETADO',
        ]);
        $metodo = MetodoPago::create(['nombre' => 'Efectivo', 'tipo' => 'EFECTIVO']);
        DetallePago::create([
            'id_pago' => $pago->id_pago,
            'id_metodo_pago' => $metodo->id_metodo_pago,
            'monto' => 150.00,
        ]);

        $response = $this->actingAs($this->superAdmin)->post("/caja/pagos/{$pago->id_pago}/anular", [
            'motivo' => 'Error de digitación en el importe cobrado',
        ]);

        $response->assertRedirect('/caja');

        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'ANULAR_COBRO',
            'modulo' => 'CAJA',
            'entidad_id' => (string) $pago->id_pago,
        ]);

        $log = AuditoriaLog::where('accion', 'ANULAR_COBRO')->first();
        $this->assertNotNull($log);
        $this->assertEquals(150.00, $log->detalles['monto']);
        $this->assertEquals('Error de digitación en el importe cobrado', $log->detalles['motivo']);
    }

    public function test_audit_service_records_cancelled_appointment_event(): void
    {
        $personaProp = Persona::create(['nombre' => 'Tutor', 'apellido1' => 'Test', 'documento' => 'DOC-123']);
        $propietario = Propietario::create(['id_persona' => $personaProp->id_persona]);
        $especie = Especie::create(['nombre' => 'Felino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Siamés']);
        $paciente = Paciente::create([
            'id_raza' => $raza->id_raza,
            'id_propietario' => $propietario->id_persona,
            'nombre' => 'Michi',
            'sexo' => 'HEMBRA',
            'fecha_nacimiento' => '2023-01-01',
            'estado' => 'ACTIVO',
        ]);
        $prof = Profesional::create(['id_persona' => $personaProp->id_persona]);

        $cita = Cita::create([
            'id_paciente' => $paciente->id_paciente,
            'id_profesional' => $prof->id_persona,
            'fecha_hora_inicio' => now()->addDays(2),
            'fecha_hora_fin' => now()->addDays(2)->addMinutes(30),
            'estado' => 'PENDIENTE',
            'tipo_cita' => 'CONSULTA',
        ]);

        $response = $this->actingAs($this->superAdmin)->post("/citas/{$cita->id_cita}/cancelar", [
            'motivo_cancelacion' => 'El cliente reprogramó por viaje familiar',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'CANCELAR_CITA',
            'modulo' => 'CITAS',
            'entidad_id' => (string) $cita->id_cita,
        ]);
    }

    public function test_audit_service_records_user_update_event(): void
    {
        $personaTarget = Persona::create([
            'nombre' => 'Roberto',
            'apellido1' => 'Pérez',
            'documento' => 'CI-889900',
            'email' => 'roberto@lapetdiatria.com',
        ]);
        $targetUser = Usuario::factory()->create([
            'id_persona' => $personaTarget->id_persona,
            'nombre_usuario' => 'roberto_p',
            'estado' => 'ACTIVO',
        ]);
        $targetUser->roles()->attach($this->rolVet->id_rol);

        $response = $this->actingAs($this->superAdmin)->put("/usuarios/{$targetUser->id_usuario}", [
            'nombre' => 'Roberto Carlos',
            'apellido1' => 'Pérez',
            'documento' => 'CI-889900',
            'email' => 'roberto@lapetdiatria.com',
            'nombre_usuario' => 'roberto_p',
            'id_rol' => $this->rolSuperAdmin->id_rol,
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect('/usuarios');

        $this->assertDatabaseHas('auditoria_logs', [
            'accion' => 'MODIFICAR_USUARIO',
            'modulo' => 'USUARIOS',
            'entidad_id' => (string) $targetUser->id_usuario,
        ]);
    }
}
