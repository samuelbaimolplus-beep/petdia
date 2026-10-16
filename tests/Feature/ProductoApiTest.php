<?php

namespace Tests\Feature;

use App\Models\Medicamento;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_productos_with_medicamento_when_present(): void
    {
        Producto::factory()->count(2)->create();
        Medicamento::factory()->create();

        $this->getJson('/api/v1/productos')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'nombre',
                    'tipo',
                    'precio_venta',
                    'estado',
                    'medicamento',
                ]],
            ]);
    }

    public function test_show_returns_producto_with_medicamento_details(): void
    {
        $medicamento = Medicamento::factory()->create();

        $this->getJson('/api/v1/productos/'.$medicamento->id_producto)
            ->assertOk()
            ->assertJsonPath('data.nombre', $medicamento->producto->nombre)
            ->assertJsonStructure([
                'data' => [
                    'medicamento' => ['principio_activo', 'presentacion', 'concentracion'],
                    'lotes',
                ],
            ]);
    }

    public function test_valid_payload_creates_producto_and_returns_201(): void
    {
        $this->postJson('/api/v1/productos', [
            'nombre' => 'Shampoo antipulgas',
            'tipo' => 'INSUMO',
            'categoria' => 'ESTETICA',
            'precio_venta' => 45.0,
        ])->assertCreated()
            ->assertJsonPath('data.nombre', 'Shampoo antipulgas');

        $this->assertDatabaseHas('productos', ['nombre' => 'Shampoo antipulgas']);
    }

    public function test_store_rejects_missing_required_fields_with_422(): void
    {
        $this->postJson('/api/v1/productos', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['nombre', 'tipo', 'categoria', 'precio_venta']);
    }
}
