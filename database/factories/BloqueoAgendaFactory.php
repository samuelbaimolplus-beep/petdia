<?php

namespace Database\Factories;

use App\Models\BloqueoAgenda;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BloqueoAgenda>
 */
class BloqueoAgendaFactory extends Factory
{
    protected $model = BloqueoAgenda::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-1 week', '+4 weeks');

        return [
            'id_profesional' => Profesional::factory(),
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->modify('+'.fake()->numberBetween(1, 8).' hours'),
            'motivo' => fake()->optional()->sentence(),
        ];
    }
}
