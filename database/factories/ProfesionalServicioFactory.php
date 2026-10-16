<?php

namespace Database\Factories;

use App\Models\Profesional;
use App\Models\ProfesionalServicio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProfesionalServicio>
 */
class ProfesionalServicioFactory extends Factory
{
    protected $model = ProfesionalServicio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_profesional' => Profesional::factory(),
            'id_servicio' => Servicio::factory(),
            'estado' => 'ACTIVO',
        ];
    }
}
