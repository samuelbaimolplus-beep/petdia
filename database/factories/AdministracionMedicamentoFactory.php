<?php

namespace Database\Factories;

use App\Models\AdministracionMedicamento;
use App\Models\UsoProductoAtencion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdministracionMedicamento>
 */
class AdministracionMedicamentoFactory extends Factory
{
    protected $model = AdministracionMedicamento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_uso' => UsoProductoAtencion::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-2 weeks', 'now'),
            'dosis' => fake()->randomFloat(2, 0.1, 50),
            'unidad_dosis' => fake()->randomElement(['mg/kg', 'ml', 'Tabletas', 'UI']),
            'via_administracion' => fake()->randomElement(['ORAL', 'SUBCUTANEA', 'INTRAMUSCULAR', 'TOPICA']),
            'observaciones_clinicas' => fake()->optional()->sentence(),
        ];
    }
}
