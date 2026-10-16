<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\Propietario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Propietario>
 */
class PropietarioFactory extends Factory
{
    protected $model = Propietario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_persona' => Persona::factory(),
        ];
    }
}
