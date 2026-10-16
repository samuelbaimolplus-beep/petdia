<?php

namespace Database\Factories;

use App\Models\Proveedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Proveedor>
 */
class ProveedorFactory extends Factory
{
    protected $model = Proveedor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->company(),
            'nit' => fake()->boolean(80) ? fake()->unique()->numerify('############') : null,
            'telefono' => fake()->optional()->numerify('7#######'),
            'email' => fake()->boolean(80) ? fake()->unique()->safeEmail() : null,
            'direccion' => fake()->optional()->address(),
            'estado' => 'ACTIVO',
        ];
    }
}
