<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\AtencionVeterinaria;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Carbon\Carbon;
use Tests\TestCase;

class MejorasFormulariosYRendimientoTest extends TestCase
{
    private function createAuthUser(string $rolName = 'VETERINARIO'): Usuario
    {
        $persona = Persona::create([
            'documento' => 'DOC-'.uniqid(),
            'nombre' => 'Test',
            'apellido1' => 'Vet',
            'email' => 'vet_'.uniqid().'@example.com',
        ]);

        $usuario = Usuario::create([
            'id_persona' => $persona->id_persona,
            'nombre_usuario' => 'vet_'.uniqid(),
            'password_hash' => bcrypt('password'),
            'estado' => 'ACTIVO',
        ]);

        $rol = Rol::firstOrCreate(['nombre' => $rolName]);
        $usuario->roles()->attach($rol->id_rol);

        return $usuario;
    }

    private function createPacienteConAtenciones(int $cantidad = 3): Paciente
    {
        $especie = Especie::firstOrCreate(['nombre' => 'Canino']);
        $raza = Raza::firstOrCreate(['id_especie' => $especie->id_especie, 'nombre' => 'Labrador']);

        $persona = Persona::create([
            'documento' => 'PROP-'.uniqid(),
            'nombre' => 'Dueño',
            'apellido1' => 'Prueba',
            'email' => 'dueno_'.uniqid().'@example.com',
        ]);

        $propietario = Propietario::create([
            'id_persona' => $persona->id_persona,
        ]);

        $paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Max',
            'sexo' => 'MACHO',
            'estado' => 'VIVO',
        ]);

        for ($i = 0; $i < $cantidad; $i++) {
            $atencion = Atencion::create([
                'id_paciente' => $paciente->id_paciente,
                'fecha_hora_inicio' => Carbon::now()->subDays($i),
                'estado' => 'COMPLETADA',
            ]);

            AtencionVeterinaria::create([
                'id_atencion' => $atencion->id_atencion,
                'tipo' => 'CONSULTA',
                'motivo_consulta' => "Consulta médica número {$i}",
                'anamnesis' => "Anamnesis de prueba {$i}",
            ]);
        }

        return $paciente;
    }

    public function test_6_1_paginacion_infinita_entradas_timeline(): void
    {
        $vet = $this->createAuthUser('VETERINARIO');
        $paciente = $this->createPacienteConAtenciones(7);

        $response = $this->actingAs($vet)->getJson(route('historial.entradas.index', [
            'id' => $paciente->id_paciente,
            'page' => 1,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'html',
            'has_more',
            'current_page',
            'total',
        ]);

        $this->assertTrue($response->json('has_more'));
        $this->assertEquals(1, $response->json('current_page'));
        $this->assertStringContainsString('Consulta médica número', $response->json('html'));
    }

    public function test_5_2_vista_historial_renderiza_autoguardado_y_timeline(): void
    {
        $vet = $this->createAuthUser('VETERINARIO');
        $paciente = $this->createPacienteConAtenciones(3);

        $response = $this->actingAs($vet)->get(route('historial.show', $paciente->id_paciente));

        $response->assertStatus(200);
        // Debe contener el script y banner de recuperación de autoguardado (Mejora 5.2)
        $response->assertSee('draftRecoverBanner');
        $response->assertSee('Hay un borrador guardado automáticamente');
        $response->assertSee('historialManager()');
    }

    public function test_6_2_dark_mode_toggle_presente_en_layout(): void
    {
        $admin = $this->createAuthUser('SUPER_ADMIN');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        // Debe incluir soporte de tema oscuro
        $response->assertSee('darkMode');
        $response->assertSee('Alternar modo oscuro y claro');
        $response->assertSee("localStorage.getItem('theme')", false);
    }
}
