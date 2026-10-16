<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup 
                            {--compress : Comprimir la copia de seguridad con formato gzip (.sql.gz)}
                            {--keep=15 : Cantidad de días para conservar respaldos anteriores}
                            {--filename= : Nombre personalizado para el archivo de respaldo}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo (dump) de la base de datos PostgreSQL con timestamp y rotación automática.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Iniciando proceso de respaldo de la base de datos...');

        $connection = config('database.default');

        if ($connection !== 'pgsql') {
            $this->components->error("El motor configurado es '{$connection}'. Este comando está optimizado para 'pgsql' (PostgreSQL).");

            return Command::FAILURE;
        }

        $dbConfig = config("database.connections.{$connection}");

        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '5432';
        $database = $dbConfig['database'] ?? 'veterinaria';
        $username = $dbConfig['username'] ?? 'postgres';
        $password = $dbConfig['password'] ?? '';

        $pgDumpBinary = $this->resolvePgDumpBinary();

        if (! $pgDumpBinary) {
            $this->components->error('No se encontró el ejecutable pg_dump en el PATH ni en las rutas estándar de PostgreSQL.');
            $this->line('  <fg=gray>Tip: Puedes definir PG_DUMP_PATH en tu archivo .env con la ruta completa a pg_dump.exe</>');

            return Command::FAILURE;
        }

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $compress = (bool) $this->option('compress');
        $customName = $this->option('filename');

        $baseName = $customName ?: "{$database}_backup_{$timestamp}";
        $extension = $compress ? '.sql.gz' : '.sql';
        $finalFilename = str_ends_with($baseName, $extension) ? $baseName : $baseName.$extension;
        $outputPath = $backupDir.DIRECTORY_SEPARATOR.$finalFilename;

        $startTime = microtime(true);

        $command = [
            $pgDumpBinary,
            '-h', $host,
            '-p', (string) $port,
            '-U', $username,
            '-F', 'p', // plain text SQL
            '-d', $database,
            '--clean',
            '--if-exists',
        ];

        $env = [
            'PGPASSWORD' => $password,
            'SYSTEMROOT' => getenv('SYSTEMROOT') ?: 'C:\Windows',
            'PATH' => getenv('PATH') ?: '',
        ];

        $process = new Process($command, null, $env, null, 300);

        if ($compress) {
            // Ejecutar y comprimir en streaming o guardar y comprimir
            $tempSqlPath = $backupDir.DIRECTORY_SEPARATOR."temp_{$timestamp}.sql";
            $command[] = '-f';
            $command[] = $tempSqlPath;
            $process = new Process($command, null, $env, null, 300);
            $process->run();

            if (! $process->isSuccessful() || ! file_exists($tempSqlPath)) {
                $this->components->error('Ocurrió un error al ejecutar pg_dump: '.$process->getErrorOutput());
                if (file_exists($tempSqlPath)) {
                    @unlink($tempSqlPath);
                }

                return Command::FAILURE;
            }

            // Comprimir a .gz
            $gzHandle = gzopen($outputPath, 'w9');
            $sqlHandle = fopen($tempSqlPath, 'r');
            while (! feof($sqlHandle)) {
                gzwrite($gzHandle, fread($sqlHandle, 1024 * 512));
            }
            fclose($sqlHandle);
            gzclose($gzHandle);
            @unlink($tempSqlPath);
        } else {
            $command[] = '-f';
            $command[] = $outputPath;
            $process = new Process($command, null, $env, null, 300);
            $process->run();

            if (! $process->isSuccessful() || ! file_exists($outputPath)) {
                $this->components->error('Ocurrió un error al ejecutar pg_dump: '.$process->getErrorOutput());
                if (file_exists($outputPath)) {
                    @unlink($outputPath);
                }

                return Command::FAILURE;
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        $fileSizeBytes = filesize($outputPath);
        $fileSizeFormatted = $this->formatBytes($fileSizeBytes);

        $this->components->info("Respaldo completado en {$elapsed}s");
        $this->table(
            ['Propiedad', 'Valor'],
            [
                ['Archivo', $finalFilename],
                ['Ubicación', $outputPath],
                ['Tamaño', $fileSizeFormatted],
                ['Base de Datos', $database],
                ['Servidor', "{$host}:{$port}"],
            ]
        );

        // Rotación de respaldos antiguos
        $keepDays = (int) $this->option('keep');
        $this->rotarRespaldos($backupDir, $keepDays);

        return Command::SUCCESS;
    }

    /**
     * Localiza el binario de pg_dump en el entorno o rutas habituales.
     */
    protected function resolvePgDumpBinary(): ?string
    {
        $envPath = env('PG_DUMP_PATH');
        if ($envPath && file_exists($envPath)) {
            return $envPath;
        }

        // Búsqueda en rutas conocidas de PostgreSQL en Windows
        $commonDirs = glob('C:\\Program Files\\PostgreSQL\\*\\bin\\pg_dump.exe') ?: [];
        if (! empty($commonDirs)) {
            rsort($commonDirs); // Versión más alta primero

            return $commonDirs[0];
        }

        $commonX86 = glob('C:\\Program Files (x86)\\PostgreSQL\\*\\bin\\pg_dump.exe') ?: [];
        if (! empty($commonX86)) {
            rsort($commonX86);

            return $commonX86[0];
        }

        // Probar si está en el PATH
        $process = new Process(['pg_dump', '--version']);
        $process->run();
        if ($process->isSuccessful()) {
            return 'pg_dump';
        }

        return null;
    }

    /**
     * Elimina archivos de respaldo con más de X días de antigüedad.
     */
    protected function rotarRespaldos(string $directory, int $dias): void
    {
        if ($dias <= 0) {
            return;
        }

        $limite = Carbon::now()->subDays($dias)->getTimestamp();
        $archivos = glob($directory.DIRECTORY_SEPARATOR.'*_backup_*.*') ?: [];
        $eliminados = 0;

        foreach ($archivos as $archivo) {
            if (is_file($archivo) && filemtime($archivo) < $limite) {
                @unlink($archivo);
                $eliminados++;
            }
        }

        if ($eliminados > 0) {
            $this->line("  <fg=gray>• Rotación:</> Se eliminaron {$eliminados} respaldo(s) con más de {$dias} días de antigüedad.");
        } else {
            $this->line("  <fg=gray>• Rotación:</> No se encontraron respaldos antiguos para depurar (política: conservar {$dias} días).");
        }
    }

    /**
     * Formatea bytes a una unidad legible (KB, MB, GB).
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2).' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
