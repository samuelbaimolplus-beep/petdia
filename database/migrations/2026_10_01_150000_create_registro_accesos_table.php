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
        if (! Schema::hasTable('registro_accesos')) {
            Schema::create('registro_accesos', function (Blueprint $table) {
                $table->id('id_acceso');
                $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
                $table->string('nombre_usuario', 50);
                $table->string('rol', 50)->nullable();
                $table->date('fecha_ingreso');
                $table->time('hora_ingreso');
                $table->timestamp('fecha_hora_cierre')->nullable();
                $table->string('estado', 20)->default('ACTIVA'); // ACTIVA, CERRADA
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registro_accesos');
    }
};
