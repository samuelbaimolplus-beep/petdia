<?php

namespace Database\Factories;

use App\Models\Paquete;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paquete>
 */
class PaqueteFactory extends Factory
{
    protected $model = Paquete::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'Paquete '.fake()->unique()->numberBetween(1000, 9999),
            'descripcion' => fake()->optional()->sentence(),
            'precio' => fake()->randomFloat(2, 50, 500),
            'estado' => 'ACTIVO',
        ];
    }
}
