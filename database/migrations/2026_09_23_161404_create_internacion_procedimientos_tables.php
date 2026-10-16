<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internaciones', function (Blueprint $table) {
            $table->id('id_internacion');
            $table->foreignId('id_atencion_ingreso')->unique()->constrained('atencion_veterinarias', 'id_atencion');
            $table->dateTime('fecha_hora_ingreso')->nullable();
            $table->dateTime('fecha_hora_alta')->nullable();
            $table->text('motivo')->nullable();
            $table->string('estado', 20)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('seguimiento_internaciones', function (Blueprint $table) {
            $table->id('id_seguimiento');
            $table->foreignId('id_internacion')->constrained('internaciones', 'id_internacion');
            $table->foreignId('id_profesional')->constrained('profesionales', 'id_persona');
            $table->dateTime('fecha_hora')->nullable();
            $table->text('cuidados')->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('procedimientos', function (Blueprint $table) {
            $table->id('id_procedimiento');
            $table->foreignId('id_atencion')->constrained('atencion_veterinarias', 'id_atencion');
            $table->dateTime('fecha_hora')->nullable();
            $table->text('descripcion')->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('cirugias', function (Blueprint $table) {
            $table->unsignedBigInteger('id_procedimiento');
            $table->primary('id_procedimiento');
            $table->foreign('id_procedimiento')->references('id_procedimiento')->on('procedimientos');
            $table->string('tipo_cirugia', 50)->nullable();
            $table->text('diagnostico_preoperatorio')->nullable();
            $table->text('complicaciones')->nullable();
            $table->text('indicaciones_postoperatorias')->nullable();
            $table->string('resultado', 50)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cirugias');
        Schema::dropIfExists('procedimientos');
        Schema::dropIfExists('seguimiento_internaciones');
        Schema::dropIfExists('internaciones');
    }
};
