<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PulidoMejorasTest extends TestCase
{
    /**
     * Verifica que los archivos de favicon existan y no estén vacíos.
     */
    public function test_favicons_exist_and_are_not_empty(): void
    {
        $this->assertFileExists(public_path('favicon.svg'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.svg')));

        $this->assertFileExists(public_path('favicon.ico'));
        $this->assertGreaterThan(0, filesize(public_path('favicon.ico')));
    }

    /**
     * Verifica que el comando de rutina diaria funcione en modo simulación.
     */
    public function test_rutina_clinica_diaria_command_dry_run(): void
    {
        $exitCode = Artisan::call('veterinaria:rutina-diaria', ['--dry-run' => true]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * Verifica que el comando de respaldo de base de datos genere el dump.
     */
    public function test_database_backup_command_runs_successfully(): void
    {
        $exitCode = Artisan::call('db:backup', ['--compress' => true]);

        $this->assertSame(0, $exitCode);
    }

    /**
     * Verifica que las páginas de error personalizadas compilen y rendericen correctamente.
     */
    public function test_custom_error_views_render(): void
    {
        $view403 = view('errors.403')->render();
        $this->assertStringContainsString('403', $view403);
        $this->assertStringContainsString('Acceso Restringido', $view403);

        $view404 = view('errors.404')->render();
        $this->assertStringContainsString('404', $view404);
        $this->assertStringContainsString('Página o Registro no encontrado', $view404);

        $view419 = view('errors.419')->render();
        $this->assertStringContainsString('419', $view419);
        $this->assertStringContainsString('Sesión Expirada', $view419);

        $view500 = view('errors.500')->render();
        $this->assertStringContainsString('500', $view500);
        $this->assertStringContainsString('Error Inesperado', $view500);
    }
}
