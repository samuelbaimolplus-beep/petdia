<?php

namespace Tests\Feature;

use App\Models\Especie;
use App\Models\Paciente;
use App\Models\Paquete;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Suscripcion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaqueteWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $adminUser;

    protected Usuario $unauthorizedUser;

    protected Servicio $servicio1;

    protected Servicio $servicio2;

    protected Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolAdmin = Rol::firstOrCreate(['nombre' => 'ADMIN'], ['descripcion' => 'Admin']);
        $rolTransp = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Transporte']);

        // 2. Personas y Usuarios
        $pAdmin = Persona::create([
            'nombre' => 'Admin',
            'apellido1' => 'Paquetes',
            'documento' => '112233',
            'telefono' => '71122334',
            'estado' => 'ACTIVO',
        ]);

        $this->adminUser = Usuario::create([
            'id_persona' => $pAdmin->id_persona,
            'nombre_usuario' => 'admin_pkg',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->adminUser->roles()->sync([$rolAdmin->id_rol]);

        $pTransp = Persona::create([
            'nombre' => 'Chofer',
            'apellido1' => 'SinAcceso',
            'documento' => '445566',
            'telefono' => '78899001',
            'estado' => 'ACTIVO',
        ]);

        $this->unauthorizedUser = Usuario::create([
            'id_persona' => $pTransp->id_persona,
            'nombre_usuario' => 'transp_pkg',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->unauthorizedUser->roles()->sync([$rolTransp->id_rol]);

        // 3. Catálogos y Servicios
        $this->servicio1 = Servicio::create([
            'nombre' => 'Consulta Veterinaria General',
            'categoria' => 'VETERINARIA',
            'precio_base' => 80.00,
            'estado' => 'ACTIVO',
        ]);

        $this->servicio2 = Servicio::create([
            'nombre' => 'Baño Medicado Canino',
            'categoria' => 'ESTETICA',
            'precio_base' => 60.00,
            'estado' => 'ACTIVO',
        ]);

        // 4. Paciente y Propietario
        $pCliente = Persona::create([
            'nombre' => 'Ana',
            'apellido1' => 'Morales',
            'documento' => '778899',
            'telefono' => '70011223',
            'estado' => 'ACTIVO',
        ]);

        $propietario = Propietario::create(['id_persona' => $pCliente->id_persona]);
        $especie = Especie::firstOrCreate(['nombre' => 'Canino']);
        $raza = Raza::firstOrCreate(['nombre' => 'Poodle', 'id_especie' => $especie->id_especie]);

        $this->paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Toby',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2022-01-15',
            'peso_actual' => 8.5,
            'estado' => 'VIVO',
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_ver_paquetes(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('paquetes.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_ver_catalogo_de_paquetes(): void
    {
        Paquete::create([
            'nombre' => 'Plan Cachorro Saludable',
            'descripcion' => 'Vacunas y consultas del primer año',
            'precio' => 250.00,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('paquetes.index'));
        $response->assertStatus(200);
        $response->assertSee('Catálogo de Paquetes y Planes');
        $response->assertSee('Plan Cachorro Saludable');
        $response->assertSee('250.00');
    }

    public function test_admin_puede_crear_paquete_con_servicios(): void
    {
        $data = [
            'nombre' => 'Membresía Spa Mensual',
            'descripcion' => '4 baños y 1 revisión clínica',
            'precio' => 220.00,
            'estado' => 'ACTIVO',
            'servicios' => [
                [
                    'id_servicio' => $this->servicio2->id_servicio,
                    'cantidad_incluida' => 4,
                ],
                [
                    'id_servicio' => $this->servicio1->id_servicio,
                    'cantidad_incluida' => 1,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('paquetes.store'), $data);
        $response->assertRedirect(route('paquetes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('paquetes', [
            'nombre' => 'Membresía Spa Mensual',
            'precio' => 220.00,
            'estado' => 'ACTIVO',
        ]);

        $paquete = Paquete::where('nombre', 'Membresía Spa Mensual')->first();
        $this->assertNotNull($paquete);
        $this->assertCount(2, $paquete->detallePaquetes);
    }

    public function test_admin_puede_editar_paquete_y_servicios(): void
    {
        $paquete = Paquete::create([
            'nombre' => 'Plan Inicial',
            'descripcion' => 'Plan básico',
            'precio' => 100.00,
            'estado' => 'ACTIVO',
        ]);

        $editData = [
            'nombre' => 'Plan Inicial Plus',
            'descripcion' => 'Plan básico mejorado con spa',
            'precio' => 140.00,
            'estado' => 'ACTIVO',
            'servicios' => [
                [
                    'id_servicio' => $this->servicio1->id_servicio,
                    'cantidad_incluida' => 2,
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->put(route('paquetes.update', $paquete), $editData);
        $response->assertRedirect(route('paquetes.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('paquetes', [
            'id_paquete' => $paquete->id_paquete,
            'nombre' => 'Plan Inicial Plus',
            'precio' => 140.00,
        ]);
    }

    public function test_admin_puede_alternar_estado_de_paquete(): void
    {
        $paquete = Paquete::create([
            'nombre' => 'Plan Temporal',
            'precio' => 50.00,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('paquetes.toggle-estado', $paquete));
        $response->assertRedirect(route('paquetes.index'));

        $this->assertDatabaseHas('paquetes', [
            'id_paquete' => $paquete->id_paquete,
            'estado' => 'INACTIVO',
        ]);
    }

    public function test_admin_puede_gestionar_suscripciones(): void
    {
        $paquete = Paquete::create([
            'nombre' => 'Plan Dental & Spa',
            'precio' => 300.00,
            'estado' => 'ACTIVO',
        ]);

        // 1. Ver vista suscripciones
        $response = $this->actingAs($this->adminUser)->get(route('paquetes.suscripciones'));
        $response->assertStatus(200);
        $response->assertSee('Suscripciones de Pacientes');

        // 2. Asignar suscripción a paciente
        $subData = [
            'id_paciente' => $this->paciente->id_paciente,
            'id_paquete' => $paquete->id_paquete,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
        ];

        $postResp = $this->actingAs($this->adminUser)->post(route('paquetes.suscripciones.store'), $subData);
        $postResp->assertRedirect(route('paquetes.suscripciones'));
        $postResp->assertSessionHas('success');

        $this->assertDatabaseHas('suscripciones', [
            'id_paciente' => $this->paciente->id_paciente,
            'id_paquete' => $paquete->id_paquete,
            'estado' => 'ACTIVO',
        ]);

        $suscripcion = Suscripcion::where('id_paciente', $this->paciente->id_paciente)->first();

        // 3. Cancelar suscripción
        $cancelResp = $this->actingAs($this->adminUser)->post(route('paquetes.suscripciones.cancelar', $suscripcion));
        $cancelResp->assertRedirect(route('paquetes.suscripciones'));

        $this->assertDatabaseHas('suscripciones', [
            'id_suscripcion' => $suscripcion->id_suscripcion,
            'estado' => 'CANCELADO',
        ]);
    }
}
