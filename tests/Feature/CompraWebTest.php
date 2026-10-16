<?php

namespace Tests\Feature;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\Persona;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompraWebTest extends TestCase
{
    use RefreshDatabase;

    protected Usuario $adminUser;

    protected Usuario $noAccessUser;

    protected Proveedor $proveedor;

    protected Producto $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $rolAdmin = Rol::firstOrCreate(['nombre' => 'ADMINISTRADOR'], ['descripcion' => 'Admin']);
        $rolTransp = Rol::firstOrCreate(['nombre' => 'TRANSPORTE'], ['descripcion' => 'Transporte']);

        $personaAdmin = Persona::create([
            'nombre' => 'Admin Compras',
            'apellido1' => 'Perez',
            'documento' => '654321',
            'telefono' => '71122334',
            'estado' => 'ACTIVO',
        ]);

        $this->adminUser = Usuario::create([
            'id_persona' => $personaAdmin->id_persona,
            'nombre_usuario' => 'admin_compras',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->adminUser->roles()->sync([$rolAdmin->id_rol]);

        $personaNo = Persona::create([
            'nombre' => 'Chofer',
            'apellido1' => 'Trans',
            'documento' => '998877',
            'telefono' => '78899001',
            'estado' => 'ACTIVO',
        ]);

        $this->noAccessUser = Usuario::create([
            'id_persona' => $personaNo->id_persona,
            'nombre_usuario' => 'chofer_test',
            'password_hash' => bcrypt('password123'),
            'estado' => 'ACTIVO',
        ]);
        $this->noAccessUser->roles()->sync([$rolTransp->id_rol]);

        $this->proveedor = Proveedor::create([
            'nombre' => 'Laboratorios Veterinarios Unidos',
            'nit' => '1029384756',
            'telefono' => '22446688',
            'email' => 'ventas@labunidos.com',
            'direccion' => 'Parque Industrial Mz. 4',
            'estado' => 'ACTIVO',
        ]);

        $this->producto = Producto::create([
            'nombre' => 'Amoxicilina 500mg',
            'descripcion' => 'Antibiótico inyectable',
            'tipo' => 'MEDICAMENTO',
            'categoria' => 'FARMACIA',
            'precio_venta' => 45.00,
            'costo' => 25.00,
            'stock_minimo' => 5,
            'estado' => 'ACTIVO',
        ]);
    }

    public function test_usuario_sin_permiso_no_puede_ver_compras(): void
    {
        $response = $this->actingAs($this->noAccessUser)->get(route('compras.index'));
        $response->assertStatus(403);
    }

    public function test_admin_puede_ver_listado_de_compras(): void
    {
        Compra::create([
            'id_proveedor' => $this->proveedor->id_proveedor,
            'fecha_hora' => now(),
            'estado' => 'COMPLETADA',
            'total' => 500.00,
            'observaciones' => 'Compra inicial de lote',
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('compras.index'));
        $response->assertStatus(200);
        $response->assertSee('Compras y Proveedores');
        $response->assertSee('Laboratorios Veterinarios Unidos');
        $response->assertSee('500.00');
    }

    public function test_admin_puede_cargar_formulario_crear_compra(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('compras.create'));
        $response->assertStatus(200);
        $response->assertSee('Registrar Compra a Proveedor');
        $response->assertSee($this->proveedor->nombre);
        $response->assertSee($this->producto->nombre);
    }

    public function test_admin_puede_registrar_compra_e_impactar_inventario(): void
    {
        $data = [
            'id_proveedor' => $this->proveedor->id_proveedor,
            'fecha' => now()->toDateString(),
            'observaciones' => 'Abastecimiento antibióticos',
            'items' => [
                [
                    'id_producto' => $this->producto->id_producto,
                    'cantidad' => 20,
                    'precio_unitario' => 22.50,
                    'lote' => 'LOT-AMX-2026',
                    'fecha_vencimiento' => now()->addMonths(12)->toDateString(),
                ],
            ],
        ];

        $response = $this->actingAs($this->adminUser)->post(route('compras.store'), $data);
        $response->assertRedirect(route('compras.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('compras', [
            'id_proveedor' => $this->proveedor->id_proveedor,
            'total' => 450.00,
            'estado' => 'COMPLETADA',
        ]);

        $this->assertDatabaseHas('detalle_compras', [
            'id_producto' => $this->producto->id_producto,
            'cantidad' => 20,
            'precio_unitario' => 22.50,
        ]);

        $this->assertDatabaseHas('lotes', [
            'numero_lote' => 'LOT-AMX-2026',
            'id_producto' => $this->producto->id_producto,
            'estado' => 'ACTIVO',
        ]);

        $this->assertDatabaseHas('movimiento_inventarios', [
            'id_producto' => $this->producto->id_producto,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 20,
            'motivo' => 'COMPRA_PROVEEDOR',
        ]);
    }

    public function test_admin_puede_ver_detalle_de_compra(): void
    {
        $compra = Compra::create([
            'id_proveedor' => $this->proveedor->id_proveedor,
            'fecha_hora' => now(),
            'estado' => 'COMPLETADA',
            'total' => 150.00,
        ]);

        DetalleCompra::create([
            'id_compra' => $compra->id_compra,
            'id_producto' => $this->producto->id_producto,
            'cantidad' => 6,
            'precio_unitario' => 25.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('compras.show', $compra));
        $response->assertStatus(200);
        $response->assertSee('Orden de Compra #'.$compra->id_compra);
        $response->assertSee($this->producto->nombre);
        $response->assertSee('150.00');
    }

    public function test_admin_puede_gestionar_proveedores(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('compras.proveedores'));
        $response->assertStatus(200);
        $response->assertSee('Directorio de Proveedores');
        $response->assertSee($this->proveedor->nombre);

        // Crear nuevo proveedor
        $newProvData = [
            'nombre' => 'Distribuidora Pet Pharma',
            'nit' => '99887766',
            'telefono' => '77665544',
            'email' => 'pharma@pet.com',
            'direccion' => 'Calle Los Tajibos 123',
        ];

        $postResp = $this->actingAs($this->adminUser)->post(route('compras.proveedores.store'), $newProvData);
        $postResp->assertRedirect(route('compras.proveedores'));
        $this->assertDatabaseHas('proveedores', ['nombre' => 'Distribuidora Pet Pharma']);

        // Editar proveedor
        $putResp = $this->actingAs($this->adminUser)->put(route('compras.proveedores.update', $this->proveedor), [
            'nombre' => 'Laboratorios Veterinarios Unidos S.R.L.',
            'nit' => '1029384756',
            'telefono' => '22449999',
            'email' => 'gerencia@labunidos.com',
            'direccion' => 'Parque Industrial Mz. 4 B',
            'estado' => 'ACTIVO',
        ]);
        $putResp->assertRedirect(route('compras.proveedores'));
        $this->assertDatabaseHas('proveedores', [
            'id_proveedor' => $this->proveedor->id_proveedor,
            'nombre' => 'Laboratorios Veterinarios Unidos S.R.L.',
        ]);
    }
}
