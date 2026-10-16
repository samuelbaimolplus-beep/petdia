<?php

namespace Database\Factories;

use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MovimientoInventario>
 */
class MovimientoInventarioFactory extends Factory
{
    protected $model = MovimientoInventario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_producto' => Producto::factory(),
            'id_lote' => Lote::factory(),
            'id_usuario' => Usuario::factory(),
            'id_detalle_compra' => DetalleCompra::factory(),
            'id_uso' => null,
            'id_detalle_venta' => null,
            'fecha_hora' => fake()->dateTimeBetween('-3 months', 'now'),
            'tipo_movimiento' => fake()->randomElement(['ENTRADA', 'SALIDA', 'AJUSTE']),
            'cantidad' => fake()->randomFloat(2, 1, 50),
            'motivo' => fake()->optional()->text(45),
        ];
    }
}
