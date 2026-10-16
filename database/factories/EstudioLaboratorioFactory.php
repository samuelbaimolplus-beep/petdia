<?php

namespace Database\Factories;

use App\Models\EstudioLaboratorio;
use App\Models\Procedimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EstudioLaboratorio>
 */
class EstudioLaboratorioFactory extends Factory
{
    protected $model = EstudioLaboratorio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_procedimiento' => Procedimiento::factory(),
            'tipo_estudio' => fake()->randomElement(['HEMATOLOGIA', 'BIOQUIMICA', 'URIANALISIS', 'COPROLOGICO']),
            'fecha_resultado' => fake()->optional()->dateTimeBetween('-1 week', '+1 week'),
            'resultado' => fake()->optional()->paragraph(),
            'interpretacion' => fake()->optional()->sentence(),
        ];
    }
}
