<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\Propietario;
use App\Models\Raza;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Paciente>
 */
class PacienteFactory extends Factory
{
    protected $model = Paciente::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_raza' => Raza::factory(),
            'id_propietario' => Propietario::factory(),
            'nombre' => fake()->firstName(),
            'sexo' => fake()->randomElement(['MACHO', 'HEMBRA']),
            'fecha_nacimiento' => fake()->date(),
            'tamanio' => fake()->randomElement(['PEQUEÑO', 'MEDIANO', 'GRANDE']),
            'condicion_reproductiva' => fake()->randomElement(['INTACTO', 'CASTRADO']),
            'color' => fake()->safeColorName(),
            'microchip' => fake()->boolean(80) ? fake()->unique()->numerify('################') : null,
            'fecha_implantacion' => fake()->optional()->date(),
            'estado' => 'ACTIVO',
            'caract_particulares' => fake()->optional()->sentence(),
            'tipo_pelaje' => fake()->optional()->randomElement(['CORTO', 'LARGO', 'CURLY', 'SIN PELO']),
            'comportamiento_normal' => fake()->optional()->sentence(),
            'recomendaciones' => fake()->optional()->sentence(),
            'autorizacion_redes' => fake()->boolean(),
        ];
    }
}
