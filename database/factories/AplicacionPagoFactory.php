<?php

namespace Database\Factories;

use App\Models\AplicacionPago;
use App\Models\Cargo;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AplicacionPago>
 */
class AplicacionPagoFactory extends Factory
{
    protected $model = AplicacionPago::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pago' => Pago::factory(),
            'id_cargo' => Cargo::factory(),
            'monto_aplicado' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
