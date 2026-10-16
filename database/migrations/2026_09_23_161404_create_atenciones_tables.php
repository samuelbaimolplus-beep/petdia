<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atenciones', function (Blueprint $table) {
            $table->id('id_atencion');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente');
            $table->foreignId('id_cita')->nullable()->constrained('citas', 'id_cita');
            $table->dateTime('fecha_hora_inicio')->nullable();
            $table->dateTime('fecha_hora_fin')->nullable();
            $table->string('estado', 20)->nullable();
            $table->text('observaciones')->nullable();
            $table->text('recomendaciones')->nullable();
            $table->decimal('total_final', 10, 2)->nullable();
        });

        Schema::create('atencion_veterinarias', function (Blueprint $table) {
            $table->unsignedBigInteger('id_atencion');
            $table->primary('id_atencion');
            $table->foreign('id_atencion')->references('id_atencion')->on('atenciones');
            $table->text('motivo_consulta')->nullable();
            $table->string('tipo', 50)->nullable();
            $table->text('anamnesis')->nullable();
            $table->text('sintomas')->nullable();
            $table->text('examen_fisico')->nullable();
            $table->text('conclusion')->nullable();
            $table->dateTime('fecha_reconsulta')->nullable();
            $table->text('observaciones_clinicas')->nullable();
        });

        Schema::create('atencion_esteticas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_atencion');
            $table->primary('id_atencion');
            $table->foreign('id_atencion')->references('id_atencion')->on('atenciones');
            $table->string('modalidad', 50)->nullable();
            $table->string('estado_pelaje', 50)->nullable();
            $table->text('caracteristicas_observadas')->nullable();
        });

        Schema::create('participacion_atencions', function (Blueprint $table) {
            $table->id('id_participacion');
            $table->foreignId('id_atencion')->constrained('atenciones', 'id_atencion');
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_persona');
            $table->string('rol_atencion', 50)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('atencion_servicios', function (Blueprint $table) {
            $table->id('id_atencion_servicio');
            $table->foreignId('id_atencion')->constrained('atenciones', 'id_atencion');
            $table->foreignId('id_servicio')->constrained('servicios', 'id_servicio');
            $table->integer('cantidad')->nullable();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->decimal('descuento', 10, 2)->nullable();
        });

        Schema::create('ficha_medicas', function (Blueprint $table) {
            $table->id('id_ficha');
            $table->foreignId('id_paciente')->unique()->constrained('pacientes', 'id_paciente');
            $table->date('fecha_apertura')->nullable();
            $table->text('antecedentes')->nullable();
            $table->text('alergias')->nullable();
            $table->text('enfermedades_previas')->nullable();
            $table->text('cirugias_previas')->nullable();
            $table->text('medicamentos_habituales')->nullable();
            $table->text('observaciones')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ficha_medicas');
        Schema::dropIfExists('atencion_servicios');
        Schema::dropIfExists('participacion_atencions');
        Schema::dropIfExists('atencion_esteticas');
        Schema::dropIfExists('atencion_veterinarias');
        Schema::dropIfExists('atenciones');
    }
};
