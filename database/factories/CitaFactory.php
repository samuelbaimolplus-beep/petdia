<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cita>
 */
class CitaFactory extends Factory
{
    protected $model = Cita::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-1 week', '+4 weeks');

        return [
            'id_paciente' => Paciente::factory(),
            'id_profesional' => Profesional::factory(),
            'fecha_hora_inicio' => $inicio,
            'fecha_hora_fin' => (clone $inicio)->modify('+'.fake()->numberBetween(30, 120).' minutes'),
            'estado' => fake()->randomElement(['PROGRAMADA', 'CONFIRMADA', 'COMPLETADA', 'CANCELADA']),
            'motivo_cancelacion' => null,
            'origen' => fake()->randomElement(['RECEPCION', 'WEB']),
        ];
    }
}
