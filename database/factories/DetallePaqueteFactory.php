<?php

namespace Database\Factories;

use App\Models\DetallePaquete;
use App\Models\Paquete;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetallePaquete>
 */
class DetallePaqueteFactory extends Factory
{
    protected $model = DetallePaquete::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_paquete' => Paquete::factory(),
            'id_servicio' => Servicio::factory(),
            'cantidad_incluida' => fake()->numberBetween(1, 4),
        ];
    }
}
