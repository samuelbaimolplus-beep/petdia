<?php

namespace Tests\Feature;

use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteWebTest extends TestCase
{
    use RefreshDatabase;

    private Usuario $admin;

    private Usuario $recepcion;

    private Usuario $veterinario;

    private Especie $especiePerro;

    private Raza $razaLabrador;

    private Propietario $propietario;

    private Paciente $mascota;

    protected function setUp(): void
    {
        parent::setUp();

        $rolAdmin = Rol::create(['nombre' => 'ADMIN', 'descripcion' => 'Administrador']);
        $rolRecepcion = Rol::create(['nombre' => 'RECEPCION', 'descripcion' => 'Recepción']);
        $rolVet = Rol::create(['nombre' => 'VETERINARIO', 'descripcion' => 'Veterinario']);

        $personaAdmin = Persona::create([
            'documento' => '11111111',
            'nombre' => 'Admin',
            'apellido1' => 'Sistema',
            'email' => 'admin@lapetdiatria.com',
        ]);
        $this->admin = Usuario::factory()->create([
            'id_persona' => $personaAdmin->id_persona,
            'nombre_usuario' => 'admin_test',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->admin->roles()->attach($rolAdmin->id_rol);

        $personaRecepcion = Persona::create([
            'documento' => '22222222',
            'nombre' => 'Clara',
            'apellido1' => 'Recepcionista',
            'email' => 'clara@lapetdiatria.com',
        ]);
        $this->recepcion = Usuario::factory()->create([
            'id_persona' => $personaRecepcion->id_persona,
            'nombre_usuario' => 'clara_rec',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->recepcion->roles()->attach($rolRecepcion->id_rol);

        $personaVet = Persona::create([
            'documento' => '33333333',
            'nombre' => 'Dra. Patricia',
            'apellido1' => 'Veterinaria',
            'email' => 'patricia@lapetdiatria.com',
        ]);
        $this->veterinario = Usuario::factory()->create([
            'id_persona' => $personaVet->id_persona,
            'nombre_usuario' => 'dra_patricia',
            'password_hash' => bcrypt('12345678'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->attach($rolVet->id_rol);

        // Catálogos
        $this->especiePerro = Especie::create(['nombre' => 'Canino']);
        $this->razaLabrador = Raza::create(['id_especie' => $this->especiePerro->id_especie, 'nombre' => 'Labrador']);

        // Propietario de prueba
        $personaCliente = Persona::create([
            'documento' => '77889900',
            'nombre' => 'Mario',
            'apellido1' => 'Vargas',
            'apellido2' => 'Rojas',
            'telefono' => '71234567',
            'email' => 'mario.vargas@email.com',
            'direccion' => 'Calle Los Álamos #12',
        ]);
        $this->propietario = Propietario::create(['id_persona' => $personaCliente->id_persona]);

        // Mascota de prueba
        $this->mascota = Paciente::create([
            'id_raza' => $this->razaLabrador->id_raza,
            'id_propietario' => $this->propietario->id_persona,
            'nombre' => 'Rocky',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2022-01-15',
            'tamanio' => 'GRANDE',
            'condicion_reproductiva' => 'CASTRADO',
            'color' => 'Dorado',
            'microchip' => '981000123456',
            'estado' => 'ACTIVO',
            'autorizacion_redes' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_clientes(): void
    {
        $response = $this->get('/clientes');
        $response->assertRedirect('/login');
    }

    public function test_authorized_user_can_view_clientes_list_and_search(): void
    {
        $response = $this->actingAs($this->admin)->get('/clientes');
        $response->assertStatus(200);
        $response->assertSee('Clientes y Mascotas');
        $response->assertSee('Mario Vargas');
        $response->assertSee('Rocky');

        // Búsqueda por nombre de mascota
        $searchResponse = $this->actingAs($this->admin)->get('/clientes?search=Rocky');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Mario Vargas');

        // Búsqueda sin coincidencias
        $emptyResponse = $this->actingAs($this->admin)->get('/clientes?search=Inexistente999');
        $emptyResponse->assertStatus(200);
        $emptyResponse->assertSee('No se encontraron propietarios');
    }

    public function test_read_only_role_can_view_but_cannot_create_propietario(): void
    {
        // Veterinario puede ver listado
        $response = $this->actingAs($this->veterinario)->get('/clientes');
        $response->assertStatus(200);

        // Veterinario no tiene permiso de escritura
        $postResponse = $this->actingAs($this->veterinario)->post('/clientes', [
            'documento' => '55667788',
            'nombre' => 'Nuevo',
            'apellido1' => 'Cliente',
        ]);
        $postResponse->assertStatus(403);
    }

    public function test_recepcion_can_create_new_propietario(): void
    {
        $response = $this->actingAs($this->recepcion)->post('/clientes', [
            'documento' => '44556677',
            'nombre' => 'Camila',
            'apellido1' => 'Fernández',
            'apellido2' => 'Paz',
            'telefono' => '78901234',
            'email' => 'camila.f@email.com',
            'direccion' => 'Av. Ballivián #300',
        ]);

        $this->assertDatabaseHas('personas', [
            'documento' => '44556677',
            'nombre' => 'Camila',
        ]);

        $persona = Persona::where('documento', '44556677')->firstOrFail();
        $this->assertDatabaseHas('propietarios', [
            'id_persona' => $persona->id_persona,
        ]);

        $response->assertRedirect('/clientes/'.$persona->id_persona);
        $response->assertSessionHas('success');
    }

    public function test_user_can_view_ficha_propietario_with_mascotas(): void
    {
        $response = $this->actingAs($this->admin)->get('/clientes/'.$this->propietario->id_persona);

        $response->assertStatus(200);
        $response->assertSee('Mario Vargas');
        $response->assertSee('Rocky');
        $response->assertSee('Labrador');
        $response->assertSee('Agregar Mascota');
    }

    public function test_user_can_update_propietario_datos(): void
    {
        $response = $this->actingAs($this->admin)->put('/clientes/'.$this->propietario->id_persona, [
            'documento' => '77889900',
            'nombre' => 'Mario Alberto',
            'apellido1' => 'Vargas',
            'apellido2' => 'Rojas',
            'telefono' => '79998877',
            'email' => 'mario.alberto@email.com',
            'direccion' => 'Nueva Dirección #999',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('personas', [
            'id_persona' => $this->propietario->id_persona,
            'nombre' => 'Mario Alberto',
            'telefono' => '79998877',
        ]);
    }

    public function test_user_can_create_mascota_for_propietario(): void
    {
        $response = $this->actingAs($this->recepcion)->post('/clientes/'.$this->propietario->id_persona.'/mascotas', [
            'nombre' => 'Bella',
            'id_raza' => $this->razaLabrador->id_raza,
            'sexo' => 'HEMBRA',
            'fecha_nacimiento' => '2023-05-10',
            'tamanio' => 'MEDIANO',
            'condicion_reproductiva' => 'INTACTO',
            'color' => 'Chocolate',
            'microchip' => '981000999888',
            'estado' => 'ACTIVO',
            'autorizacion_redes' => 1,
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pacientes', [
            'id_propietario' => $this->propietario->id_persona,
            'nombre' => 'Bella',
            'sexo' => 'HEMBRA',
        ]);

        $bella = Paciente::where('nombre', 'Bella')->firstOrFail();
        $this->assertDatabaseHas('ficha_medicas', [
            'id_paciente' => $bella->id_paciente,
        ]);
    }

    public function test_user_can_view_ficha_mascota(): void
    {
        $response = $this->actingAs($this->admin)->get('/mascotas/'.$this->mascota->id_paciente);

        $response->assertStatus(200);
        $response->assertSee('Rocky');
        $response->assertSee('Labrador');
        $response->assertSee('Historial');
        $response->assertSee('Historial de Servicios');
        $response->assertSee('Mario Vargas');
    }

    public function test_user_can_update_mascota(): void
    {
        $response = $this->actingAs($this->admin)->put('/mascotas/'.$this->mascota->id_paciente, [
            'nombre' => 'Rocky Balboa',
            'id_raza' => $this->razaLabrador->id_raza,
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2022-01-15',
            'tamanio' => 'GIGANTE',
            'condicion_reproductiva' => 'CASTRADO',
            'color' => 'Dorado Brillante',
            'estado' => 'ACTIVO',
            'autorizacion_redes' => 0,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pacientes', [
            'id_paciente' => $this->mascota->id_paciente,
            'nombre' => 'Rocky Balboa',
            'tamanio' => 'GIGANTE',
        ]);
    }

    public function test_user_can_manage_catalogo_especies_y_razas(): void
    {
        // Ver catálogo
        $response = $this->actingAs($this->admin)->get('/catalogo/especies-razas');
        $response->assertStatus(200);
        $response->assertSee('Especies y Razas');
        $response->assertSee('Canino');

        // Crear nueva especie
        $postEspecie = $this->actingAs($this->admin)->post('/catalogo/especies', [
            'nombre' => 'Felino',
        ]);
        $postEspecie->assertRedirect('/catalogo/especies-razas');
        $this->assertDatabaseHas('especies', ['nombre' => 'Felino']);

        $felino = Especie::where('nombre', 'Felino')->firstOrFail();

        // Crear nueva raza
        $postRaza = $this->actingAs($this->admin)->post('/catalogo/razas', [
            'id_especie' => $felino->id_especie,
            'nombre' => 'Siamés',
        ]);
        $postRaza->assertRedirect('/catalogo/especies-razas');
        $this->assertDatabaseHas('razas', [
            'id_especie' => $felino->id_especie,
            'nombre' => 'Siamés',
        ]);

        // Probar endpoint JSON de razas dependientes
        $jsonResponse = $this->actingAs($this->admin)->getJson('/api/especies/'.$felino->id_especie.'/razas');
        $jsonResponse->assertOk();
        $jsonResponse->assertJsonFragment(['nombre' => 'Siamés']);
    }
}
