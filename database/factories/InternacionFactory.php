<?php

namespace Database\Factories;

use App\Models\AtencionVeterinaria;
use App\Models\Internacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Internacion>
 */
class InternacionFactory extends Factory
{
    protected $model = Internacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $ingreso = fake()->dateTimeBetween('-2 weeks', 'now');

        return [
            'id_atencion_ingreso' => AtencionVeterinaria::factory(),
            'fecha_hora_ingreso' => $ingreso,
            'fecha_hora_alta' => fake()->optional()->dateTimeBetween($ingreso, '+1 week'),
            'motivo' => fake()->sentence(),
            'estado' => fake()->randomElement(['ACTIVA', 'ALTA', 'TRASLADO']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
