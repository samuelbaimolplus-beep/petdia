<?php

namespace Database\Factories;

use App\Models\Pago;
use App\Models\PagoVenta;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoVenta>
 */
class PagoVentaFactory extends Factory
{
    protected $model = PagoVenta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pago' => Pago::factory(),
            'id_venta' => Venta::factory(),
            'monto_aplicado' => fake()->randomFloat(2, 5, 500),
        ];
    }
}
