<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('egresos_caja', function (Blueprint $table) {
            $table->id('id_egreso');
            $table->foreignId('id_cierre')->nullable()->constrained('cierre_cajas', 'id_cierre')->nullOnDelete();
            $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
            $table->decimal('monto', 10, 2);
            $table->string('concepto', 255);
            $table->string('categoria', 50)->default('GENERAL');
            $table->string('comprobante', 100)->nullable();
            $table->dateTime('fecha_hora');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('egresos_caja');
    }
};
