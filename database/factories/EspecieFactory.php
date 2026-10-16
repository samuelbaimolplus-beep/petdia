<?php

namespace Database\Factories;

use App\Models\Especie;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Especie>
 */
class EspecieFactory extends Factory
{
    protected $model = Especie::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->numerify('Especie ####'),
        ];
    }
}
