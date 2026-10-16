<?php

namespace Database\Factories;

use App\Models\AdministracionMedicamento;
use App\Models\Desparasitacion;
use App\Models\Procedimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Desparasitacion>
 */
class DesparasitacionFactory extends Factory
{
    protected $model = Desparasitacion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_procedimiento' => Procedimiento::factory(),
            'id_uso' => AdministracionMedicamento::factory(),
            'tipo' => fake()->randomElement(['INTERNO', 'EXTERNO', 'MIXTO']),
            'proxima_dosis' => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}
