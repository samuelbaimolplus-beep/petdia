<?php

namespace Database\Factories;

use App\Models\Propietario;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    protected $model = Venta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_propietario' => Propietario::factory(),
            'id_usuario' => Usuario::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-3 months', 'now'),
            'estado' => fake()->randomElement(['COMPLETADA', 'PENDIENTE', 'ANULADA']),
            'total' => fake()->randomFloat(2, 10, 1000),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
