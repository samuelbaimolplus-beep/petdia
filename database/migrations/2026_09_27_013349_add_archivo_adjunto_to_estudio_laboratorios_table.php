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
        Schema::table('estudio_laboratorios', function (Blueprint $table) {
            $table->string('archivo_adjunto')->nullable()->after('interpretacion');
            $table->string('nombre_archivo_original')->nullable()->after('archivo_adjunto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudio_laboratorios', function (Blueprint $table) {
            $table->dropColumn(['archivo_adjunto', 'nombre_archivo_original']);
        });
    }
};
