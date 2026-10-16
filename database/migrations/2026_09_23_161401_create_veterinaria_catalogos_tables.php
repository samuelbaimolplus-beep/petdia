<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id('id_persona');
            $table->string('documento', 50)->unique();
            $table->string('nombre', 100);
            $table->string('apellido1', 100);
            $table->string('apellido2', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 255)->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id('id_rol');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id('id_permiso');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 255)->nullable();
        });

        Schema::create('especies', function (Blueprint $table) {
            $table->id('id_especie');
            $table->string('nombre', 50)->unique();
        });

        Schema::create('servicios', function (Blueprint $table) {
            $table->id('id_servicio');
            $table->string('nombre', 100);
            $table->string('categoria', 50)->nullable();
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();
            $table->decimal('precio_base', 10, 2)->nullable();
            $table->boolean('disponible_domicilio')->default(false);
            $table->string('estado', 20)->nullable();
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('nombre', 100);
            $table->string('tipo', 50)->nullable();
            $table->string('categoria', 50)->nullable();
            $table->string('marca', 50)->nullable();
            $table->string('unidad_medida', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_venta', 10, 2)->nullable();
            $table->decimal('stock_minimo', 10, 2)->nullable();
            $table->boolean('control_lotes')->default(false);
            $table->string('estado', 20)->nullable();
        });

        Schema::create('proveedores', function (Blueprint $table) {
            $table->id('id_proveedor');
            $table->string('nombre', 100);
            $table->string('nit', 50)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('estado', 20)->nullable();
        });

        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id('id_metodo_pago');
            $table->string('nombre', 50)->unique();
            $table->string('estado', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_pago');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('especies');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('personas');
    }
};
