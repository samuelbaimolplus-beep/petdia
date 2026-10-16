<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\Cargo;
use App\Models\Cita;
use App\Models\DetallePago;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Recordatorio;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $hoy = now()->startOfDay();
        $mesInicio = now()->startOfMonth();

        // Citas programadas para hoy, agrupadas por hora para la gráfica de carga.
        $citasHoy = Cita::query()
            ->whereBetween('fecha_hora_inicio', [$hoy, $hoy->copy()->endOfDay()])
            ->orderBy('fecha_hora_inicio')
            ->get(['id_cita', 'fecha_hora_inicio', 'estado']);

        // Sala de espera: atenciones en curso/pendientes del día.
        $salaEspera = Atencion::query()
            ->whereBetween('fecha_hora_inicio', [$hoy, $hoy->copy()->endOfDay()])
            ->whereIn('estado', ['PENDIENTE', 'EN_PROCESO', 'EN_ESPERA'])
            ->count();

        // Ingresos del mes: suma de pagos aplicados (detalle_pagos).
        $ingresosMes = (float) DetallePago::query()
            ->whereHas('pago', function ($q) use ($mesInicio) {
                $q->where('fecha_hora', '>=', $mesInicio)->where('estado', '!=', 'ANULADO');
            })
            ->sum('monto');

        // Deudas vencidas: cargos pendientes sobre cuentas activas.
        $deudasVencidas = Cargo::query()
            ->whereIn('estado', ['PENDIENTE', 'VENCIDO'])
            ->count();

        // Alertas de inventario.
        $stockBajo = Producto::query()
            ->where('estado', 'ACTIVO')
            ->whereRaw('stock_actual IS NOT NULL AND stock_minimo IS NOT NULL AND stock_actual <= stock_minimo')
            ->count();
        $vencimientos = Lote::query()
            ->where('estado', 'ACTIVO')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->count();

        // Próximos recordatorios (7 días).
        $recordatorios = Recordatorio::query()
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])
            ->whereBetween('fecha_hora_programada', [now(), now()->addDays(7)])
            ->orderBy('fecha_hora_programada')
            ->limit(5)
            ->with(['paciente.persona'])
            ->get();

        // Gráfica: carga por hora 8a-7p.
        $horas = collect(range(8, 19))->map(function (int $hora) use ($citasHoy) {
            $borde = $hora === 19;

            return [
                'hora' => $hora,
                'completadas' => $citasHoy->filter(fn ($c) => $c->fecha_hora_inicio->hour === $hora && $c->estado === 'COMPLETADA')->count(),
                'restantes' => $citasHoy->filter(fn ($c) => $c->fecha_hora_inicio->hour === $hora && $c->estado !== 'COMPLETADA')->count(),
                'borde' => $borde,
            ];
        })->values();

        $completadas = $horas->sum('completadas');
        $restantes = $horas->sum('restantes');
        $horaPico = $horas->sortByDesc(fn ($h) => $h['completadas'] + $h['restantes'])->first();

        return response()->json([
            'citas_hoy' => [
                'total' => $citasHoy->count(),
                'completadas' => $completadas,
                'restantes' => $restantes,
                'por_hora' => $horas,
                'hora_pico' => $horaPico ? [
                    'hora' => $horaPico['hora'],
                    'citas' => $horaPico['completadas'] + $horaPico['restantes'],
                ] : null,
            ],
            'sala_espera' => $salaEspera,
            'ingresos_mes' => $ingresosMes,
            'deudas_vencidas' => $deudasVencidas,
            'alertas_inventario' => [
                'stock_bajo' => $stockBajo,
                'vencimientos' => $vencimientos,
            ],
            'recordatorios' => $recordatorios->map(fn ($r) => [
                'id' => $r->id_recordatorio,
                'tipo' => $r->tipo,
                'fecha_hora_programada' => $r->fecha_hora_programada->format('Y-m-d H:i:s'),
                'paciente' => $r->paciente?->nombre,
                'propietario' => $r->paciente?->persona?->nombre,
            ]),
        ]);
    }
}
