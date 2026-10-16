<?php

namespace Database\Factories;

use App\Models\Cargo;
use App\Models\Cuenta;
use App\Models\Tratamiento;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cargo>
 */
class CargoFactory extends Factory
{
    protected $model = Cargo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cuenta' => Cuenta::factory(),
            'id_tratamiento' => Tratamiento::factory(),
            'fecha_hora' => fake()->dateTimeBetween('-3 months', 'now'),
            'concepto' => fake()->randomElement(['Consulta veterinaria', 'Baño y corte', 'Vacunación', 'Medicamentos', 'Internación']),
            'monto' => fake()->randomFloat(2, 20, 500),
            'descuento' => fake()->randomFloat(2, 0, 100),
            'estado' => fake()->randomElement(['PENDIENTE', 'PAGADO', 'ANULADO']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
