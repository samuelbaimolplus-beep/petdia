<?php

namespace Database\Factories;

use App\Models\Paciente;
use App\Models\Recordatorio;
use App\Models\Servicio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recordatorio>
 */
class RecordatorioFactory extends Factory
{
    protected $model = Recordatorio::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_paciente' => Paciente::factory(),
            'id_servicio' => Servicio::factory(),
            'id_atencion_origen' => null,
            'id_cita' => null,
            'tipo' => fake()->randomElement(['VACUNACION', 'DESPARASITACION', 'CITA', 'RECONSULTA']),
            'fecha_hora_programada' => fake()->dateTimeBetween('now', '+2 months'),
            'fecha_hora_envio' => fake()->optional()->dateTimeBetween('now', '+2 months'),
            'medio_envio' => fake()->randomElement(['SMS', 'EMAIL', 'WHATSAPP']),
            'mensaje' => fake()->sentence(),
            'estado' => fake()->randomElement(['PENDIENTE', 'ENVIADO', 'CANCELADO']),
        ];
    }
}
