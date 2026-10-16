<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\DetalleCita;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleCita>
 */
class DetalleCitaFactory extends Factory
{
    protected $model = DetalleCita::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cita' => Cita::factory(),
            'id_servicio' => Servicio::factory(),
            'costo_estimado' => fake()->randomFloat(2, 20, 300),
            'tiempo_estimado_minutos' => fake()->numberBetween(10, 120),
        ];
    }
}
