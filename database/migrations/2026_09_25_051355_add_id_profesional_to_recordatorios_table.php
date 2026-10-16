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
        Schema::table('recordatorios', function (Blueprint $table) {
            $table->foreignId('id_profesional')
                ->nullable()
                ->after('id_cita')
                ->constrained('profesionales', 'id_persona')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recordatorios', function (Blueprint $table) {
            $table->dropForeign(['id_profesional']);
            $table->dropColumn('id_profesional');
        });
    }
};
