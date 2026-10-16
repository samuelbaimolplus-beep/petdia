<?php

namespace Tests\Feature;

use App\Models\Atencion;
use App\Models\Cuenta;
use App\Models\DetallePago;
use App\Models\EgresoCaja;
use App\Models\Especie;
use App\Models\MetodoPago;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Persona;
use App\Models\Propietario;
use App\Models\Raza;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CajaWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $superAdmin;

    protected Usuario $recepcionista;

    protected Usuario $veterinario;

    protected MetodoPago $metodoEfectivo;

    protected MetodoPago $metodoQR;

    protected Especie $especie;

    protected Raza $raza;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolSuperAdmin = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Administrador']);
        $rolRecepcion = Rol::firstOrCreate(['nombre' => 'RECEPCION'], ['descripcion' => 'Recepción']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Veterinario']);

        // 2. Personas
        $p1 = Persona::create([
            'nombre' => 'Admin', 'apellido1' => 'Sistema', 'documento' => '111111',
            'telefono' => '70000001', 'email' => 'admin@petdiatria.com', 'estado' => 'ACTIVO',
        ]);
        $p2 = Persona::create([
            'nombre' => 'Recep', 'apellido1' => 'Frontdesk', 'documento' => '222222',
            'telefono' => '70000002', 'email' => 'recep@petdiatria.com', 'estado' => 'ACTIVO',
        ]);
        $p3 = Persona::create([
            'nombre' => 'Carlos', 'apellido1' => 'Médico', 'documento' => '333333',
            'telefono' => '70000003', 'email' => 'vet@petdiatria.com', 'estado' => 'ACTIVO',
        ]);

        // 3. Usuarios con roles asignados vía pivote
        $this->superAdmin = Usuario::create([
            'id_persona' => $p1->id_persona,
            'nombre_usuario' => 'admin_test',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->sync([$rolSuperAdmin->id_rol]);

        $this->recepcionista = Usuario::create([
            'id_persona' => $p2->id_persona,
            'nombre_usuario' => 'recep_test',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->recepcionista->roles()->sync([$rolRecepcion->id_rol]);

        $this->veterinario = Usuario::create([
            'id_persona' => $p3->id_persona,
            'nombre_usuario' => 'vet_test',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->sync([$rolVet->id_rol]);

        // 4. Métodos de Pago
        $this->metodoEfectivo = MetodoPago::firstOrCreate(['nombre' => 'EFECTIVO'], ['estado' => 'ACTIVO']);
        $this->metodoQR = MetodoPago::firstOrCreate(['nombre' => 'QR'], ['estado' => 'ACTIVO']);

        // 5. Especie y Raza para pruebas de paciente
        $this->especie = Especie::create(['nombre' => 'Canino']);
        $this->raza = Raza::create(['id_especie' => $this->especie->id_especie, 'nombre' => 'Labrador']);
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_caja(): void
    {
        $response = $this->actingAs($this->veterinario)->get(route('caja.index'));
        $response->assertStatus(403);
    }

    public function test_usuario_recepcion_puede_ver_listado_de_caja_y_kpis(): void
    {
        $response = $this->actingAs($this->recepcionista)->get(route('caja.index'));

        $response->assertStatus(200);
        $response->assertSee('Caja y Cobros');
        $response->assertSee('Ingresos Hoy');
        $response->assertSee('Arqueo y Cierre');
    }

    public function test_puede_ver_formulario_de_cobro(): void
    {
        $response = $this->actingAs($this->recepcionista)->get(route('caja.cobro'));

        $response->assertStatus(200);
        $response->assertSee('Registrar Cobro en Caja');
        $response->assertSee('Forma de pago elegida');
    }

    public function test_puede_procesar_cobro_exitosamente(): void
    {
        $propPersona = Persona::create([
            'nombre' => 'María', 'apellido1' => 'González', 'documento' => '778899',
            'telefono' => '71234567', 'email' => 'maria@gmail.com', 'estado' => 'ACTIVO',
        ]);
        $propietario = Propietario::create([
            'id_persona' => $propPersona->id_persona,
            'estado' => 'ACTIVO',
        ]);

        $datosCobro = [
            'id_propietario' => $propietario->id_persona,
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'monto' => 150.00,
            'descuento' => 10.00,
            'referencia' => 'REC-001',
            'observaciones' => 'Cobro de prueba en mostrador',
        ];

        $response = $this->actingAs($this->recepcionista)->post(route('caja.cobro.store'), $datosCobro);

        $response->assertRedirect(route('caja.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pagos', [
            'estado' => 'COMPLETADO',
            'observaciones' => 'Cobro de prueba en mostrador',
        ]);

        $this->assertDatabaseHas('detalle_pagos', [
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'monto' => 150.00,
            'referencia' => 'REC-001',
        ]);
    }

    public function test_puede_procesar_cobro_vinculado_a_atencion(): void
    {
        $propPersona = Persona::create([
            'nombre' => 'Luis', 'apellido1' => 'Mendoza', 'documento' => '998877',
            'telefono' => '76543210', 'email' => 'luis@gmail.com', 'estado' => 'ACTIVO',
        ]);
        $propietario = Propietario::create([
            'id_persona' => $propPersona->id_persona,
            'estado' => 'ACTIVO',
        ]);
        $paciente = Paciente::create([
            'id_propietario' => $propietario->id_persona,
            'id_especie' => $this->especie->id_especie,
            'id_raza' => $this->raza->id_raza,
            'nombre' => 'Toby',
            'sexo' => 'M',
            'estado' => 'ACTIVO',
        ]);
        $atencion = Atencion::create([
            'id_paciente' => $paciente->id_paciente,
            'fecha_hora_inicio' => now()->subHour(),
            'fecha_hora_fin' => now(),
            'estado' => 'FINALIZADA',
            'total_final' => 200.00,
        ]);

        $datosCobro = [
            'id_atencion' => $atencion->id_atencion,
            'id_metodo_pago' => $this->metodoQR->id_metodo_pago,
            'monto' => 200.00,
            'referencia' => 'QR-BCP-9912',
            'observaciones' => 'Pago por consulta y vacunas',
        ];

        $response = $this->actingAs($this->recepcionista)->post(route('caja.cobro.store'), $datosCobro);

        $response->assertRedirect(route('caja.index'));

        $this->assertDatabaseHas('pago_atencions', [
            'id_atencion' => $atencion->id_atencion,
            'monto_aplicado' => 200.00,
        ]);
    }

    public function test_puede_ver_formulario_de_cierre_de_caja(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('caja.cierre'));

        $response->assertStatus(200);
        $response->assertSee('Arqueo y Cierre de Caja');
        $response->assertSee('Efectivo y Valores Físicos Contados');
    }

    public function test_puede_procesar_cierre_de_caja(): void
    {
        // Registrar un pago sin cierre
        $pago = Pago::create([
            'fecha_hora' => now(),
            'estado' => 'COMPLETADO',
            'observaciones' => 'Pago previo a cierre',
        ]);
        DetallePago::create([
            'id_pago' => $pago->id_pago,
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'monto' => 80.00,
        ]);

        $datosCierre = [
            'total_fisico' => 80.00,
            'observaciones' => 'Cierre sin novedades',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('caja.cierre.store'), $datosCierre);

        $response->assertRedirect(route('caja.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cierre_cajas', [
            'id_usuario' => $this->superAdmin->id_usuario,
            'total_registrado' => 80.00,
        ]);

        // Verificar que el pago ahora tiene el id_cierre asignado
        $pago->refresh();
        $this->assertNotNull($pago->id_cierre);
    }

    public function test_puede_anular_un_pago_con_motivo(): void
    {
        $pago = Pago::create([
            'fecha_hora' => now(),
            'estado' => 'COMPLETADO',
            'observaciones' => 'Cobro emitido por error',
        ]);
        DetallePago::create([
            'id_pago' => $pago->id_pago,
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'monto' => 120.00,
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('caja.anular', $pago->id_pago), [
            'motivo' => 'Error en el cobro del servicio solicitado',
        ]);

        $response->assertRedirect(route('caja.index'));

        $pago->refresh();
        $this->assertEquals('ANULADO', $pago->estado);

        $this->assertDatabaseHas('devolucion_pagos', [
            'id_pago' => $pago->id_pago,
            'id_usuario' => $this->superAdmin->id_usuario,
            'monto' => 120.00,
            'motivo' => 'Error en el cobro del servicio solicitado',
        ]);
    }

    public function test_puede_ver_listado_de_deudas(): void
    {
        $propPersona = Persona::create([
            'nombre' => 'Ana', 'apellido1' => 'Suárez', 'documento' => '445566',
            'telefono' => '71122334', 'email' => 'ana@gmail.com', 'estado' => 'ACTIVO',
        ]);
        $propietario = Propietario::create([
            'id_persona' => $propPersona->id_persona,
            'estado' => 'ACTIVO',
        ]);
        Cuenta::create([
            'id_propietario' => $propietario->id_persona,
            'fecha_apertura' => now()->toDateString(),
            'estado' => 'ACTIVA',
        ]);

        $response = $this->actingAs($this->recepcionista)->get(route('caja.deudas'));

        $response->assertStatus(200);
        $response->assertSee('Cuentas y Saldos de Clientes');
        $response->assertSee('Ana Suárez');
    }

    public function test_puede_registrar_egreso_de_caja_chica(): void
    {
        $datosEgreso = [
            'monto' => 35.50,
            'concepto' => 'Compra de artículos de limpieza urgente',
            'categoria' => 'Insumos y Limpieza',
            'comprobante' => 'FAC-00123',
        ];

        $response = $this->actingAs($this->recepcionista)->post(route('caja.egresos.store'), $datosEgreso);

        $response->assertRedirect(route('caja.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('egresos_caja', [
            'id_usuario' => $this->recepcionista->id_usuario,
            'monto' => 35.50,
            'concepto' => 'Compra de artículos de limpieza urgente',
            'categoria' => 'Insumos y Limpieza',
            'comprobante' => 'FAC-00123',
            'id_cierre' => null,
        ]);
    }

    public function test_cierre_de_caja_liquida_egresos_y_calcula_saldo_neto(): void
    {
        // Registrar un pago de 100
        $pago = Pago::create([
            'fecha_hora' => now(),
            'estado' => 'COMPLETADO',
            'observaciones' => 'Ingreso de prueba',
        ]);
        DetallePago::create([
            'id_pago' => $pago->id_pago,
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'monto' => 100.00,
        ]);

        // Registrar un egreso de 30
        $egreso = EgresoCaja::create([
            'id_usuario' => $this->superAdmin->id_usuario,
            'monto' => 30.00,
            'concepto' => 'Gasto de taxi urgencia',
            'categoria' => 'Viáticos y Transporte',
            'fecha_hora' => now(),
        ]);

        // Cerrar caja reportando físico 70 (100 - 30)
        $response = $this->actingAs($this->superAdmin)->post(route('caja.cierre.store'), [
            'total_fisico' => 70.00,
            'observaciones' => 'Cierre con egresos',
        ]);

        $response->assertRedirect(route('caja.index'));

        // El total registrado debe ser 70 (100 ingresos - 30 egresos)
        $this->assertDatabaseHas('cierre_cajas', [
            'id_usuario' => $this->superAdmin->id_usuario,
            'total_registrado' => 70.00,
        ]);

        $egreso->refresh();
        $this->assertNotNull($egreso->id_cierre);
    }
}
