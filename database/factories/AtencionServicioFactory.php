<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\AtencionServicio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AtencionServicio>
 */
class AtencionServicioFactory extends Factory
{
    protected $model = AtencionServicio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => Atencion::factory(),
            'id_servicio' => Servicio::factory(),
            'cantidad' => fake()->numberBetween(1, 3),
            'precio_unitario' => fake()->randomFloat(2, 20, 300),
            'descuento' => fake()->optional()->randomFloat(2, 0, 50),
        ];
    }
}
