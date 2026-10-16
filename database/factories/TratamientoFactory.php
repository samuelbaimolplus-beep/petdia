<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\Tratamiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tratamiento>
 */
class TratamientoFactory extends Factory
{
    protected $model = Tratamiento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_paciente' => Paciente::factory(),
            'descripcion' => fake()->sentence(),
            'indicaciones' => fake()->optional()->paragraph(),
            'fecha_inicio' => fake()->dateTimeBetween('-2 months', 'now'),
            'fecha_fin' => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'estado' => fake()->randomElement(['ACTIVO', 'FINALIZADO', 'SUSPENDIDO']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
