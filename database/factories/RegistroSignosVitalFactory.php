<?php

namespace Database\Factories;

use App\Models\AtencionVeterinaria;
use App\Models\RegistroSignosVital;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RegistroSignosVital>
 */
class RegistroSignosVitalFactory extends Factory
{
    protected $model = RegistroSignosVital::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => AtencionVeterinaria::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'peso' => fake()->randomFloat(2, 1, 50),
            'temperatura' => fake()->randomFloat(1, 36, 40),
            'frecuencia_cardiaca' => fake()->numberBetween(60, 160),
            'frecuencia_respiratoria' => fake()->numberBetween(10, 40),
        ];
    }
}
