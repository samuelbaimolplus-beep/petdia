<?php

namespace Database\Factories;

use App\Models\Internacion;
use App\Models\Profesional;
use App\Models\SeguimientoInternacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeguimientoInternacion>
 */
class SeguimientoInternacionFactory extends Factory
{
    protected $model = SeguimientoInternacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_internacion' => Internacion::factory(),
            'id_profesional' => Profesional::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'cuidados' => fake()->paragraph(),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
