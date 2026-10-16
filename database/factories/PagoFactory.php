<?php

namespace Database\Factories;

use App\Models\CierreCaja;
use App\Models\Cuenta;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pago>
 */
class PagoFactory extends Factory
{
    protected $model = Pago::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cuenta' => Cuenta::factory(),
            'id_cierre' => CierreCaja::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-3 months', 'now'),
            'estado' => fake()->randomElement(['COMPLETADO', 'PENDIENTE', 'ANULADO']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
