<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuentas', function (Blueprint $table) {
            $table->id('id_cuenta');
            $table->foreignId('id_propietario')->unique()->constrained('propietarios', 'id_persona');
            $table->date('fecha_apertura')->nullable();
            $table->string('estado', 20)->nullable();
        });

        Schema::create('cargos', function (Blueprint $table) {
            $table->id('id_cargo');
            $table->foreignId('id_cuenta')->constrained('cuentas', 'id_cuenta');
            $table->foreignId('id_tratamiento')->nullable()->constrained('tratamientos', 'id_tratamiento');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('concepto', 255)->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->decimal('descuento', 10, 2)->default(0);
            $table->string('estado', 20)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('cierre_cajas', function (Blueprint $table) {
            $table->id('id_cierre');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->dateTime('fecha_hora_apertura')->nullable();
            $table->dateTime('fecha_hora_cierre')->nullable();
            $table->decimal('total_registrado', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->foreignId('id_cuenta')->nullable()->constrained('cuentas', 'id_cuenta');
            $table->foreignId('id_cierre')->nullable()->constrained('cierre_cajas', 'id_cierre');
            $table->dateTime('fecha_hora')->nullable();
            $table->string('estado', 20)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('detalle_pagos', function (Blueprint $table) {
            $table->id('id_detalle_pago');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago');
            $table->foreignId('id_metodo_pago')->constrained('metodos_pago', 'id_metodo_pago');
            $table->decimal('monto', 10, 2)->nullable();
            $table->string('referencia', 100)->nullable();
        });

        Schema::create('aplicacion_pagos', function (Blueprint $table) {
            $table->id('id_aplicacion');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago');
            $table->foreignId('id_cargo')->constrained('cargos', 'id_cargo');
            $table->decimal('monto_aplicado', 10, 2)->nullable();
        });

        Schema::create('pago_atencions', function (Blueprint $table) {
            $table->id('id_pago_atencion');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago');
            $table->foreignId('id_atencion')->constrained('atenciones', 'id_atencion');
            $table->decimal('monto_aplicado', 10, 2)->nullable();
        });

        Schema::create('devolucion_pagos', function (Blueprint $table) {
            $table->id('id_devolucion');
            $table->foreignId('id_pago')->constrained('pagos', 'id_pago');
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->dateTime('fecha_hora')->nullable();
            $table->decimal('monto', 10, 2)->nullable();
            $table->text('motivo')->nullable();
            $table->string('estado', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devolucion_pagos');
        Schema::dropIfExists('pago_atencions');
        Schema::dropIfExists('aplicacion_pagos');
        Schema::dropIfExists('detalle_pagos');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('cierre_cajas');
        Schema::dropIfExists('cargos');
        Schema::dropIfExists('cuentas');
    }
};
