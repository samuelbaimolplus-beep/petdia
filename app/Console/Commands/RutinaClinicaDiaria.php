<?php

namespace App\Console\Commands;

use App\Mail\RecordatorioMailable;
use App\Models\Cita;
use App\Models\Configuracion;
use App\Models\Producto;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RutinaClinicaDiaria extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'veterinaria:rutina-diaria {--dry-run : Ejecutar en modo simulado sin guardar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta las rutinas diarias de la clínica: actualiza citas vencidas a NO_ASISTIO, procesa recordatorios y verifica alertas de inventario.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        $this->components->info('Iniciando rutina clínica diaria de La Petdiatría'.($isDryRun ? ' [MODO SIMULACIÓN --dry-run]' : ''));

        $this->procesarCitasVencidas($isDryRun);
        $this->procesarRecordatorios($isDryRun);
        $this->verificarStockMinimo();

        $this->components->info('Rutina clínica diaria finalizada con éxito.');

        return Command::SUCCESS;
    }

    /**
     * Actualiza citas anteriores a hoy que quedaron en estado PROGRAMADA/AGENDADA.
     */
    protected function procesarCitasVencidas(bool $isDryRun): void
    {
        $hoy = Carbon::today();

        $citasVencidas = Cita::query()
            ->whereDate('fecha_hora_inicio', '<', $hoy)
            ->whereIn('estado', ['AGENDADA', 'PROGRAMADA', 'PENDIENTE'])
            ->get();

        $total = $citasVencidas->count();

        if ($total === 0) {
            $this->line('  <fg=gray>• Citas:</> No hay citas vencidas pendientes de regularizar.');

            return;
        }

        if ($isDryRun) {
            $this->components->warn("[SIMULACIÓN] Se marcarían {$total} citas pasadas como NO_ASISTIO.");

            return;
        }

        $citasActualizadas = Cita::query()
            ->whereDate('fecha_hora_inicio', '<', $hoy)
            ->whereIn('estado', ['AGENDADA', 'PROGRAMADA', 'PENDIENTE'])
            ->update([
                'estado' => 'NO_ASISTIO',
                'motivo_cancelacion' => 'Cita pasada no asistida (regularizada por rutina diaria)',
            ]);

        $this->components->info("• Citas: {$citasActualizadas} citas pasadas marcadas como NO_ASISTIO.");
    }

    /**
     * Procesa los recordatorios preventivos y citas programadas para hoy o fechas anteriores.
     */
    protected function procesarRecordatorios(bool $isDryRun): void
    {
        $ahora = Carbon::now();

        $recordatoriosPendientes = Recordatorio::query()
            ->where('fecha_hora_programada', '<=', $ahora)
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])
            ->get();

        $total = $recordatoriosPendientes->count();

        if ($total === 0) {
            $this->line('  <fg=gray>• Recordatorios:</> No hay recordatorios pendientes para despachar.');

            return;
        }

        if ($isDryRun) {
            $this->components->warn("[SIMULACIÓN] Se procesarían {$total} recordatorios para envío.");

            return;
        }

        $enviadosPorCorreo = 0;

        foreach ($recordatoriosPendientes as $rec) {
            $rec->loadMissing([
                'paciente.propietario.persona',
                'servicio',
                'profesional.persona',
                'cita.profesional.persona',
            ]);
            $profesional = $rec->profesional_responsable;
            $email = $profesional?->persona?->email;

            // Si el profesional no tiene correo personal registrado, fallback al correo de la clínica
            if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $email = Configuracion::get('clinica_email', config('mail.from.address'));
            }

            if (! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::to($email)->send(new RecordatorioMailable($rec));
                    $enviadosPorCorreo++;
                } catch (\Throwable $e) {
                    Log::error("Error al enviar recordatorio ID {$rec->id_recordatorio} por correo en rutina diaria: ".$e->getMessage());
                }
            }

            $rec->update([
                'estado' => 'ENVIADO',
                'fecha_hora_envio' => $ahora,
            ]);
        }

        $this->components->info("• Recordatorios: {$total} recordatorios procesados como ENVIADO ({$enviadosPorCorreo} notificados por correo).");
    }

    /**
     * Inspecciona productos activos y reporta aquellos con stock por debajo del mínimo.
     */
    protected function verificarStockMinimo(): void
    {
        $productosCriticos = Producto::query()
            ->where('estado', 'ACTIVO')
            ->whereNotNull('stock_minimo')
            ->withSum(['movimientoInventarios as stock_entradas' => function ($q) {
                $q->where('tipo_movimiento', 'ENTRADA');
            }], 'cantidad')
            ->withSum(['movimientoInventarios as stock_salidas' => function ($q) {
                $q->where('tipo_movimiento', 'SALIDA');
            }], 'cantidad')
            ->get()
            ->each(function ($p) {
                $p->stock_actual = max(0, (float) (($p->stock_entradas ?? 0) - ($p->stock_salidas ?? 0)));
            })
            ->filter(function ($p) {
                return $p->stock_actual <= (float) $p->stock_minimo;
            });

        $total = $productosCriticos->count();

        if ($total === 0) {
            $this->line('  <fg=gray>• Inventario:</> Todos los productos cuentan con niveles de stock adecuados.');

            return;
        }

        $this->components->warn("• Inventario: {$total} productos con stock crítico (en o por debajo del mínimo):");

        $rows = $productosCriticos->take(5)->map(function ($p) {
            return [
                'ID' => $p->id_producto,
                'Producto' => $p->nombre,
                'Stock Actual' => (float) $p->stock_actual,
                'Mínimo' => (float) $p->stock_minimo,
            ];
        })->toArray();

        $this->table(['ID', 'Producto', 'Stock Actual', 'Mínimo'], $rows);

        if ($total > 5) {
            $restantes = $total - 5;
            $this->line("    <fg=gray>(...y {$restantes} productos adicionales en stock crítico)</>");
        }
    }
}
