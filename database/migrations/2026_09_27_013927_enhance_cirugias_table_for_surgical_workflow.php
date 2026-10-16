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
        Schema::table('cirugias', function (Blueprint $table) {
            $table->string('estado', 30)->default('PROGRAMADA')->after('resultado');
            $table->string('riesgo_asa', 20)->nullable()->after('estado');
            $table->string('tipo_anestesia', 60)->nullable()->after('riesgo_asa');
            $table->unsignedSmallInteger('ayuno_solidos_horas')->nullable()->after('tipo_anestesia');
            $table->unsignedSmallInteger('ayuno_liquidos_horas')->nullable()->after('ayuno_solidos_horas');
            $table->dateTime('fecha_programada')->nullable()->after('ayuno_liquidos_horas');
            $table->unsignedBigInteger('id_cirujano')->nullable()->after('fecha_programada');
            $table->foreign('id_cirujano')->references('id_persona')->on('profesionales')->nullOnDelete();
            $table->unsignedBigInteger('id_anestesista')->nullable()->after('id_cirujano');
            $table->foreign('id_anestesista')->references('id_persona')->on('profesionales')->nullOnDelete();
            $table->boolean('autorizacion_firmada')->default(false)->after('id_anestesista');
            $table->string('firma_propietario_nombre', 150)->nullable()->after('autorizacion_firmada');
            $table->string('firma_propietario_documento', 50)->nullable()->after('firma_propietario_nombre');
            $table->text('observaciones_prequirurgicas')->nullable()->after('firma_propietario_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cirugias', function (Blueprint $table) {
            $table->dropForeign(['id_cirujano']);
            $table->dropForeign(['id_anestesista']);
            $table->dropColumn([
                'estado',
                'riesgo_asa',
                'tipo_anestesia',
                'ayuno_solidos_horas',
                'ayuno_liquidos_horas',
                'fecha_programada',
                'id_cirujano',
                'id_anestesista',
                'autorizacion_firmada',
                'firma_propietario_nombre',
                'firma_propietario_documento',
                'observaciones_prequirurgicas',
            ]);
        });
    }
};
