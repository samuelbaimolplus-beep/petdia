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
        Schema::table('internaciones', function (Blueprint $table) {
            $table->string('jaula_numero', 50)->nullable();
        });

        Schema::table('seguimiento_internaciones', function (Blueprint $table) {
            $table->decimal('temperatura', 4, 1)->nullable();
            $table->integer('frecuencia_cardiaca')->nullable();
            $table->integer('frecuencia_respiratoria')->nullable();
            $table->decimal('peso', 5, 2)->nullable();
            $table->string('estado_animo', 50)->nullable();
            $table->string('alimentacion', 50)->nullable();
            $table->string('hidratacion', 50)->nullable();
            $table->string('deposiciones', 50)->nullable();
            $table->string('orina', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seguimiento_internaciones', function (Blueprint $table) {
            $table->dropColumn([
                'temperatura',
                'frecuencia_cardiaca',
                'frecuencia_respiratoria',
                'peso',
                'estado_animo',
                'alimentacion',
                'hidratacion',
                'deposiciones',
                'orina',
            ]);
        });

        Schema::table('internaciones', function (Blueprint $table) {
            $table->dropColumn('jaula_numero');
        });
    }
};
