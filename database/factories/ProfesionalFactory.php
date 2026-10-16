<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profesional>
 */
class ProfesionalFactory extends Factory
{
    protected $model = Profesional::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_persona' => Persona::factory(),
            'especialidad' => fake()->randomElement(['Veterinaria General', 'Estetica Canina', 'Veterinaria Especialista', 'Estetica Felina']),
            'registro_profesional' => fake()->unique()->numerify('REG########'),
            'estado' => 'ACTIVO',
        ];
    }
}
