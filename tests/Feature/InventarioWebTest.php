<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarioWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_view_inventario_index(): void
    {
        $superAdmin = Usuario::where('nombre_usuario', 'jperez')->first();

        $response = $this->actingAs($superAdmin)
            ->get(route('inventario.index'));

        $response->assertStatus(200);
        $response->assertSee('Inventario Médico e Insumos');
        $response->assertSee('Vacuna Rabia');
        $response->assertSee('Shampoo Antipulgas');
    }

    public function test_veterinario_can_view_inventario_in_read_only_mode(): void
    {
        $vet = Usuario::where('nombre_usuario', 'sgarcia')->first();

        $response = $this->actingAs($vet)
            ->get(route('inventario.index'));

        $response->assertStatus(200);
        $response->assertSee('Solo lectura');
        $response->assertDontSee('Nuevo Producto');
    }

    public function test_recepcion_cannot_access_inventario(): void
    {
        $recepcion = Usuario::where('nombre_usuario', 'mlopez')->first();

        $response = $this->actingAs($recepcion)
            ->get(route('inventario.index'));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_medication_product(): void
    {
        $superAdmin = Usuario::where('nombre_usuario', 'jperez')->first();

        $response = $this->actingAs($superAdmin)
            ->post(route('inventario.productos.store'), [
                'nombre' => 'Enrofloxacina 50mg',
                'tipo' => 'MEDICAMENTO',
                'categoria' => 'VETERINARIA',
                'marca' => 'Bayer',
                'unidad_medida' => 'Tableta',
                'descripcion' => 'Antibiótico de amplio espectro',
                'precio_venta' => 40.00,
                'stock_minimo' => 15.00,
                'control_lotes' => 1,
                'principio_activo' => 'Enrofloxacina',
                'presentacion' => 'Comprimidos orales',
                'concentracion' => 50,
                'unidad_concentracion' => 'mg',
                'stock_inicial' => 30,
                'lote_numero' => 'ENRO-2026-01',
                'lote_vencimiento' => '2027-05-15',
            ]);

        $response->assertRedirect(route('inventario.index'));
        $this->assertDatabaseHas('productos', [
            'nombre' => 'Enrofloxacina 50mg',
            'marca' => 'Bayer',
        ]);
        $this->assertDatabaseHas('medicamentos', [
            'principio_activo' => 'Enrofloxacina',
        ]);
        $this->assertDatabaseHas('movimiento_inventarios', [
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 30,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $superAdmin = Usuario::where('nombre_usuario', 'jperez')->first();
        $producto = Producto::where('nombre', 'Vacuna Rabia')->first();

        $response = $this->actingAs($superAdmin)
            ->put(route('inventario.productos.update', $producto->id_producto), [
                'nombre' => 'Vacuna Rabia Canina Plus',
                'tipo' => 'MEDICAMENTO',
                'categoria' => 'VETERINARIA',
                'marca' => 'VetLab Premium',
                'unidad_medida' => 'Dosis',
                'descripcion' => 'Actualizada',
                'precio_venta' => 140.00,
                'stock_minimo' => 25.00,
                'control_lotes' => 1,
                'estado' => 'ACTIVO',
                'principio_activo' => 'Virus inactivado',
                'presentacion' => 'Frasco vial',
                'concentracion' => 1,
                'unidad_concentracion' => 'ml',
            ]);

        $response->assertRedirect(route('inventario.index'));
        $this->assertDatabaseHas('productos', [
            'id_producto' => $producto->id_producto,
            'nombre' => 'Vacuna Rabia Canina Plus',
            'precio_venta' => 140.00,
        ]);
    }

    public function test_can_view_movimientos_history(): void
    {
        $superAdmin = Usuario::where('nombre_usuario', 'jperez')->first();

        $response = $this->actingAs($superAdmin)
            ->get(route('inventario.movimientos'));

        $response->assertStatus(200);
        $response->assertSee('Movimientos de Stock');
        $response->assertSee('ENTRADA');
    }

    public function test_can_register_manual_stock_adjustment(): void
    {
        $superAdmin = Usuario::where('nombre_usuario', 'jperez')->first();
        $producto = Producto::first();

        $response = $this->actingAs($superAdmin)
            ->post(route('inventario.movimientos.store'), [
                'id_producto' => $producto->id_producto,
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => 12.5,
                'motivo' => 'AJUSTE',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('movimiento_inventarios', [
            'id_producto' => $producto->id_producto,
            'tipo_movimiento' => 'ENTRADA',
            'cantidad' => 12.5,
            'motivo' => 'AJUSTE',
        ]);
    }

    public function test_can_view_alertas_panel(): void
    {
        $superAdmin = Usuario::where('nombre_usuario', 'jperez')->first();

        $response = $this->actingAs($superAdmin)
            ->get(route('inventario.alertas'));

        $response->assertStatus(200);
        $response->assertSee('Alertas de Inventario');
        $response->assertSee('Productos con Stock Bajo o Agotado');
        $response->assertSee('Lotes Próximos a Vencer o Caducados');
    }
}
