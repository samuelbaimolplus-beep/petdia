<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registro_signos_vitales', function (Blueprint $table) {
            $table->id('id_registro_signos');
            $table->foreignId('id_atencion')->constrained('atencion_veterinarias', 'id_atencion');
            $table->dateTime('fecha_hora')->nullable();
            $table->decimal('peso', 10, 2)->nullable();
            $table->decimal('temperatura', 5, 2)->nullable();
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();
        });

        Schema::create('diagnosticos', function (Blueprint $table) {
            $table->id('id_diagnostico');
            $table->foreignId('id_atencion')->constrained('atencion_veterinarias', 'id_atencion');
            $table->string('nombre', 100)->nullable();
            $table->text('descripcion')->nullable();
            $table->string('tipo', 50)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id('id_tratamiento');
            $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente');
            $table->text('descripcion')->nullable();
            $table->text('indicaciones')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            $table->string('estado', 20)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('atencion_tratamiento', function (Blueprint $table) {
            $table->foreignId('id_atencion')->constrained('atencion_veterinarias', 'id_atencion');
            $table->foreignId('id_tratamiento')->constrained('tratamientos', 'id_tratamiento');
            $table->string('tipo_relacion', 20)->nullable();
            $table->primary(['id_atencion', 'id_tratamiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atencion_tratamiento');
        Schema::dropIfExists('tratamientos');
        Schema::dropIfExists('diagnosticos');
        Schema::dropIfExists('registro_signos_vitales');
    }
};
