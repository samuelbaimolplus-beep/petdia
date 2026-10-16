<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Recordatorio;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class NotificacionWebController extends Controller
{
    /**
     * Devuelve las notificaciones del sistema agrupadas por tipo.
     */
    public function index(): JsonResponse
    {
        $notificaciones = [];

        // 1. Citas del día (AGENDADA)
        $citasHoy = Cita::with(['paciente.propietario.persona'])
            ->whereDate('fecha_hora_inicio', Carbon::today())
            ->whereIn('estado', ['AGENDADA', 'PROGRAMADA', 'PENDIENTE'])
            ->orderBy('fecha_hora_inicio')
            ->limit(5)
            ->get()
            ->map(function ($cita) {
                return [
                    'id' => 'cita-'.$cita->id_cita,
                    'tipo' => 'cita',
                    'icono' => 'calendar',
                    'titulo' => 'Cita hoy: '.($cita->paciente?->nombre ?? 'Paciente'),
                    'detalle' => $cita->fecha_hora_inicio?->format('H:i').' — '.
                        trim(($cita->paciente?->propietario?->persona?->nombre ?? '').' '.
                            ($cita->paciente?->propietario?->persona?->apellido1 ?? '')),
                    'url' => route('citas.index'),
                    'color' => 'primary',
                ];
            });

        // 2. Alertas de inventario — stock bajo
        $productosConStock = Producto::where('estado', 'ACTIVO')
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
                return $p->stock_actual <= $p->stock_minimo;
            })
            ->take(5)
            ->map(function ($p) {
                return [
                    'id' => 'stock-'.$p->id_producto,
                    'tipo' => 'inventario',
                    'icono' => 'package',
                    'titulo' => 'Stock bajo: '.$p->nombre,
                    'detalle' => 'Quedan '.(int) $p->stock_actual.' unidades (mín. '.(int) $p->stock_minimo.').',
                    'url' => route('inventario.alertas'),
                    'color' => 'amber',
                ];
            });

        // 3. Lotes por vencer (≤ 30 días)
        $lotesPorVencer = Lote::with('producto')
            ->where('estado', 'ACTIVO')
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays(30)])
            ->orderBy('fecha_vencimiento')
            ->limit(5)
            ->get()
            ->map(function ($lote) {
                $dias = now()->diffInDays(Carbon::parse($lote->fecha_vencimiento));

                return [
                    'id' => 'lote-'.$lote->id_lote,
                    'tipo' => 'inventario',
                    'icono' => 'alert-triangle',
                    'titulo' => 'Vencimiento: '.($lote->producto?->nombre ?? 'Producto'),
                    'detalle' => "Vence en {$dias} días — Lote #{$lote->numero_lote}",
                    'url' => route('inventario.alertas'),
                    'color' => 'rose',
                ];
            });

        // 4. Recordatorios próximos (≤ 7 días, PENDIENTE)
        $recordatorios = Recordatorio::with(['paciente.propietario.persona'])
            ->whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])
            ->whereBetween('fecha_hora_programada', [now(), now()->addDays(7)])
            ->orderBy('fecha_hora_programada')
            ->limit(5)
            ->get()
            ->map(function ($r) {
                $fecha = Carbon::parse($r->fecha_hora_programada)->format('d/m');

                return [
                    'id' => 'rec-'.$r->id_recordatorio,
                    'tipo' => 'recordatorio',
                    'icono' => 'bell-ring',
                    'titulo' => ($r->tipo ?? 'Recordatorio').': '.($r->paciente?->nombre ?? 'Paciente'),
                    'detalle' => $fecha.' — '.trim(($r->paciente?->propietario?->persona?->nombre ?? '').' '.($r->paciente?->propietario?->persona?->apellido1 ?? '')),
                    'url' => route('recordatorios.index'),
                    'color' => 'violet',
                ];
            });

        $readIds = session()->get('notificaciones_leidas', []);

        $todas = collect()
            ->merge($citasHoy)
            ->merge($productosConStock)
            ->merge($lotesPorVencer)
            ->merge($recordatorios)
            ->map(function ($item) use ($readIds) {
                $item['leida'] = in_array($item['id'], $readIds, true);

                return $item;
            })
            ->values();

        $pendientes = $todas->where('leida', false)->count();

        return response()->json([
            'total' => $pendientes,
            'total_general' => $todas->count(),
            'notificaciones' => $todas,
        ]);
    }

    /**
     * Marca una notificación como leída en la sesión.
     */
    public function marcarLeida(string $id): JsonResponse
    {
        $readIds = session()->get('notificaciones_leidas', []);
        if (! in_array($id, $readIds, true)) {
            $readIds[] = $id;
            session()->put('notificaciones_leidas', $readIds);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Marca todas las notificaciones como leídas.
     */
    public function marcarTodas(): JsonResponse
    {
        // Traer todos los IDs actuales y marcarlos
        $readIds = session()->get('notificaciones_leidas', []);

        $citas = Cita::whereDate('fecha_hora_inicio', Carbon::today())->pluck('id_cita')->map(fn ($id) => 'cita-'.$id);
        $prods = Producto::where('estado', 'ACTIVO')->pluck('id_producto')->map(fn ($id) => 'stock-'.$id);
        $lotes = Lote::where('estado', 'ACTIVO')->pluck('id_lote')->map(fn ($id) => 'lote-'.$id);
        $recs = Recordatorio::whereIn('estado', ['PENDIENTE', 'PROGRAMADO'])->pluck('id_recordatorio')->map(fn ($id) => 'rec-'.$id);

        $allIds = $citas->merge($prods)->merge($lotes)->merge($recs)->all();
        $updated = array_unique(array_merge($readIds, $allIds));
        session()->put('notificaciones_leidas', $updated);

        return response()->json(['success' => true]);
    }
}
