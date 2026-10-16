<?php

namespace Database\Factories;

use App\Models\Compra;
use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Compra>
 */
class CompraFactory extends Factory
{
    protected $model = Compra::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_proveedor' => Proveedor::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-3 months', 'now'),
            'estado' => fake()->randomElement(['RECIBIDA', 'PENDIENTE', 'CANCELADA']),
            'total' => fake()->randomFloat(2, 100, 5000),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
