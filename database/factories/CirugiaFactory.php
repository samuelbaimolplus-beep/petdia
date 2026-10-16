<?php

namespace Database\Factories;

use App\Models\Cirugia;
use App\Models\Procedimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cirugia>
 */
class CirugiaFactory extends Factory
{
    protected $model = Cirugia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_procedimiento' => Procedimiento::factory(),
            'tipo_cirugia' => fake()->randomElement(['CASTRACION', 'OVARIOHISTERECTOMIA', 'TRAUMATOLOGICA', 'TEJIDOS BLANDOS']),
            'diagnostico_preoperatorio' => fake()->sentence(),
            'complicaciones' => fake()->optional()->sentence(),
            'indicaciones_postoperatorias' => fake()->optional()->paragraph(),
            'resultado' => fake()->randomElement(['EXITOSO', 'CON_COMPLICACIONES']),
        ];
    }
}
