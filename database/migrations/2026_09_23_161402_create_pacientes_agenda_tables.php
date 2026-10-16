<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('razas', function (Blueprint $table) {
            $table->id('id_raza');
            $table->foreignId('id_especie')->constrained('especies', 'id_especie');
            $table->string('nombre', 50);
        });

        Schema::create('pacientes', function (Blueprint $table) {
            $table->id('id_paciente');
            $table->foreignId('id_raza')->constrained('razas', 'id_raza');
            $table->foreignId('id_propietario')->nullable()->constrained('propietarios', 'id_persona');
            $table->string('nombre', 100);
            $table->string('sexo', 10)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('tamanio', 20)->nullable();
            $table->string('condicion_reproductiva', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('microchip', 50)->nullable()->unique();
            $table->date('fecha_implantacion')->nullable();
            $table->string('estado', 20)->nullable();
            $table->text('caract_particulares')->nullable();
            $table->string('tipo_pelaje', 50)->nullable();
            $table->text('comportamiento_normal')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->boolean('autorizacion_redes')->default(false);
        });

        Schema::create('tiempos_estimados', function (Blueprint $table) {
            $table->id('id_tiempo_estimado');
            $table->foreignId('id_servicio')->constrained('servicios', 'id_servicio');
            $table->foreignId('id_especie')->constrained('especies', 'id_especie');
            $table->decimal('peso_min', 10, 2)->nullable();
            $table->decimal('peso_max', 10, 2)->nullable();
            $table->integer('duracion_minutos')->nullable();
        });

        Schema::create('ajustes_tiempo_razas', function (Blueprint $table) {
            $table->id('id_ajuste');
            $table->foreignId('id_tiempo_estimado')->constrained('tiempos_estimados', 'id_tiempo_estimado');
            $table->foreignId('id_raza')->constrained('razas', 'id_raza');
            $table->integer('duracion_minutos')->nullable();
        });

        Schema::create('profesional_servicio', function (Blueprint $table) {
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_persona');
            $table->foreignId('id_servicio')->constrained('servicios', 'id_servicio');
            $table->string('estado', 20)->nullable();
            $table->primary(['id_profesional', 'id_servicio']);
        });

        Schema::create('horario_profesionales', function (Blueprint $table) {
            $table->id('id_horario');
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_persona');
            $table->integer('dia_semana')->nullable();
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('estado', 20)->nullable();
        });

        Schema::create('bloqueo_agendas', function (Blueprint $table) {
            $table->id('id_bloqueo');
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_persona');
            $table->dateTime('fecha_hora_inicio')->nullable();
            $table->dateTime('fecha_hora_fin')->nullable();
            $table->string('motivo', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueo_agendas');
        Schema::dropIfExists('horario_profesionales');
        Schema::dropIfExists('profesional_servicio');
        Schema::dropIfExists('ajustes_tiempo_razas');
        Schema::dropIfExists('tiempos_estimados');
        Schema::dropIfExists('pacientes');
        Schema::dropIfExists('razas');
    }
};
