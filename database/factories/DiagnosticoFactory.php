<?php

namespace Database\Factories;

use App\Models\AtencionVeterinaria;
use App\Models\Diagnostico;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diagnostico>
 */
class DiagnosticoFactory extends Factory
{
    protected $model = Diagnostico::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => AtencionVeterinaria::factory(),
            'nombre' => fake()->randomElement(['Sano', 'Infección cutánea', 'Otitis', 'Parvovirosis', 'Dermatitis alérgica']),
            'descripcion' => fake()->optional()->paragraph(),
            'tipo' => fake()->randomElement(['PRESUNTIVO', 'DEFINITIVO', 'DESCARTADO']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
