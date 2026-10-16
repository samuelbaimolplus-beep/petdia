<?php

namespace Database\Factories;

use App\Models\Medicamento;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Medicamento>
 */
class MedicamentoFactory extends Factory
{
    protected $model = Medicamento::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_producto' => Producto::factory()->state([
                'tipo' => 'MEDICAMENTO',
            ]),
            'principio_activo' => fake()->unique()->lexify('PRINCIPIO_????'),
            'presentacion' => fake()->randomElement(['Frasco', 'Blister', 'Jeringa', 'Sobre']),
            'concentracion' => fake()->randomFloat(2, 1, 500),
            'unidad_concentracion' => fake()->randomElement(['mg', 'mg/ml', 'UI', 'g']),
        ];
    }
}
