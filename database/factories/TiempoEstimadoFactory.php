<?php

namespace Database\Factories;

use App\Models\Especie;
use App\Models\Servicio;
use App\Models\TiempoEstimado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TiempoEstimado>
 */
class TiempoEstimadoFactory extends Factory
{
    protected $model = TiempoEstimado::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_servicio' => Servicio::factory(),
            'id_especie' => Especie::factory(),
            'peso_min' => fake()->randomFloat(2, 0, 10),
            'peso_max' => fake()->randomFloat(2, 10, 100),
            'duracion_minutos' => fake()->numberBetween(10, 120),
        ];
    }
}
