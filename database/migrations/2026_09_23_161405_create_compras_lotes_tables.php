<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->foreignId('id_proveedor')->constrained('proveedores', 'id_proveedor');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('estado', 20)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('detalle_compras', function (Blueprint $table) {
            $table->id('id_detalle_compra');
            $table->foreignId('id_compra')->constrained('compras', 'id_compra');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
        });

        Schema::create('lotes', function (Blueprint $table) {
            $table->id('id_lote');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->foreignId('id_detalle_compra')->nullable()->constrained('detalle_compras', 'id_detalle_compra');
            $table->string('numero_lote', 50)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('estado', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
        Schema::dropIfExists('detalle_compras');
        Schema::dropIfExists('compras');
    }
};
