<?php

namespace Database\Factories;

use App\Models\AtencionTratamiento;
use App\Models\AtencionVeterinaria;
use App\Models\Tratamiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AtencionTratamiento>
 */
class AtencionTratamientoFactory extends Factory
{
    protected $model = AtencionTratamiento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => AtencionVeterinaria::factory(),
            'id_tratamiento' => Tratamiento::factory(),
            'tipo_relacion' => fake()->randomElement(['INICIO', 'SEGUIMIENTO', 'AJUSTE']),
        ];
    }
}
