<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Cita;
use App\Models\DetalleCita;
use App\Models\Paciente;
use App\Models\Profesional;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_citas_with_paciente_and_servicios(): void
    {
        $cita = Cita::factory()->create();
        DetalleCita::factory()->create(['id_cita' => $cita->id_cita]);

        $this->getJson('/api/v1/citas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'estado',
                    'paciente' => ['id', 'nombre'],
                    'profesional' => ['id', 'nombre'],
                    'servicios' => [['servicio', 'costo_estimado']],
                ]],
            ]);
    }

    public function test_show_returns_cita_with_atenciones(): void
    {
        $cita = Cita::factory()->create();
        Atencion::factory()->create(['id_cita' => $cita->id_cita]);

        $this->getJson('/api/v1/citas/'.$cita->id_cita)
            ->assertOk()
            ->assertJsonStructure(['data' => ['atenciones', 'servicio_domicilio']]);
    }

    public function test_valid_payload_creates_cita_and_returns_201(): void
    {
        $paciente = Paciente::factory()->create();
        $profesional = Profesional::factory()->create();

        $this->postJson('/api/v1/citas', [
            'id_paciente' => $paciente->id_paciente,
            'id_profesional' => $profesional->id_persona,
            'fecha_hora_inicio' => '2026-10-01 10:00:00',
            'fecha_hora_fin' => '2026-10-01 11:00:00',
        ])->assertCreated()
            ->assertJsonPath('data.paciente.nombre', $paciente->nombre);

        $this->assertDatabaseHas('citas', ['id_paciente' => $paciente->id_paciente]);
    }

    public function test_store_rejects_end_before_start_with_422(): void
    {
        $paciente = Paciente::factory()->create();
        $profesional = Profesional::factory()->create();

        $this->postJson('/api/v1/citas', [
            'id_paciente' => $paciente->id_paciente,
            'id_profesional' => $profesional->id_persona,
            'fecha_hora_inicio' => '2026-10-01 11:00:00',
            'fecha_hora_fin' => '2026-10-01 10:00:00',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('fecha_hora_fin');
    }

    public function test_store_rejects_missing_required_fields_with_422(): void
    {
        $this->postJson('/api/v1/citas', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['id_paciente', 'id_profesional', 'fecha_hora_inicio', 'fecha_hora_fin']);
    }

    public function test_destroy_deletes_cita_and_returns_200(): void
    {
        $cita = Cita::factory()->create();

        $this->deleteJson('/api/v1/citas/'.$cita->id_cita)
            ->assertOk()
            ->assertJsonPath('message', 'Cita eliminada');

        $this->assertDatabaseMissing('citas', ['id_cita' => $cita->id_cita]);
    }
}
