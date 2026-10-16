<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Diagnostico;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtencionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_atenciones_with_paciente(): void
    {
        Atencion::factory()->count(3)->create();

        $this->getJson('/api/v1/atenciones')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id',
                    'fecha_hora_inicio',
                    'estado',
                    'paciente' => ['id', 'nombre'],
                ]],
            ]);
    }

    public function test_index_filters_by_paciente_id(): void
    {
        $atencion = Atencion::factory()->create();
        Atencion::factory()->count(2)->create();

        $this->getJson('/api/v1/atenciones?paciente_id='.$atencion->id_paciente)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $atencion->id_atencion);
    }

    public function test_show_returns_atencion_veterinaria_with_diagnosticos(): void
    {
        $atencionVeterinaria = AtencionVeterinaria::factory()->create();
        Diagnostico::factory()->create(['id_atencion' => $atencionVeterinaria->id_atencion]);

        $this->getJson('/api/v1/atenciones/'.$atencionVeterinaria->id_atencion)
            ->assertOk()
            ->assertJsonPath('data.atencion_veterinaria.tipo', $atencionVeterinaria->tipo)
            ->assertJsonCount(1, 'data.atencion_veterinaria.diagnosticos');
    }

    public function test_show_returns_404_for_missing_atencion(): void
    {
        $this->getJson('/api/v1/atenciones/999999')->assertNotFound();
    }
}
