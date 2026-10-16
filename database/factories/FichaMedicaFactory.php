<?php

namespace Database\Factories;

use App\Models\FichaMedica;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FichaMedica>
 */
class FichaMedicaFactory extends Factory
{
    protected $model = FichaMedica::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_paciente' => Paciente::factory(),
            'fecha_apertura' => fake()->dateTimeBetween('-3 years', 'now'),
            'antecedentes' => fake()->optional()->paragraph(),
            'alergias' => fake()->optional()->sentence(),
            'enfermedades_previas' => fake()->optional()->sentence(),
            'cirugias_previas' => fake()->optional()->sentence(),
            'medicamentos_habituales' => fake()->optional()->sentence(),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
