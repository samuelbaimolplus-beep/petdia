<?php

namespace Database\Factories;

use App\Models\DetalleReceta;
use App\Models\Medicamento;
use App\Models\Receta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DetalleReceta>
 */
class DetalleRecetaFactory extends Factory
{
    protected $model = DetalleReceta::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_receta' => Receta::factory(),
            'id_producto' => Medicamento::factory(),
            'dosis' => fake()->randomFloat(2, 0.1, 50),
            'unidad_dosis' => fake()->randomElement(['mg/kg', 'ml', 'Tabletas', 'UI']),
            'frecuencia' => fake()->randomElement(['CADA_8_HORAS', 'CADA_12_HORAS', 'UNA_VEZ_AL_DIA', 'SEGUN_INDICACION']),
            'duracion' => fake()->randomElement(['5 días', '7 días', '10 días', '14 días']),
            'via_administracion' => fake()->randomElement(['ORAL', 'TOPICA', 'SUBCUTANEA']),
            'indicaciones' => fake()->optional()->sentence(),
        ];
    }
}
