<?php

namespace Database\Factories;

use App\Models\EgresoCaja;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EgresoCaja>
 */
class EgresoCajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_usuario' => Usuario::factory(),
            'id_cierre' => null,
            'monto' => fake()->randomFloat(2, 5, 200),
            'concepto' => fake()->sentence(4),
            'categoria' => fake()->randomElement([
                'Insumos y Limpieza',
                'Farmacia y Material Menor',
                'Servicios y Mantenimiento',
                'Viáticos y Transporte',
                'Caja / Vuelto',
                'Otros',
            ]),
            'comprobante' => fake()->optional()->bothify('REC-####'),
            'fecha_hora' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
