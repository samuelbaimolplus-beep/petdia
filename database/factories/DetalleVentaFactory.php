<?php

namespace Database\Factories;

use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleVenta>
 */
class DetalleVentaFactory extends Factory
{
    protected $model = DetalleVenta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_venta' => Venta::factory(),
            'id_producto' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 10),
            'precio_unitario' => fake()->randomFloat(2, 5, 200),
            'descuento' => fake()->randomFloat(2, 0, 50),
        ];
    }
}
