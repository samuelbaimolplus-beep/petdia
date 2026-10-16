<?php

namespace Tests\Feature;

use App\Models\Servicio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicioApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_servicios_with_tiempos_estimados(): void
    {
        Servicio::factory()->count(3)->create();

        $this->getJson('/api/v1/servicios')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'nombre',
                    'categoria',
                    'precio_base',
                    'estado',
                    'tiempos_estimados',
                ]],
            ]);
    }

    public function test_valid_payload_creates_servicio_and_returns_201(): void
    {
        $this->postJson('/api/v1/servicios', [
            'nombre' => 'Consulta dermatológica',
            'categoria' => 'VETERINARIA',
            'precio_base' => 150.0,
            'disponible_domicilio' => false,
        ])->assertCreated()
            ->assertJsonPath('data.nombre', 'Consulta dermatológica');

        $this->assertDatabaseHas('servicios', ['nombre' => 'Consulta dermatológica']);
    }

    public function test_store_rejects_missing_required_fields_with_422(): void
    {
        $this->postJson('/api/v1/servicios', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'categoria', 'precio_base']);
    }

    public function test_store_rejects_duplicate_nombre_with_422(): void
    {
        $servicio = Servicio::factory()->create();

        $this->postJson('/api/v1/servicios', [
            'nombre' => $servicio->nombre,
            'categoria' => 'VETERINARIA',
            'precio_base' => 100,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }
}
