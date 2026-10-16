<?php

namespace Database\Factories;

use App\Models\HorarioProfesional;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HorarioProfesional>
 */
class HorarioProfesionalFactory extends Factory
{
    protected $model = HorarioProfesional::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_profesional' => Profesional::factory(),
            'dia_semana' => fake()->numberBetween(1, 7),
            'hora_inicio' => fake()->time('H:i:s'),
            'hora_fin' => fake()->time('H:i:s'),
            'estado' => 'ACTIVO',
        ];
    }
}
