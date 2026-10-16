<?php

namespace Database\Factories;

use App\Models\AdministracionMedicamento;
use App\Models\Procedimiento;
use App\Models\Vacunacion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vacunacion>
 */
class VacunacionFactory extends Factory
{
    protected $model = Vacunacion::class;

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
            'proxima_dosis' => fake()->optional()->dateTimeBetween('+1 month', '+1 year'),
            'sitio_aplicacion' => fake()->randomElement(['Subcutáneo', 'Intramuscular', 'Intranasal']),
        ];
    }
}
