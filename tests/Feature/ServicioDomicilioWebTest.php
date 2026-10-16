<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\ServicioDomicilio;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicioDomicilioWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $superAdmin;

    protected Usuario $recepcionista;

    protected Usuario $conductor;

    protected Usuario $veterinario;

    protected Paciente $paciente;

    protected Servicio $servicio;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolSuperAdmin = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Admin']);
        $rolRecepcion = Rol::firstOrCreate(['nombre' => 'RECEPCION'], ['descripcion' => 'Recepción']);
        $rolTransporte = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Transporte']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Veterinario']);

        // 2. Personas
        $p1 = Persona::create(['nombre' => 'Admin', 'apellido1' => 'Master', 'documento' => '8001', 'email' => 'admin@petdiatria.com', 'estado' => 'ACTIVO']);
        $p2 = Persona::create(['nombre' => 'Recep', 'apellido1' => 'Desk', 'documento' => '8002', 'email' => 'recep@petdiatria.com', 'estado' => 'ACTIVO']);
        $p3 = Persona::create(['nombre' => 'Chofer', 'apellido1' => 'Móvil', 'documento' => '8003', 'email' => 'chofer@petdiatria.com', 'estado' => 'ACTIVO']);
        $p4 = Persona::create(['nombre' => 'Vet', 'apellido1' => 'Doc', 'documento' => '8004', 'email' => 'vet@petdiatria.com', 'estado' => 'ACTIVO']);

        // 3. Usuarios
        $this->superAdmin = Usuario::create([
            'id_persona' => $p1->id_persona,
            'nombre_usuario' => 'admin_dom',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->sync([$rolSuperAdmin->id_rol]);

        $this->recepcionista = Usuario::create([
            'id_persona' => $p2->id_persona,
            'nombre_usuario' => 'recep_dom',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->recepcionista->roles()->sync([$rolRecepcion->id_rol]);

        $this->conductor = Usuario::create([
            'id_persona' => $p3->id_persona,
            'nombre_usuario' => 'chofer_dom',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->conductor->roles()->sync([$rolTransporte->id_rol]);

        $this->veterinario = Usuario::create([
            'id_persona' => $p4->id_persona,
            'nombre_usuario' => 'vet_dom',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->sync([$rolVet->id_rol]);

        // 4. Entorno de datos para citas y domicilios
        $especie = Especie::create(['nombre' => 'Canino']);
        $raza = Raza::create(['id_especie' => $especie->id_especie, 'nombre' => 'Beagle']);

        $propPersona = Persona::create(['nombre' => 'Carmen', 'apellido1' => 'Vega', 'documento' => '334455', 'telefono' => '78901234']);
        $propietario = Propietario::create(['id_persona' => $propPersona->id_persona]);

        $this->paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_especie' => $especie->id_especie,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Rocky',
            'sexo' => 'M',
            'estado' => 'ACTIVO',
        ]);

        $this->servicio = Servicio::create([
            'nombre' => 'Vacunación Antirrábica a Domicilio',
            'categoria' => 'VETERINARIA',
            'precio_base' => 70.00,
            'disponible_domicilio' => true,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_domicilios(): void
    {
        $response = $this->actingAs($this->veterinario)->get(route('domicilios.index'));
        $response->assertStatus(403);
    }

    public function test_recepcion_puede_ver_listado_y_kpis_de_domicilios(): void
    {
        $response = $this->actingAs($this->recepcionista)->get(route('domicilios.index'));

        $response->assertStatus(200);
        $response->assertSee('Servicio a Domicilio y Rutas');
        $response->assertSee('Rutas Hoy');
        $response->assertSee('En Ruta');
    }

    public function test_conductor_ve_vista_itinerario(): void
    {
        $response = $this->actingAs($this->conductor)->get(route('domicilios.index'));

        $response->assertStatus(200);
        $response->assertSee('Mis Rutas Asignadas para Hoy');
        $response->assertSee('Modo Conductor Activo');
    }

    public function test_puede_ver_formulario_de_creacion_de_solicitud(): void
    {
        $response = $this->actingAs($this->recepcionista)->get(route('domicilios.create'));

        $response->assertStatus(200);
        $response->assertSee('Programar Visita o Traslado');
        $response->assertSee('Dirección Exacta');
    }

    public function test_puede_programar_visita_a_domicilio_exitosamente(): void
    {
        $datos = [
            'id_paciente' => $this->paciente->id_paciente,
            'fecha' => now()->toDateString(),
            'hora' => '11:30',
            'direccion' => 'Calle Los Álamos #321, Barrio Norte',
            'referencia' => 'Portón azul frente a la plaza',
            'distancia_estimada' => 8.5,
            'costo_domicilio' => 35.00,
            'servicios' => [$this->servicio->id_servicio],
            'observaciones' => 'Llevar bozal preventivo',
        ];

        $response = $this->actingAs($this->recepcionista)->post(route('domicilios.store'), $datos);

        $response->assertRedirect(route('domicilios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('citas', [
            'id_paciente' => $this->paciente->id_paciente,
            'origen' => 'DOMICILIO',
        ]);

        $this->assertDatabaseHas('servicio_domicilios', [
            'direccion' => 'Calle Los Álamos #321, Barrio Norte',
            'costo_domicilio' => 35.00,
            'estado' => 'PENDIENTE',
        ]);
    }

    public function test_puede_cambiar_estado_a_en_ruta_y_completado(): void
    {
        $cita = Cita::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'fecha_hora_fin' => now()->addHour(),
            'estado' => 'CONFIRMADA',
            'origen' => 'DOMICILIO',
        ]);

        $domicilio = ServicioDomicilio::create([
            'id_cita' => $cita->id_cita,
            'direccion' => 'Av. San Martín #789',
            'costo_domicilio' => 25.00,
            'estado' => 'PENDIENTE',
        ]);

        // Cambiar a EN_RUTA
        $response = $this->actingAs($this->conductor)->post(route('domicilios.cambiar-estado', $domicilio->id_servicio_domicilio), [
            'estado' => 'EN_RUTA',
        ]);

        $response->assertRedirect();
        $domicilio->refresh();
        $this->assertEquals('EN_RUTA', $domicilio->estado);

        // Cambiar a COMPLETADO
        $responseComp = $this->actingAs($this->conductor)->post(route('domicilios.cambiar-estado', $domicilio->id_servicio_domicilio), [
            'estado' => 'COMPLETADO',
        ]);

        $responseComp->assertRedirect();
        $domicilio->refresh();
        $this->assertEquals('COMPLETADO', $domicilio->estado);
    }
}
