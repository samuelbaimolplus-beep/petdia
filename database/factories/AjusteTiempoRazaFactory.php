<?php

namespace Database\Factories;

use App\Models\AjusteTiempoRaza;
use App\Models\Raza;
use App\Models\TiempoEstimado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AjusteTiempoRaza>
 */
class AjusteTiempoRazaFactory extends Factory
{
    protected $model = AjusteTiempoRaza::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_tiempo_estimado' => TiempoEstimado::factory(),
            'id_raza' => Raza::factory(),
            'duracion_minutos' => fake()->optional()->numberBetween(5, 60),
        ];
    }
}
