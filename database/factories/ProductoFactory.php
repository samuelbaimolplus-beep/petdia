<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(2, true),
            'tipo' => fake()->randomElement(['MEDICAMENTO', 'INSUMO', 'HERRAMIENTA', 'ACCESORIO']),
            'categoria' => fake()->randomElement(['VETERINARIA', 'ESTETICA']),
            'marca' => fake()->optional()->company(),
            'unidad_medida' => fake()->randomElement(['Unidad', 'Litro', 'Dosis', 'Tableta', 'Kilo']),
            'descripcion' => fake()->optional()->sentence(),
            'precio_venta' => fake()->randomFloat(2, 5, 500),
            'stock_minimo' => fake()->randomFloat(2, 5, 100),
            'control_lotes' => fake()->boolean(),
            'estado' => 'ACTIVO',
        ];
    }
}
