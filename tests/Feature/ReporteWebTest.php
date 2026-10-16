<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\AtencionEstetica;
use App\Models\AtencionVeterinaria;
use App\Models\Cuenta;
use App\Models\DetallePago;
use App\Models\Especie;
use App\Models\MetodoPago;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporteWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $adminUser;

    protected Usuario $unauthorizedUser;

    protected Paciente $paciente;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolAdmin = Rol::firstOrCreate(['nombre' => 'ADMIN'], ['descripcion' => 'Admin']);
        $rolTransp = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Transporte']);

        // 2. Personas y Usuarios
        $pAdmin = Persona::create([
            'nombre' => 'Gerente',
            'apellido1' => 'Reportes',
            'documento' => '102030',
            'telefono' => '71122334',
            'estado' => 'ACTIVO',
        ]);

        $this->adminUser = Usuario::create([
            'id_persona' => $pAdmin->id_persona,
            'nombre_usuario' => 'admin_rep',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->adminUser->roles()->sync([$rolAdmin->id_rol]);

        $pTransp = Persona::create([
            'nombre' => 'Chofer',
            'apellido1' => 'SinReportes',
            'documento' => '405060',
            'telefono' => '78899002',
            'estado' => 'ACTIVO',
        ]);

        $this->unauthorizedUser = Usuario::create([
            'id_persona' => $pTransp->id_persona,
            'nombre_usuario' => 'transp_rep',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->unauthorizedUser->roles()->sync([$rolTransp->id_rol]);

        // 3. Cliente y Mascota
        $pCliente = Persona::create([
            'nombre' => 'Marcos',
            'apellido1' => 'Suárez',
            'documento' => '887766',
            'telefono' => '72233445',
            'estado' => 'ACTIVO',
        ]);

        $propietario = Propietario::create(['id_persona' => $pCliente->id_persona]);
        $especie = Especie::firstOrCreate(['nombre' => 'Canino']);
        $raza = Raza::firstOrCreate(['nombre' => 'Beagle', 'id_especie' => $especie->id_especie]);

        $this->paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_raza' => $raza->id_raza,
            'nombre' => 'Rocky',
            'sexo' => 'MACHO',
            'fecha_nacimiento' => '2021-08-20',
            'peso_actual' => 12.0,
            'estado' => 'VIVO',
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_ver_reportes(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get(route('reportes.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_ver_panel_de_reportes_con_kpis(): void
    {
        $metodoQR = MetodoPago::firstOrCreate(['nombre' => 'QR'], ['estado' => 'ACTIVO']);

        // 1. Crear cuenta, pago y detalle
        $cuenta = Cuenta::create([
            'id_propietario' => $this->paciente->id_propietario,
            'total' => 250.00,
            'saldo' => 0.00,
            'estado' => 'PAGADA',
        ]);

        $pago = Pago::create([
            'id_cuenta' => $cuenta->id_cuenta,
            'fecha_hora' => now(),
            'estado' => 'APLICADO',
            'observaciones' => 'Cobro consulta y baño',
        ]);

        DetallePago::create([
            'id_pago' => $pago->id_pago,
            'id_metodo_pago' => $metodoQR->id_metodo_pago,
            'monto' => 250.00,
            'referencia' => 'TX-9988',
        ]);

        // 2. Crear atención y su detalle veterinario
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'fecha_hora_fin' => now()->addMinutes(30),
            'estado' => 'COMPLETADA',
        ]);

        AtencionVeterinaria::create([
            'id_atencion' => $atencion->id_atencion,
            'motivo_consulta' => 'Control y vacuna',
            'tipo' => 'CONSULTA',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reportes.index'));
        $response->assertStatus(200);
        $response->assertSee('Panel de Reportes y Analítica');
        $response->assertSee('250.00');
        $response->assertSee('QR');
        $response->assertSee('Atenciones Médicas');
    }

    public function test_admin_puede_filtrar_reportes_por_rango_de_fechas(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('reportes.index', [
            'desde' => now()->subDays(15)->toDateString(),
            'hasta' => now()->toDateString(),
        ]));

        $response->assertStatus(200);
        $response->assertSee('Panel de Reportes y Analítica');
    }

    public function test_admin_puede_exportar_reporte_de_ingresos_a_csv(): void
    {
        $metodoEfectivo = MetodoPago::firstOrCreate(['nombre' => 'EFECTIVO'], ['estado' => 'ACTIVO']);

        $cuenta = Cuenta::create([
            'id_propietario' => $this->paciente->id_propietario,
            'total' => 180.00,
            'saldo' => 0.00,
            'estado' => 'PAGADA',
        ]);

        $pago = Pago::create([
            'id_cuenta' => $cuenta->id_cuenta,
            'fecha_hora' => now(),
            'estado' => 'APLICADO',
        ]);

        DetallePago::create([
            'id_pago' => $pago->id_pago,
            'id_metodo_pago' => $metodoEfectivo->id_metodo_pago,
            'monto' => 180.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reportes.exportar', ['tipo' => 'ingresos']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertStreamed();
    }

    public function test_admin_puede_exportar_reporte_de_atenciones_a_csv(): void
    {
        $atencion = Atencion::create([
            'id_paciente' => $this->paciente->id_paciente,
            'fecha_hora_inicio' => now(),
            'fecha_hora_fin' => now()->addMinutes(45),
            'estado' => 'COMPLETADA',
        ]);

        AtencionEstetica::create([
            'id_atencion' => $atencion->id_atencion,
            'modalidad' => 'SPA',
            'estado_pelaje' => 'BUENO',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reportes.exportar', ['tipo' => 'atenciones']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertStreamed();
    }

    public function test_admin_puede_exportar_reporte_de_inventario_a_csv(): void
    {
        Producto::create([
            'nombre' => 'Colirio Oftálmico Vet',
            'descripcion' => 'Gotas antibióticas',
            'tipo' => 'MEDICAMENTO',
            'categoria' => 'FARMACIA',
            'precio_venta' => 35.00,
            'stock_minimo' => 4,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('reportes.exportar', ['tipo' => 'inventario']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertStreamed();
    }
}
