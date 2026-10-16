<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            // Verificación de Correo Electrónico (Single-use token con expiración 24h)
            $table->timestamp('email_verified_at')->nullable()->after('estado');
            $table->string('email_verification_token', 64)->nullable()->after('email_verified_at');
            $table->timestamp('email_verification_expires_at')->nullable()->after('email_verification_token');

            // Autenticación de Dos Factores (2FA) TOTP (Google Authenticator)
            $table->text('two_factor_secret')->nullable()->after('email_verification_expires_at');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
        });

        // Marcar usuarios existentes como verificados para evitar romper accesos de producción/desarrollo existentes
        DB::table('usuarios')->whereNull('email_verified_at')->update([
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn([
                'email_verified_at',
                'email_verification_token',
                'email_verification_expires_at',
                'two_factor_secret',
                'two_factor_confirmed_at',
                'two_factor_recovery_codes',
            ]);
        });
    }
};
