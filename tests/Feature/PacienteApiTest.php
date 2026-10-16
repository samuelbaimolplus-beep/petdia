<?php

namespace Tests\Feature;

use App\Models\Paciente;
use App\Models\Propietario;
use App\Models\Raza;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PacienteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_pacientes_with_relationships(): void
    {
        Paciente::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/pacientes');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'nombre',
                    'raza',
                    'especie',
                    'propietario' => ['id', 'nombre'],
                ]],
            ]);
    }

    public function test_show_returns_paciente_with_medical_history(): void
    {
        $paciente = Paciente::factory()->create();

        $this->getJson('/api/v1/pacientes/'.$paciente->id_paciente)
            ->assertOk()
            ->assertJsonPath('data.id', $paciente->id_paciente)
            ->assertJsonPath('data.raza', $paciente->raza->nombre)
            ->assertJsonStructure(['data' => ['ficha_medica', 'citas', 'atenciones']]);
    }

    public function test_show_returns_404_for_missing_paciente(): void
    {
        $this->getJson('/api/v1/pacientes/999999')->assertNotFound();
    }

    public function test_valid_payload_creates_paciente_and_returns_201(): void
    {
        $raza = Raza::factory()->create();
        $propietario = Propietario::factory()->create();

        $this->postJson('/api/v1/pacientes', [
            'id_raza' => $raza->id_raza,
            'id_propietario' => $propietario->id_persona,
            'nombre' => 'Rocky',
            'sexo' => 'MACHO',
        ])->assertCreated()
            ->assertJsonPath('data.nombre', 'Rocky');

        $this->assertDatabaseHas('pacientes', ['nombre' => 'Rocky', 'sexo' => 'MACHO']);
    }

    public function test_store_rejects_missing_required_fields_with_422(): void
    {
        $this->postJson('/api/v1/pacientes', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['id_raza', 'id_propietario', 'nombre', 'sexo']);
    }

    public function test_valid_payload_updates_paciente_and_returns_200(): void
    {
        $paciente = Paciente::factory()->create();

        $this->patchJson('/api/v1/pacientes/'.$paciente->id_paciente, ['nombre' => 'Nuevo Nombre'])
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Nuevo Nombre');

        $this->assertDatabaseHas('pacientes', ['id_paciente' => $paciente->id_paciente, 'nombre' => 'Nuevo Nombre']);
    }

    public function test_destroy_deletes_paciente_and_returns_200(): void
    {
        $paciente = Paciente::factory()->create();

        $this->deleteJson('/api/v1/pacientes/'.$paciente->id_paciente)
            ->assertOk()
            ->assertJsonPath('message', 'Paciente eliminado');

        $this->assertDatabaseMissing('pacientes', ['id_paciente' => $paciente->id_paciente]);
    }
}
