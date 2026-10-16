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
        // 1. Ampliación de auditoria_logs para auditoría completa (Sección 3)
        Schema::table('auditoria_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('auditoria_logs', 'resultado')) {
                $table->string('resultado', 30)->default('EXITO')->after('entidad_id')->index();
            }
            if (! Schema::hasColumn('auditoria_logs', 'descripcion')) {
                $table->string('descripcion', 500)->nullable()->after('resultado');
            }
        });

        // 2. Ampliación de roles para gestión de grupos activos/inactivos (Sección 4)
        Schema::table('roles', function (Blueprint $table) {
            if (! Schema::hasColumn('roles', 'estado')) {
                $table->string('estado', 20)->default('ACTIVO')->after('descripcion')->index();
            }
        });

        // 3. Ampliación de permisos para agrupación por módulo (Sección 4)
        Schema::table('permisos', function (Blueprint $table) {
            if (! Schema::hasColumn('permisos', 'modulo')) {
                $table->string('modulo', 50)->default('GENERAL')->after('descripcion')->index();
            }
            if (! Schema::hasColumn('permisos', 'codigo')) {
                $table->string('codigo', 60)->nullable()->after('modulo')->index();
            }
        });

        // 4. Tabla de controles posquirúrgicos y evolución (CU11)
        if (! Schema::hasTable('controles_cirugia')) {
            Schema::create('controles_cirugia', function (Blueprint $table) {
                $table->id('id_control');
                $table->unsignedBigInteger('id_cirugia');
                $table->foreign('id_cirugia')->references('id_procedimiento')->on('cirugias')->cascadeOnDelete();
                $table->unsignedBigInteger('id_profesional')->nullable();
                $table->foreign('id_profesional')->references('id_persona')->on('profesionales')->nullOnDelete();
                $table->dateTime('fecha_control');
                $table->string('estado_herida', 50)->nullable();
                $table->decimal('temperatura', 5, 2)->nullable();
                $table->decimal('peso', 10, 2)->nullable();
                $table->text('evolucion');
                $table->text('indicaciones')->nullable();
                $table->date('proximo_control')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 5. Tabla de observaciones clínicas y administrativas (CU34)
        if (! Schema::hasTable('observaciones_registros')) {
            Schema::create('observaciones_registros', function (Blueprint $table) {
                $table->id('id_observacion');
                $table->foreignId('id_paciente')->constrained('pacientes', 'id_paciente')->cascadeOnDelete();
                $table->foreignId('id_atencion')->nullable()->constrained('atenciones', 'id_atencion')->nullOnDelete();
                $table->foreignId('id_procedimiento')->nullable()->constrained('procedimientos', 'id_procedimiento')->nullOnDelete();
                $table->foreignId('id_usuario')->nullable()->constrained('usuarios', 'id_usuario')->nullOnDelete();
                $table->string('tipo', 20)->default('CLINICA')->index(); // 'CLINICA', 'ADMINISTRATIVA'
                $table->string('categoria', 50)->nullable();
                $table->text('observacion');
                $table->dateTime('fecha_hora')->useCurrent()->index();
                $table->timestamp('created_at')->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observaciones_registros');
        Schema::dropIfExists('controles_cirugia');

        Schema::table('permisos', function (Blueprint $table) {
            if (Schema::hasColumn('permisos', 'codigo')) {
                $table->dropColumn('codigo');
            }
            if (Schema::hasColumn('permisos', 'modulo')) {
                $table->dropColumn('modulo');
            }
        });

        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'estado')) {
                $table->dropColumn('estado');
            }
        });

        Schema::table('auditoria_logs', function (Blueprint $table) {
            if (Schema::hasColumn('auditoria_logs', 'descripcion')) {
                $table->dropColumn('descripcion');
            }
            if (Schema::hasColumn('auditoria_logs', 'resultado')) {
                $table->dropColumn('resultado');
            }
        });
    }
};
