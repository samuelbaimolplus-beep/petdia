<?php

namespace Database\Factories;

use App\Models\AtencionVeterinaria;
use App\Models\Procedimiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Procedimiento>
 */
class ProcedimientoFactory extends Factory
{
    protected $model = Procedimiento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => AtencionVeterinaria::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'descripcion' => fake()->sentence(),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
