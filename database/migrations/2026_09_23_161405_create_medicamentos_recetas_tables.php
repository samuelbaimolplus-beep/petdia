<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_producto');
            $table->primary('id_producto');
            $table->foreign('id_producto')->references('id_producto')->on('productos');
            $table->string('principio_activo', 100)->nullable();
            $table->string('presentacion', 50)->nullable();
            $table->decimal('concentracion', 10, 2)->nullable();
            $table->string('unidad_concentracion', 20)->nullable();
        });

        Schema::create('uso_producto_atencions', function (Blueprint $table) {
            $table->id('id_uso');
            $table->foreignId('id_atencion')->constrained('atenciones', 'id_atencion');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto');
            $table->unsignedBigInteger('id_lote')->nullable();
            $table->decimal('cantidad', 10, 2)->nullable();
            $table->text('observaciones')->nullable();
        });

        Schema::create('administracion_medicamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('id_uso');
            $table->primary('id_uso');
            $table->foreign('id_uso')->references('id_uso')->on('uso_producto_atencions');
            $table->dateTime('fecha_hora')->nullable();
            $table->decimal('dosis', 10, 2)->nullable();
            $table->string('unidad_dosis', 20)->nullable();
            $table->string('via_administracion', 50)->nullable();
            $table->text('observaciones_clinicas')->nullable();
        });

        Schema::create('vacunaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('id_procedimiento');
            $table->primary('id_procedimiento');
            $table->foreign('id_procedimiento')->references('id_procedimiento')->on('procedimientos');
            $table->foreignId('id_uso')->unique()->constrained('administracion_medicamentos', 'id_uso');
            $table->date('proxima_dosis')->nullable();
            $table->string('sitio_aplicacion', 100)->nullable();
        });

        Schema::create('desparasitaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('id_procedimiento');
            $table->primary('id_procedimiento');
            $table->foreign('id_procedimiento')->references('id_procedimiento')->on('procedimientos');
            $table->foreignId('id_uso')->unique()->constrained('administracion_medicamentos', 'id_uso');
            $table->string('tipo', 50)->nullable();
            $table->date('proxima_dosis')->nullable();
        });

        Schema::create('estudio_laboratorios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_procedimiento');
            $table->primary('id_procedimiento');
            $table->foreign('id_procedimiento')->references('id_procedimiento')->on('procedimientos');
            $table->string('tipo_estudio', 50)->nullable();
            $table->dateTime('fecha_resultado')->nullable();
            $table->text('resultado')->nullable();
            $table->text('interpretacion')->nullable();
        });

        Schema::create('recetas', function (Blueprint $table) {
            $table->id('id_receta');
            $table->foreignId('id_atencion')->constrained('atencion_veterinarias', 'id_atencion');
            $table->dateTime('fecha_hora')->nullable();
            $table->text('indicaciones_generales')->nullable();
        });

        Schema::create('detalle_recetas', function (Blueprint $table) {
            $table->id('id_detalle_receta');
            $table->foreignId('id_receta')->constrained('recetas', 'id_receta');
            $table->foreignId('id_producto')->constrained('medicamentos', 'id_producto');
            $table->decimal('dosis', 10, 2)->nullable();
            $table->string('unidad_dosis', 20)->nullable();
            $table->string('frecuencia', 50)->nullable();
            $table->string('duracion', 50)->nullable();
            $table->string('via_administracion', 50)->nullable();
            $table->text('indicaciones')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_recetas');
        Schema::dropIfExists('recetas');
        Schema::dropIfExists('estudio_laboratorios');
        Schema::dropIfExists('desparasitaciones');
        Schema::dropIfExists('vacunaciones');
        Schema::dropIfExists('administracion_medicamentos');
        Schema::dropIfExists('uso_producto_atencions');
        Schema::dropIfExists('medicamentos');
    }
};
