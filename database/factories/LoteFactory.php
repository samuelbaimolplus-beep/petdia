<?php

namespace Database\Factories;

use App\Models\DetalleCompra;
use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lote>
 */
class LoteFactory extends Factory
{
    protected $model = Lote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_producto' => Producto::factory(),
            'id_detalle_compra' => DetalleCompra::factory(),
            'numero_lote' => fake()->unique()->bothify('LOT-####-??'),
            'fecha_vencimiento' => fake()->dateTimeBetween('now', '+2 years'),
            'estado' => fake()->randomElement(['VIGENTE', 'VENCIDO', 'AGOTADO']),
        ];
    }
}
