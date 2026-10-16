<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleCompra>
 */
class DetalleCompraFactory extends Factory
{
    protected $model = DetalleCompra::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_compra' => Compra::factory(),
            'id_producto' => Producto::factory(),
            'cantidad' => fake()->numberBetween(1, 100),
            'precio_unitario' => fake()->randomFloat(2, 5, 100),
        ];
    }
}
