<?php

namespace Database\Factories;

use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Servicio>
 */
class ServicioFactory extends Factory
{
    protected $model = Servicio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->randomElement(['Baño', 'Corte', 'Corte de uñas', 'Consulta general', 'Vacunacion', 'Desparasitacion']),
            'categoria' => fake()->randomElement(['ESTETICA', 'VETERINARIA']),
            'descripcion' => fake()->optional()->sentence(),
            'observaciones' => fake()->optional()->sentence(),
            'precio_base' => fake()->randomFloat(2, 20, 300),
            'disponible_domicilio' => fake()->boolean(),
            'estado' => 'ACTIVO',
        ];
    }
}
