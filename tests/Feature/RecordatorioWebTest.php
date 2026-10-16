<?php

namespace Tests\Feature;

use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Recordatorio;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecordatorioWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $recepcionUser;

    protected Usuario $unauthorizedUser;

    protected Paciente $paciente;

    protected Servicio $servicioVacuna;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolRecep = Rol::firstOrCreate(['nombre' => 'RECEPCION'], ['descripcion' => 'Recepción']);
        $rolTransp = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Transporte']);

        // 2. Personas y Usuarios
        $pRecep = Persona::create([
            'nombre' => 'Lucía',
            'apellido1' => 'Recepción',
            'documento' => '223344',
            'telefono' => '71223344',
            'estado' => 'ACTIVO',
        ]);

        $this->recepcionUser = Usuario::create([
            'id_persona' => $pRecep->id_persona,
            'nombre_usuario' => 'recep_rec',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->recepcionUser->roles()->sync([$rolRecep->id_rol]);

        $pTransp = Persona::create([
            'nombre' => 'Conductor',
            'apellido1' => 'Móvil',
            'documento' => '556677',
            'telefono' => '78899002',
            'estado' => 'ACTIVO',
        ]);

        $this->unauthorizedUser = Usuario::create([
            'id_persona' => $pTransp->id_persona,
            'nombre_usuario' => 'transp_rec',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->unauthorizedUser->roles()->sync([$rolTransp->id_rol]);

        // 3. Cliente y Mascota
        $pCliente = Persona::create([
            'nombre' => 'Carlos',
            'apellido1' => 'Santander',
            'documento' => '990011',
            'telefono' => '76543210',
            'estado' => 'ACTIVO',
        ]);

        $propietario = Propietario::create(['id_persona' => $pCliente->id_persona]);
        $especie = Especie::firstOrCreate(['nombre' => 'Felino']);
        $raza = Raza::firstOrCreate(['nombre' => 'Siamés', 'id_especie' => $especie->id_especie]);

        $this->paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Mishi',
            'sexo' => 'HEMBRA',
            'fecha_nacimiento' => '2023-04-10',
            'peso_actual' => 4.2,
            'estado' => 'VIVO',
        ]);

        $this->servicioVacuna = Servicio::create([
            'nombre' => 'Vacuna Triple Felina',
            'categoria' => 'VETERINARIA',
            'precio_base' => 95.00,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_ver_recordatorios(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('recordatorios.index'));
        $response->assertStatus(403);
    }

    public function test_recepcion_puede_ver_listado_de_recordatorios(): void
    {
        Recordatorio::create([
            'id_paciente' => $this->paciente->id_paciente,
            'tipo' => 'VACUNA',
            'id_servicio' => $this->servicioVacuna->id_servicio,
            'fecha_hora_programada' => now()->addDays(2),
            'medio_envio' => 'WHATSAPP',
            'mensaje' => 'Recordatorio de vacuna para Mishi',
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->actingAs($this->recepcionUser)->get(route('recordatorios.index'));
        $response->assertStatus(200);
        $response->assertSee('Recordatorios y Alertas Preventivas');
        $response->assertSee('Mishi');
        $response->assertSee('Vacuna Triple Felina');
    }

    public function test_recepcion_puede_cargar_formulario_crear_recordatorio(): void
    {
        $response = $this->actingAs($this->recepcionUser)->get(route('recordatorios.create'));
        $response->assertStatus(200);
        $response->assertSee('Programar Recordatorio Preventivo');
        $response->assertSee('Mishi');
    }

    public function test_recepcion_puede_programar_recordatorio(): void
    {
        $data = [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo' => 'DESPARASITACION',
            'id_servicio' => null,
            'fecha_programada' => now()->addDays(5)->toDateString(),
            'hora_programada' => '10:30',
            'medio_envio' => 'WHATSAPP',
            'mensaje' => 'Estimado cliente, Mishi requiere su pastilla desparasitante periódica.',
        ];

        $response = $this->actingAs($this->recepcionUser)->post(route('recordatorios.store'), $data);
        $response->assertRedirect(route('recordatorios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recordatorios', [
            'id_paciente' => $this->paciente->id_paciente,
            'tipo' => 'DESPARASITACION',
            'medio_envio' => 'WHATSAPP',
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_recepcion_puede_marcar_como_enviado(): void
    {
        $rec = Recordatorio::create([
            'id_paciente' => $this->paciente->id_paciente,
            'tipo' => 'CONTROL',
            'fecha_hora_programada' => now(),
            'medio_envio' => 'WHATSAPP',
            'mensaje' => 'Control de peso de Mishi',
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->actingAs($this->recepcionUser)->post(route('recordatorios.marcar-enviado', $rec));
        $response->assertRedirect(route('recordatorios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recordatorios', [
            'id_recordatorio' => $rec->id_recordatorio,
            'estado' => 'ENVIADO',
        ]);
    }

    public function test_recepcion_puede_cancelar_recordatorio(): void
    {
        $rec = Recordatorio::create([
            'id_paciente' => $this->paciente->id_paciente,
            'tipo' => 'VACUNA',
            'fecha_hora_programada' => now()->addDays(10),
            'medio_envio' => 'EMAIL',
            'mensaje' => 'Aviso descartable',
            'estado' => 'PENDIENTE',
        ]);

        $response = $this->actingAs($this->recepcionUser)->post(route('recordatorios.cancelar', $rec));
        $response->assertRedirect(route('recordatorios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('recordatorios', [
            'id_recordatorio' => $rec->id_recordatorio,
            'estado' => 'CANCELADO',
        ]);
    }
}
