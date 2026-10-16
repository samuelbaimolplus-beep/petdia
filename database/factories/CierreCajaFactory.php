<?php

namespace Database\Factories;

use App\Models\CierreCaja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CierreCaja>
 */
class CierreCajaFactory extends Factory
{
    protected $model = CierreCaja::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $apertura = fake()->dateTimeBetween('-2 weeks', 'now');

        return [
            'id_usuario' => Usuario::factory(),
            'fecha_hora_apertura' => $apertura,
            'fecha_hora_cierre' => fake()->dateTimeBetween($apertura, '+12 hours'),
            'total_registrado' => fake()->optional()->randomFloat(2, 100, 5000),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
