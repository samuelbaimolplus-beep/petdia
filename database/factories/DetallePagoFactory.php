<?php

namespace Database\Factories;

use App\Models\DetallePago;
use App\Models\MetodoPago;
use App\Models\Pago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetallePago>
 */
class DetallePagoFactory extends Factory
{
    protected $model = DetallePago::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_pago' => Pago::factory(),
            'id_metodo_pago' => MetodoPago::factory(),
            'monto' => fake()->randomFloat(2, 10, 500),
            'referencia' => fake()->optional()->bothify('REF-####'),
        ];
    }
}
