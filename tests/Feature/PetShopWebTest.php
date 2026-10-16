<?php

namespace Tests\Feature;

use App\Models\MetodoPago;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Propietario;
use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetShopWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $superAdmin;

    protected Usuario $recepcionista;

    protected Usuario $veterinario;

    protected MetodoPago $metodoEfectivo;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $rolSuperAdmin = Rol::firstOrCreate(['nombre' => 'SUPER_ADMIN'], ['descripcion' => 'Super Administrador']);
        $rolRecepcion = Rol::firstOrCreate(['nombre' => 'RECEPCION'], ['descripcion' => 'Recepción']);
        $rolVet = Rol::firstOrCreate(['nombre' => 'VETERINARIO'], ['descripcion' => 'Veterinario']);

        // 2. Personas
        $p1 = Persona::create(['nombre' => 'Admin', 'apellido1' => 'Sistema', 'documento' => '1001', 'email' => 'admin@petdiatria.com', 'estado' => 'ACTIVO']);
        $p2 = Persona::create(['nombre' => 'Recep', 'apellido1' => 'Frontdesk', 'documento' => '1002', 'email' => 'recep@petdiatria.com', 'estado' => 'ACTIVO']);
        $p3 = Persona::create(['nombre' => 'Vet', 'apellido1' => 'Médico', 'documento' => '1003', 'email' => 'vet@petdiatria.com', 'estado' => 'ACTIVO']);

        // 3. Usuarios
        $this->superAdmin = Usuario::create([
            'id_persona' => $p1->id_persona,
            'nombre_usuario' => 'admin_petshop',
            'password_hash' => bcrypt('secret'),
            'estado' => 'ACTIVO',
        ]);
        $this->superAdmin->roles()->sync([$rolSuperAdmin->id_rol]);

        $this->recepcionista = Usuario::create([
            'id_persona' => $p2->id_persona,
            'nombre_usuario' => 'recep_petshop',
            'password_hash' => bcrypt('secret'),
            'estado' => 'ACTIVO',
        ]);
        $this->recepcionista->roles()->sync([$rolRecepcion->id_rol]);

        $this->veterinario = Usuario::create([
            'id_persona' => $p3->id_persona,
            'nombre_usuario' => 'vet_petshop',
            'password_hash' => bcrypt('secret'),
            'estado' => 'ACTIVO',
        ]);
        $this->veterinario->roles()->sync([$rolVet->id_rol]);

        // 4. Método de pago
        $this->metodoEfectivo = MetodoPago::firstOrCreate(['nombre' => 'EFECTIVO'], ['estado' => 'ACTIVO']);
    }

    public function test_usuario_sin_permiso_no_puede_acceder_a_petshop(): void
    {
        $response = $this->actingAs($this->veterinario)->get(route('petshop.index'));
        $response->assertStatus(403);
    }

    public function test_usuario_recepcion_puede_ver_catalogo_y_kpis(): void
    {
        $response = $this->actingAs($this->recepcionista)->get(route('petshop.index'));

        $response->assertStatus(200);
        $response->assertSee('Pet Shop');
        $response->assertSee('Total Productos');
        $response->assertSee('Ventas Hoy');
    }

    public function test_puede_ver_formulario_de_creacion_de_producto(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('petshop.create'));

        $response->assertStatus(200);
        $response->assertSee('Registrar Producto de Pet Shop');
        $response->assertSee('Precio Venta');
    }

    public function test_puede_crear_producto_exitosamente_con_stock_inicial(): void
    {
        $datos = [
            'nombre' => 'Correa Reforzada 2m',
            'categoria' => 'Accesorios',
            'marca' => 'Flexi',
            'unidad_medida' => 'UNIDAD',
            'precio_venta' => 45.00,
            'stock_minimo' => 3,
            'stock_inicial' => 10,
            'descripcion' => 'Correa resistente para razas medianas y grandes',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('petshop.store'), $datos);

        $response->assertRedirect(route('petshop.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('productos', [
            'nombre' => 'Correa Reforzada 2m',
            'categoria' => 'Accesorios',
            'precio_venta' => 45.00,
        ]);

        $producto = Producto::where('nombre', 'Correa Reforzada 2m')->first();
        $this->assertDatabaseHas('movimiento_inventarios', [
            'id_producto' => $producto->id_producto,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 10,
            'motivo' => 'INVENTARIO_INICIAL',
        ]);
    }

    public function test_puede_ver_formulario_de_edicion(): void
    {
        $producto = Producto::create([
            'nombre' => 'Hueso de Juguete',
            'categoria' => 'Juguetes',
            'precio_venta' => 15.00,
            'stock_minimo' => 2,
            'estado' => 'ACTIVO',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('petshop.edit', $producto->id_producto));

        $response->assertStatus(200);
        $response->assertSee('Editar: Hueso de Juguete');
    }

    public function test_puede_actualizar_producto(): void
    {
        $producto = Producto::create([
            'nombre' => 'Pelota de Goma',
            'categoria' => 'Juguetes',
            'precio_venta' => 12.00,
            'stock_minimo' => 4,
            'estado' => 'ACTIVO',
        ]);

        $datosActualizados = [
            'nombre' => 'Pelota de Goma Flotante',
            'categoria' => 'Juguetes',
            'marca' => 'Kong',
            'unidad_medida' => 'UNIDAD',
            'precio_venta' => 18.50,
            'stock_minimo' => 5,
            'descripcion' => 'Nueva versión con textura',
            'estado' => 'ACTIVO',
        ];

        $response = $this->actingAs($this->superAdmin)->put(route('petshop.update', $producto->id_producto), $datosActualizados);

        $response->assertRedirect(route('petshop.index'));
        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
            'nombre' => 'Pelota de Goma Flotante',
            'precio_venta' => 18.50,
        ]);
    }

    public function test_puede_procesar_venta_desde_carrito_con_salida_de_stock_y_cobro_en_caja(): void
    {
        $producto = Producto::create([
            'nombre' => 'Shampoo Aloe Vera 250ml',
            'categoria' => 'Higiene',
            'precio_venta' => 30.00,
            'stock_minimo' => 2,
            'estado' => 'ACTIVO',
        ]);

        $propPersona = Persona::create(['nombre' => 'Pedro', 'apellido1' => 'Alarcón', 'documento' => '889900']);
        $propietario = Propietario::create(['id_persona' => $propPersona->id_persona]);

        $payloadVenta = [
            'id_propietario' => $propietario->id_persona,
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'items' => [
                [
                    'id_producto' => $producto->id_producto,
                    'cantidad' => 2,
                    'precio_unitario' => 30.00,
                    'descuento' => 5.00,
                ],
            ],
            'descuento_general' => 0,
            'observaciones' => 'Venta rápida de prueba',
        ];

        $response = $this->actingAs($this->recepcionista)->post(route('petshop.venta.store'), $payloadVenta);

        $response->assertRedirect(route('petshop.index'));
        $response->assertSessionHas('success');

        // Verificar Venta
        $this->assertDatabaseHas('ventas', [
            'id_propietario' => $propietario->id_persona,
            'total' => 55.00, // (2 * 30) - 5
            'estado' => 'COMPLETADA',
        ]);

        // Verificar Detalle de Venta
        $this->assertDatabaseHas('detalle_ventas', [
            'id_producto' => $producto->id_producto,
            'cantidad' => 2,
            'precio_unitario' => 30.00,
            'descuento' => 5.00,
        ]);

        // Verificar Salida de Inventario
        $this->assertDatabaseHas('movimiento_inventarios', [
            'id_producto' => $producto->id_producto,
            'tipo_movimiento' => 'SALIDA',
            'cantidad' => -2,
            'motivo' => 'VENTA_PETSHOP',
        ]);

        // Verificar Cobro en Caja
        $this->assertDatabaseHas('pagos', [
            'estado' => 'COMPLETADO',
        ]);

        $this->assertDatabaseHas('detalle_pagos', [
            'id_metodo_pago' => $this->metodoEfectivo->id_metodo_pago,
            'monto' => 55.00,
        ]);

        $this->assertDatabaseHas('pago_ventas', [
            'monto_aplicado' => 55.00,
        ]);
    }

    public function test_puede_ver_historial_de_ventas(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('petshop.ventas'));

        $response->assertStatus(200);
        $response->assertSee('Historial de Ventas');
        $response->assertSee('Ventas y Facturación de Pet Shop');
    }

    public function test_puede_buscar_producto_por_codigo_de_barras(): void
    {
        $prod = Producto::create([
            'nombre' => 'Collar Antipulgas EAN Test',
            'categoria' => 'Farmacia',
            'precio_venta' => 45.00,
            'estado' => 'ACTIVO',
            'codigo_barra' => '7791234567890',
        ]);

        $response = $this->actingAs($this->recepcionista)
            ->getJson(route('petshop.buscar-codigo', ['codigo' => '7791234567890']));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('producto.id_producto', $prod->id_producto)
            ->assertJsonPath('producto.nombre', 'Collar Antipulgas EAN Test')
            ->assertJsonPath('producto.codigo_barra', '7791234567890');
    }

    public function test_busqueda_codigo_de_barras_inexistente_retorna_404(): void
    {
        $response = $this->actingAs($this->recepcionista)
            ->getJson(route('petshop.buscar-codigo', ['codigo' => 'CODIGO_INEXISTENTE_999']));

        $response->assertNotFound()
            ->assertJsonPath('success', false);
    }

    public function test_puede_crear_producto_con_codigo_de_barras(): void
    {
        $datos = [
            'nombre' => 'Shampoo Canino Coco EAN',
            'categoria' => 'Higiene',
            'precio_venta' => 18.50,
            'codigo_barra' => '1234567890123',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('petshop.store'), $datos);

        $response->assertRedirect(route('petshop.index'));
        $this->assertDatabaseHas('productos', [
            'nombre' => 'Shampoo Canino Coco EAN',
            'codigo_barra' => '1234567890123',
        ]);
    }
}
