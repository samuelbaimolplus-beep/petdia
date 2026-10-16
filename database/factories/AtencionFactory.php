<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\Cita;
use App\Models\Paciente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Atencion>
 */
class AtencionFactory extends Factory
{
    protected $model = Atencion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-2 weeks', 'now');

        return [
            'id_paciente' => Paciente::factory(),
            'id_cita' => Cita::factory(),
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => fake()->dateTimeBetween($inicio, '+3 hours'),
            'estado' => fake()->randomElement(['COMPLETADA', 'EN_PROCESO']),
            'observaciones' => fake()->optional()->sentence(),
            'recomendaciones' => fake()->optional()->sentence(),
            'total_final' => fake()->optional()->randomFloat(2, 50, 500),
        ];
    }
}
