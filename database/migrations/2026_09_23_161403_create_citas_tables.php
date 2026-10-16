<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id('id_cita');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente');
            $table->foreignId('id_profesional')->nullable()->constrained('profesionales', 'id_persona');
            $table->dateTime('fecha_hora_inicio')->nullable();
            $table->dateTime('fecha_hora_fin')->nullable();
            $table->string('estado', 20)->nullable();
            $table->string('motivo_cancelacion', 255)->nullable();
            $table->string('origen', 50)->nullable();
        });

        Schema::create('detalle_citas', function (Blueprint $table) {
            $table->id('id_detalle_cita');
            $table->foreignId('id_cita')->constrained('citas', 'id_cita');
            $table->foreignId('id_servicio')->constrained('servicios', 'id_servicio');
            $table->decimal('costo_estimado', 10, 2)->nullable();
            $table->integer('tiempo_estimado_minutos')->nullable();
            $table->unique(['id_cita', 'id_servicio']);
        });

        Schema::create('servicio_domicilios', function (Blueprint $table) {
            $table->id('id_servicio_domicilio');
            $table->foreignId('id_cita')->unique()->constrained('citas', 'id_cita');
            $table->string('direccion', 255)->nullable();
            $table->string('referencia', 255)->nullable();
            $table->decimal('distancia_estimada', 10, 2)->nullable();
            $table->decimal('costo_domicilio', 10, 2)->nullable();
            $table->string('estado', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_domicilios');
        Schema::dropIfExists('detalle_citas');
        Schema::dropIfExists('citas');
    }
};
