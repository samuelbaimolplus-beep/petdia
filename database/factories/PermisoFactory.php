<?php

namespace Database\Factories;

use App\Models\Permiso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permiso>
 */
class PermisoFactory extends Factory
{
    protected $model = Permiso::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => 'PERMISO_'.fake()->unique()->regexify('[A-Z]{3,8}_[A-Z_]{3,10}'),
            'descripcion' => fake()->optional()->sentence(),
        ];
    }
}
