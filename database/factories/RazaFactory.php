<?php

namespace Database\Factories;

use App\Models\Especie;
use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Raza>
 */
class RazaFactory extends Factory
{
    protected $model = Raza::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_especie' => Especie::factory(),
            'nombre' => fake()->unique()->word(),
        ];
    }
}
