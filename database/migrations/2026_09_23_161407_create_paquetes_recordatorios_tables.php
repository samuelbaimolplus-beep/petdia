<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paquetes', function (Blueprint $table) {
            $table->id('id_paquete');
            $table->string('nombre', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2)->nullable();
            $table->string('estado', 20)->nullable();
        });

        Schema::create('detalle_paquetes', function (Blueprint $table) {
            $table->id('id_detalle_paquete');
            $table->foreignId('id_paquete')->constrained('paquetes', 'id_paquete');
            $table->foreignId('id_servicio')->constrained('servicios', 'id_servicio');
            $table->integer('cantidad_incluida')->nullable();
            $table->unique(['id_paquete', 'id_servicio']);
        });

        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id('id_suscripcion');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente');
            $table->foreignId('id_paquete')->constrained('paquetes', 'id_paquete');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->nullable();
        });

        Schema::create('recordatorios', function (Blueprint $table) {
            $table->id('id_recordatorio');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente');
            $table->foreignId('id_servicio')->nullable()->constrained('servicios', 'id_servicio');
            $table->foreignId('id_atencion_origen')->nullable()->constrained('atenciones', 'id_atencion');
            $table->foreignId('id_cita')->nullable()->constrained('citas', 'id_cita');
            $table->string('tipo', 50)->nullable();
            $table->dateTime('fecha_hora_programada')->nullable();
            $table->dateTime('fecha_hora_envio')->nullable();
            $table->string('medio_envio', 50)->nullable();
            $table->text('mensaje')->nullable();
            $table->string('estado', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recordatorios');
        Schema::dropIfExists('suscripciones');
        Schema::dropIfExists('detalle_paquetes');
        Schema::dropIfExists('paquetes');
    }
};
