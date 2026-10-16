<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AtencionVeterinaria>
 */
class AtencionVeterinariaFactory extends Factory
{
    protected $model = AtencionVeterinaria::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => Atencion::factory(),
            'motivo_consulta' => fake()->sentence(),
            'tipo' => fake()->randomElement(['CONSULTA', 'URGENCIA', 'CONTROL', 'CIRUGIA']),
            'anamnesis' => fake()->optional()->paragraph(),
            'sintomas' => fake()->optional()->sentence(),
            'examen_fisico' => fake()->optional()->paragraph(),
            'conclusion' => fake()->optional()->sentence(),
            'fecha_reconsulta' => fake()->optional()->dateTimeBetween('now', '+4 weeks'),
            'observaciones_clinicas' => fake()->optional()->sentence(),
        ];
    }
}
