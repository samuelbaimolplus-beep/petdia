<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    protected $model = Persona::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'documento' => fake()->unique()->numerify('################'),
            'nombre' => fake()->firstName(),
            'apellido1' => fake()->lastName(),
            'apellido2' => fake()->optional()->lastName(),
            'telefono' => fake()->optional()->numerify('7#######'),
            'email' => fake()->boolean(80) ? fake()->unique()->safeEmail() : null,
            'direccion' => fake()->optional()->address(),
        ];
    }
}
