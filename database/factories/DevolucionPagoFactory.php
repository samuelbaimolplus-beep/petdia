<?php

namespace Database\Factories;

use App\Models\DevolucionPago;
use App\Models\Pago;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DevolucionPago>
 */
class DevolucionPagoFactory extends Factory
{
    protected $model = DevolucionPago::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pago' => Pago::factory(),
            'id_usuario' => Usuario::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-1 month', 'now'),
            'monto' => fake()->randomFloat(2, 1, 200),
            'motivo' => fake()->sentence(),
            'estado' => fake()->randomElement(['PROCESADA', 'PENDIENTE']),
        ];
    }
}
