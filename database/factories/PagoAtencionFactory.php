<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\Pago;
use App\Models\PagoAtencion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PagoAtencion>
 */
class PagoAtencionFactory extends Factory
{
    protected $model = PagoAtencion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pago' => Pago::factory(),
            'id_atencion' => Atencion::factory(),
            'monto_aplicado' => fake()->randomFloat(2, 10, 500),
        ];
    }
}
