<?php

namespace Database\Factories;

use App\Models\Atencion;
use App\Models\ParticipacionAtencion;
use App\Models\Profesional;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ParticipacionAtencion>
 */
class ParticipacionAtencionFactory extends Factory
{
    protected $model = ParticipacionAtencion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_atencion' => Atencion::factory(),
            'id_profesional' => Profesional::factory(),
            'rol_atencion' => fake()->randomElement(['PRINCIPAL', 'ASISTENTE', 'SUPERVISOR']),
            'observaciones' => fake()->optional()->sentence(),
        ];
    }
}
