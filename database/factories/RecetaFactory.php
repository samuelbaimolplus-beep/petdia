<?php

namespace Database\Factories;

use App\Models\AtencionVeterinaria;
use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receta>
 */
class RecetaFactory extends Factory
{
    protected $model = Receta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => AtencionVeterinaria::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'indicaciones_generales' => fake()->optional()->paragraph(),
        ];
    }
}
