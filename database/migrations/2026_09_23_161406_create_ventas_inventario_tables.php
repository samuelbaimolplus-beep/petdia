<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->foreignId('id_propietario')->nullable()->constrained('propietarios', 'id_persona');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('estado', 20)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id('id_detalle_venta');
            $table->foreignId('id_venta')->constrained('ventas', 'id_venta');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->decimal('descuento', 10, 2)->default(0);
        });

        Schema::create('pago_ventas', function (Blueprint $table) {
            $table->id('id_pago_venta');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago');
            $table->foreignId('id_venta')->constrained('ventas', 'id_venta');
            $table->decimal('monto_aplicado', 10, 2)->nullable();
        });

        Schema::create('movimiento_inventarios', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->foreignId('id_lote')->nullable()->constrained('lotes', 'id_lote');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->foreignId('id_detalle_compra')->nullable()->constrained('detalle_compras', 'id_detalle_compra');
            $table->foreignId('id_uso')->nullable()->constrained('uso_producto_atencions', 'id_uso');
            $table->foreignId('id_detalle_venta')->nullable()->constrained('detalle_ventas', 'id_detalle_venta');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('tipo_movimiento', 20)->nullable();
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->string('motivo', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimiento_inventarios');
        Schema::dropIfExists('pago_ventas');
        Schema::dropIfExists('detalle_ventas');
        Schema::dropIfExists('ventas');
    }
};
