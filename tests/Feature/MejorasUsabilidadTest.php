<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MejorasUsabilidadTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $user;

    private Propietario $propietario;

    private Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRol = Rol::create([
            'nombre' => 'ADMIN',
            'descripcion' => 'Administrador',
        ]);

        $this->user = Usuario::factory()->create([
            'nombre_usuario' => 'admin_usabilidad',
            'password_hash' => bcrypt('12345678'),
        ]);

        $this->user->roles()->attach($adminRol->id_rol);

        $persona = Persona::create([
            'documento' => '1234567',
            'nombre' => 'María',
            'apellido1' => 'González',
            'telefono' => '0981123456',
            'email' => 'maria@example.com',
            'direccion' => 'Asunción',
            'tipo_persona' => 'FISICA',
        ]);

        $this->propietario = Propietario::create([
            'id_persona' => $persona->id_persona,
            'estado' => 'ACTIVO',
        ]);

        $especie = Especie::create(['nombre' => 'Canino', 'estado' => 'ACTIVO']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Labrador', 'estado' => 'ACTIVO']);

        $this->paciente = Paciente::create([
            'id_propietario' => $this->propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Rocky',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => now()->subYears(2)->toDateString(),
            'color' => 'Dorado',
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_1_1_can_upload_pet_photo_on_update(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('rocky.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->user)->put(route('mascotas.update', $this->paciente->id_paciente), [
            'nombre' => 'Rocky Updated',
            'id_raza' => $this->paciente->id_raza,
            'sexo' => 'MACHO',
            'estado' => 'ACTIVO',
            'foto' => $file,
        ]);

        $response->assertRedirect();
        $this->paciente->refresh();
        $this->assertEquals('Rocky Updated', $this->paciente->nombre);
        $this->assertNotNull($this->paciente->foto);
        Storage::disk('public')->assertExists($this->paciente->foto);
    }

    public function test_1_2_global_search_returns_expected_results(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('buscar', ['q' => 'Rocky']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'query',
            'total',
            'clientes',
            'mascotas',
            'citas',
        ]);

        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, count($data['mascotas']));
        $this->assertEquals('Rocky', $data['mascotas'][0]['nombre']);
    }

    public function test_1_3_notification_center_returns_alerts(): void
    {
        // Creamos una cita para hoy
        Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now()->setHour(10)->setMinute(0),
            'fecha_hora_fin' => now()->setHour(10)->setMinute(30),
            'motivo' => 'Control anual',
            'estado' => 'AGENDADA',
        ]);

        $response = $this->actingAs($this->user)->getJson(route('notificaciones.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total',
            'total_general',
            'notificaciones',
        ]);

        $data = $response->json();
        $this->assertGreaterThanOrEqual(1, $data['total']);
        $this->assertTrue(collect($data['notificaciones'])->contains(fn ($n) => $n['tipo'] === 'cita'));
    }

    public function test_1_3_can_mark_notification_as_read(): void
    {
        $response = $this->actingAs($this->user)->patchJson(route('notificaciones.leer', 'cita-1'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $responseAll = $this->actingAs($this->user)->postJson(route('notificaciones.leer-todas'));
        $responseAll->assertStatus(200);
        $responseAll->assertJson(['success' => true]);
    }

    public function test_1_4_breadcrumbs_are_rendered_in_detail_views(): void
    {
        $response = $this->actingAs($this->user)->get(route('clientes.show', $this->propietario->id_persona));
        $response->assertStatus(200);
        $response->assertSee('aria-label="Breadcrumb"', false);
        $response->assertSee('Clientes');
        $response->assertSee('María González');

        $responsePet = $this->actingAs($this->user)->get(route('mascotas.show', $this->paciente->id_paciente));
        $responsePet->assertStatus(200);
        $responsePet->assertSee('aria-label="Breadcrumb"', false);
        $responsePet->assertSee('Rocky');
    }
}
