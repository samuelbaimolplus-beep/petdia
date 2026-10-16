<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\Paquete;
use App\Models\Suscripcion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Suscripcion>
 */
class SuscripcionFactory extends Factory
{
    protected $model = Suscripcion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'id_paciente' => Paciente::factory(),
            'id_paquete' => Paquete::factory(),
            'fecha_inicio' => $inicio,
            'fecha_fin' => fake()->optional()->dateTimeBetween($inicio, '+1 year'),
            'estado' => fake()->randomElement(['ACTIVA', 'CANCELADA', 'EXPIRADA']),
        ];
    }
}
