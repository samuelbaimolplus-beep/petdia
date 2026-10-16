<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propietarios', function (Blueprint $table) {
            $table->unsignedBigInteger('id_persona');
            $table->primary('id_persona');
            $table->foreign('id_persona')->references('id_persona')->on('personas');
        });

        Schema::create('profesionales', function (Blueprint $table) {
            $table->unsignedBigInteger('id_persona');
            $table->primary('id_persona');
            $table->foreign('id_persona')->references('id_persona')->on('personas');
            $table->string('especialidad', 100)->nullable();
            $table->string('registro_profesional', 50)->nullable()->unique();
            $table->string('estado', 20)->nullable();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            $table->foreignId('id_persona')->unique()->constrained('personas', 'id_persona');
            $table->string('nombre_usuario', 50)->unique();
            $table->string('password_hash', 255);
            $table->string('estado', 20)->nullable();
            $table->dateTime('ultimo_acceso')->nullable();
        });

        Schema::create('rol_permiso', function (Blueprint $table) {
            $table->foreignId('id_rol')->constrained('roles', 'id_rol');
            $table->foreignId('id_permiso')->constrained('permisos', 'id_permiso');
            $table->primary(['id_rol', 'id_permiso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permiso');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('profesionales');
        Schema::dropIfExists('propietarios');
    }
};
