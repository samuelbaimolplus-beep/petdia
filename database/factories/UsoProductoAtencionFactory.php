<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\UsoProductoAtencion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UsoProductoAtencion>
 */
class UsoProductoAtencionFactory extends Factory
{
    protected $model = UsoProductoAtencion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => Atencion::factory(),
            'id_producto' => Producto::factory(),
            'id_lote' => Lote::factory(),
            'cantidad' => fake()->randomFloat(2, 0.5, 10),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
