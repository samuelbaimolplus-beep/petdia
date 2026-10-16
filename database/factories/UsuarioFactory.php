<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Usuario>
 */
class UsuarioFactory extends Factory
{
    protected $model = Usuario::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_persona' => Persona::factory(),
            'nombre_usuario' => fake()->unique()->userName(),
            'password_hash' => bcrypt('password'),
            'estado' => 'ACTIVO',
            'email_verified_at' => now(),
            'ultimo_acceso' => fake()->optional()->dateTime(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'email_verification_token' => Str::random(64),
            'email_verification_expires_at' => now()->addHours(24),
        ]);
    }
}
