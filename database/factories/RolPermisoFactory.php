<?php

namespace Database\Factories;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\RolPermiso;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RolPermiso>
 */
class RolPermisoFactory extends Factory
{
    protected $model = RolPermiso::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_rol' => Rol::factory(),
            'id_permiso' => Permiso::factory(),
        ];
    }
}
