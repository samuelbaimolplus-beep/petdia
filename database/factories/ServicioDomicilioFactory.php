<?php

namespace Database\Factories;

use App\Models\Cita;
use App\Models\ServicioDomicilio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicioDomicilio>
 */
class ServicioDomicilioFactory extends Factory
{
    protected $model = ServicioDomicilio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cita' => Cita::factory(),
            'direccion' => fake()->address(),
            'referencia' => fake()->optional()->sentence(),
            'distancia_estimada' => fake()->optional()->randomFloat(2, 0.5, 20),
            'costo_domicilio' => fake()->randomFloat(2, 10, 80),
            'estado' => 'PENDIENTE',
        ];
    }
}
