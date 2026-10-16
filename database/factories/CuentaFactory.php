<?php

namespace Database\Factories;

use App\Models\Cuenta;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cuenta>
 */
class CuentaFactory extends Factory
{
    protected $model = Cuenta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_propietario' => Propietario::factory(),
            'fecha_apertura' => fake()->dateTimeBetween('-3 years', 'now'),
            'estado' => fake()->randomElement(['ACTIVA', 'CERRADA']),
        ];
    }
}
