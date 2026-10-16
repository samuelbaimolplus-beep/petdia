<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\AtencionEstetica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AtencionEstetica>
 */
class AtencionEsteticaFactory extends Factory
{
    protected $model = AtencionEstetica::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => Atencion::factory(),
            'modalidad' => fake()->randomElement(['BAÑO', 'CORTE', 'BAÑO_CORTE', 'HIGIENE']),
            'estado_pelaje' => fake()->optional()->randomElement(['ENMARAÑADO', 'NORMAL', 'SECO', 'GRASO']),
            'caracteristicas_observadas' => fake()->optional()->sentence(),
        ];
    }
}
