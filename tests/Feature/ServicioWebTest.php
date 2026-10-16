<?php

namespace Tests\Feature;

use App\Models\Especie;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\TiempoEstimado;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicioWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $superAdmin;

    protected Usuario $veterinario;

    protected Especie $especieCanino;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolSuperAdmin = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Admin']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Veterinario']);

        // 2. Personas
        $p1 = Persona::create(['nombre' => 'Admin', 'apellido1' => 'Master', 'documento' => '9001', 'email' => 'admin@petdiatria.com', 'estado' => 'ACTIVO']);
        $p2 = Persona::create(['nombre' => 'Vet', 'apellido1' => 'Clínico', 'documento' => '9002', 'email' => 'vet@petdiatria.com', 'estado' => 'ACTIVO']);

        // 3. Usuarios
        $this->superAdmin = Usuario::create([
            'id_persona' => $p1->id_persona,
            'nombre_usuario' => 'admin_serv',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->sync([$rolSuperAdmin->id_rol]);

        $this->veterinario = Usuario::create([
            'id_persona' => $p2->id_persona,
            'nombre_usuario' => 'vet_serv',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->sync([$rolVet->id_rol]);

        // 4. Especie para tiempos
        $this->especieCanino = Especie::create(['nombre' => 'Canino']);
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_servicios(): void
    {
        $response = $this->actingAs($this->veterinario)->get(route('servicios.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_ver_listado_de_servicios_y_kpis(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('servicios.index'));

        $response->assertStatus(200);
        $response->assertSee('Catálogo de Servicios');
        $response->assertSee('Total Servicios');
        $response->assertSee('Clínicos / Vet');
    }

    public function test_puede_ver_formulario_de_creacion(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('servicios.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrar Servicio');
        $response->assertSee('Precio Base');
    }

    public function test_puede_crear_servicio_con_duracion_inicial(): void
    {
        $datos = [
            'nombre' => 'Ecografía Abdominal Completa',
            'categoria' => 'VETERINARIA',
            'precio_base' => 120.00,
            'disponible_domicilio' => 1,
            'descripcion' => 'Ecografía de alta resolución para órganos abdominales',
            'duracion_minutos_defecto' => 45,
            'id_especie_defecto' => $this->especieCanino->id_especie,
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('servicios.store'), $datos);

        $response->assertRedirect(route('servicios.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('servicios', [
            'nombre' => 'Ecografía Abdominal Completa',
            'categoria' => 'VETERINARIA',
            'precio_base' => 120.00,
            'disponible_domicilio' => true,
        ]);

        $servicio = Servicio::where('nombre', 'Ecografía Abdominal Completa')->first();
        $this->assertDatabaseHas('tiempos_estimados', [
            'id_servicio' => $servicio->id_servicio,
            'id_especie' => $this->especieCanino->id_especie,
            'duracion_minutos' => 45,
        ]);
    }

    public function test_puede_ver_formulario_de_edicion(): void
    {
        $servicio = Servicio::create([
            'nombre' => 'Limpieza Dental por Ultrasonido',
            'categoria' => 'VETERINARIA',
            'precio_base' => 180.00,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('servicios.edit', $servicio->id_servicio));

        $response->assertStatus(200);
        $response->assertSee('Editar: Limpieza Dental por Ultrasonido');
        $response->assertSee('Duración Estimada');
    }

    public function test_puede_actualizar_servicio(): void
    {
        $servicio = Servicio::create([
            'nombre' => 'Corte Sanitario',
            'categoria' => 'ESTETICA',
            'precio_base' => 35.00,
            'estado' => 'ACTIVO',
        ]);

        $datos = [
            'nombre' => 'Corte Higiénico y Vaciado',
            'categoria' => 'ESTETICA',
            'precio_base' => 40.00,
            'disponible_domicilio' => 0,
            'descripcion' => 'Corte de almohadillas y zona perianal',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->superAdmin)->put(route('servicios.update', $servicio->id_servicio), $datos);

        $response->assertRedirect(route('servicios.index'));
        $this->assertDatabaseHas('servicios', [
            'id_servicio' => $servicio->id_servicio,
            'nombre' => 'Corte Higiénico y Vaciado',
            'precio_base' => 40.00,
        ]);
    }

    public function test_puede_alternar_estado_activo_inactivo(): void
    {
        $servicio = Servicio::create([
            'nombre' => 'Peluquería Canina Raza Grande',
            'categoria' => 'ESTETICA',
            'precio_base' => 90.00,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('servicios.toggle-estado', $servicio->id_servicio));

        $response->assertRedirect();
        $servicio->refresh();
        $this->assertEquals('INACTIVO', $servicio->estado);

        // Volver a activar
        $this->actingAs($this->superAdmin)->post(route('servicios.toggle-estado', $servicio->id_servicio));
        $servicio->refresh();
        $this->assertEquals('ACTIVO', $servicio->estado);
    }

    public function test_puede_agregar_y_eliminar_regla_de_tiempo_estimado(): void
    {
        $servicio = Servicio::create([
            'nombre' => 'Baño Médico Dermatológico',
            'categoria' => 'ESTETICA',
            'precio_base' => 65.00,
            'estado' => 'ACTIVO',
        ]);

        $datosTiempo = [
            'id_especie' => $this->especieCanino->id_especie,
            'peso_min' => 10.0,
            'peso_max' => 25.0,
            'duracion_minutos' => 50,
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('servicios.tiempos.store', $servicio->id_servicio), $datosTiempo);
        $response->assertRedirect(route('servicios.edit', $servicio->id_servicio));

        $this->assertDatabaseHas('tiempos_estimados', [
            'id_servicio' => $servicio->id_servicio,
            'id_especie' => $this->especieCanino->id_especie,
            'duracion_minutos' => 50,
        ]);

        $tiempo = TiempoEstimado::where('id_servicio', $servicio->id_servicio)->first();

        // Eliminar regla
        $responseDel = $this->actingAs($this->superAdmin)->delete(route('servicios.tiempos.delete', $tiempo->id_tiempo_estimado));
        $responseDel->assertRedirect(route('servicios.edit', $servicio->id_servicio));

        $this->assertDatabaseMissing('tiempos_estimados', [
            'id_tiempo_estimado' => $tiempo->id_tiempo_estimado,
        ]);
    }
}
