<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\AtencionServicio;
use App\Models\DetallePago;
use App\Models\Especie;
use App\Models\Lote;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Profesional;
use App\Models\Servicio;
use App\Models\ServicioDomicilio;
use App\Models\Usuario;
use App\Models\Vacunacion;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteWebController extends Controller
{
    /**
     * Panel general de reportería con filtros de fechas, KPIs consolidados y desglose analítico.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('reportes')) {
            abort(403, 'No tienes permisos para acceder al módulo de reportes.');
        }

        // Rango de fechas (por defecto: mes actual)
        $desde = $request->input('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $hasta = $request->input('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        // 1. Reporte Financiero: Ingresos totales y por método de pago usando DetallePago
        $pagosQuery = Pago::where(function ($q) {
            $q->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
        })->whereBetween('fecha_hora', [$desde, $hasta]);

        $totalIngresos = (float) DetallePago::whereHas('pago', function ($q) use ($desde, $hasta) {
            $q->where(function ($qp) {
                $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
            })->whereBetween('fecha_hora', [$desde, $hasta]);
        })->sum('monto');

        $cantidadTransacciones = (clone $pagosQuery)->count();

        $ingresosPorMetodo = DetallePago::with('metodoPago')
            ->whereHas('pago', function ($q) use ($desde, $hasta) {
                $q->where(function ($qp) {
                    $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                })->whereBetween('fecha_hora', [$desde, $hasta]);
            })
            ->select('id_metodo_pago', DB::raw('SUM(monto) as total'), DB::raw('COUNT(*) as cantidad'))
            ->groupBy('id_metodo_pago')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use ($totalIngresos) {
                $item->metodo_nombre = $item->metodoPago->nombre ?? 'General';
                $item->porcentaje = $totalIngresos > 0 ? round(($item->total / $totalIngresos) * 100, 1) : 0;

                return $item;
            });

        // Ingresos diarios para la gráfica de tendencia
        $ingresosPorDia = DetallePago::join('pagos', 'detalle_pagos.id_pago', '=', 'pagos.id_pago')
            ->where(function ($qp) {
                $qp->whereNull('pagos.estado')->orWhere('pagos.estado', '!=', 'ANULADO');
            })
            ->whereBetween('pagos.fecha_hora', [$desde, $hasta])
            ->select(DB::raw('DATE(pagos.fecha_hora) as dia'), DB::raw('SUM(detalle_pagos.monto) as total'))
            ->groupBy(DB::raw('DATE(pagos.fecha_hora)'))
            ->orderBy('dia', 'asc')
            ->get();

        // 2. Reporte Clínico y Operativo: Atenciones
        $atencionesQuery = Atencion::whereBetween('fecha_hora_inicio', [$desde, $hasta]);
        $totalAtenciones = (clone $atencionesQuery)->count();
        $atencionesCompletadas = (clone $atencionesQuery)->where('estado', 'COMPLETADA')->count();

        $atencionesClinicas = (clone $atencionesQuery)->has('atencionVeterinaria')->count();
        $atencionesEsteticas = (clone $atencionesQuery)->has('atencionEstetica')->count();
        $atencionesGenerales = max(0, $totalAtenciones - ($atencionesClinicas + $atencionesEsteticas));

        $atencionesPorTipo = collect([
            (object) ['tipo' => 'Consulta Clínica', 'total' => $atencionesClinicas],
            (object) ['tipo' => 'Estética y Spa', 'total' => $atencionesEsteticas],
        ]);
        if ($atencionesGenerales > 0) {
            $atencionesPorTipo->push((object) ['tipo' => 'Atención General', 'total' => $atencionesGenerales]);
        }

        // 3. Vacunas aplicadas en el período
        $vacunasAplicadas = Vacunacion::whereHas('procedimiento', function ($q) use ($desde, $hasta) {
            $q->whereBetween('fecha_hora', [$desde, $hasta]);
        })->count();

        // 4. Inventario: Productos críticos y lotes por vencer
        $productosCriticos = Producto::withSum('movimientoInventarios as stock_actual', 'cantidad')
            ->where('estado', 'ACTIVO')
            ->get()
            ->filter(function ($p) {
                return (float) ($p->stock_actual ?? 0) <= (float) $p->stock_minimo;
            })
            ->sortBy('nombre')
            ->take(10);

        $lotesPorVencer = Lote::with('producto')
            ->where('estado', 'ACTIVO')
            ->whereDate('fecha_vencimiento', '<=', Carbon::now()->addDays(30))
            ->whereDate('fecha_vencimiento', '>=', Carbon::now())
            ->orderBy('fecha_vencimiento', 'asc')
            ->take(10)
            ->get();

        // 5. Métricas para Gráficas Interactivas (Mejora 3.2)
        // Top 10 servicios solicitados en el período
        $topServicios = AtencionServicio::with('servicio')
            ->whereHas('atencion', fn ($q) => $q->whereBetween('fecha_hora_inicio', [$desde, $hasta]))
            ->select('id_servicio', DB::raw('SUM(cantidad) as total_pedidos'))
            ->groupBy('id_servicio')
            ->orderByDesc('total_pedidos')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'nombre' => $item->servicio?->nombre ?? ('Servicio #'.$item->id_servicio),
                'cantidad' => (int) $item->total_pedidos,
            ]);

        // Si no hay atenciones_servicios en el rango, traer servicios activos con 0 para muestra visual
        if ($topServicios->isEmpty()) {
            $topServicios = Servicio::where('estado', 'ACTIVO')
                ->limit(6)
                ->get()
                ->map(fn ($s) => [
                    'nombre' => $s->nombre,
                    'cantidad' => 0,
                ]);
        }

        // Atenciones por tipo incluyendo Domicilio
        $atencionesDomicilio = ServicioDomicilio::whereHas('cita', fn ($q) => $q->whereBetween('fecha_hora_inicio', [$desde, $hasta]))->count();
        $atencionesPorTipoConDomicilio = collect([
            ['tipo' => 'Veterinaria', 'total' => $atencionesClinicas],
            ['tipo' => 'Estética y Spa', 'total' => $atencionesEsteticas],
            ['tipo' => 'A Domicilio', 'total' => $atencionesDomicilio],
            ['tipo' => 'General / Control', 'total' => $atencionesGenerales],
        ]);

        $charts = [
            'ingresos' => [
                'labels' => $ingresosPorDia->map(fn ($d) => Carbon::parse($d->dia)->format('d/m'))->values(),
                'valores' => $ingresosPorDia->map(fn ($d) => (float) $d->total)->values(),
            ],
            'metodos' => [
                'labels' => $ingresosPorMetodo->map(fn ($m) => $m->metodo_nombre)->values(),
                'valores' => $ingresosPorMetodo->map(fn ($m) => (float) $m->total)->values(),
            ],
            'servicios' => [
                'labels' => $topServicios->pluck('nombre')->values(),
                'valores' => $topServicios->pluck('cantidad')->values(),
            ],
            'atenciones' => [
                'labels' => $atencionesPorTipoConDomicilio->pluck('tipo')->values(),
                'valores' => $atencionesPorTipoConDomicilio->pluck('total')->values(),
            ],
        ];

        return view('reportes.index', compact(
            'desde',
            'hasta',
            'totalIngresos',
            'cantidadTransacciones',
            'ingresosPorMetodo',
            'ingresosPorDia',
            'totalAtenciones',
            'atencionesCompletadas',
            'atencionesPorTipo',
            'vacunasAplicadas',
            'productosCriticos',
            'lotesPorVencer',
            'charts'
        ));
    }

    /**
     * SEC: Sanitiza un valor para prevenir inyección de fórmulas en CSV (Excel).
     */
    private function csvSafe(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $dangerous = ['=', '+', '-', '@', "\t", "\r", "\n"];
        if (in_array($value[0], $dangerous, true)) {
            return "'".$value;
        }

        return $value;
    }

    /**
     * Exportación de datos en formato CSV estándar descargable.
     */
    public function exportar(Request $request, string $tipo): StreamedResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('reportes')) {
            abort(403, 'No tienes permisos para exportar reportes.');
        }

        $desde = $request->input('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $hasta = $request->input('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        $filename = "reporte_{$tipo}_".Carbon::now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tipo, $desde, $hasta) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para apertura correcta en Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($tipo === 'ingresos') {
                fputcsv($handle, ['ID Pago', 'Fecha y Hora', 'Método de Pago', 'Monto (Bs.)', 'Estado', 'Referencia']);
                $detalles = DetallePago::with(['pago', 'metodoPago'])
                    ->whereHas('pago', function ($q) use ($desde, $hasta) {
                        $q->whereBetween('fecha_hora', [$desde, $hasta]);
                    })
                    ->get();
                foreach ($detalles as $d) {
                    fputcsv($handle, [
                        $d->id_pago,
                        $d->pago->fecha_hora ?? '',
                        $this->csvSafe($d->metodoPago->nombre ?? 'General'),
                        number_format($d->monto, 2, '.', ''),
                        $this->csvSafe($d->pago->estado ?? 'APLICADO'),
                        $this->csvSafe($d->referencia ?? ''),
                    ]);
                }
            } elseif ($tipo === 'atenciones') {
                fputcsv($handle, ['ID Atención', 'Fecha/Hora Inicio', 'Paciente', 'Propietario', 'Tipo', 'Estado']);
                $atenciones = Atencion::with(['paciente.propietario.persona', 'atencionVeterinaria', 'atencionEstetica'])
                    ->whereBetween('fecha_hora_inicio', [$desde, $hasta])
                    ->orderBy('id_atencion')
                    ->get();
                foreach ($atenciones as $at) {
                    $tipoNombre = $at->atencionVeterinaria ? 'Clínica' : ($at->atencionEstetica ? 'Estética' : 'General');
                    fputcsv($handle, [
                        $at->id_atencion,
                        $at->fecha_hora_inicio,
                        $this->csvSafe($at->paciente->nombre ?? 'N/A'),
                        $this->csvSafe(trim(($at->paciente->propietario->persona->nombre ?? '').' '.($at->paciente->propietario->persona->apellido1 ?? ''))),
                        $this->csvSafe($tipoNombre),
                        $this->csvSafe($at->estado),
                    ]);
                }
            } elseif ($tipo === 'inventario') {
                fputcsv($handle, ['ID Producto', 'Nombre', 'Tipo', 'Categoría', 'Precio Venta', 'Stock Mínimo']);
                $productos = Producto::where('estado', 'ACTIVO')->orderBy('nombre')->get();
                foreach ($productos as $pr) {
                    fputcsv($handle, [
                        $pr->id_producto,
                        $this->csvSafe($pr->nombre),
                        $this->csvSafe($pr->tipo),
                        $this->csvSafe($pr->categoria),
                        number_format($pr->precio_venta, 2, '.', ''),
                        $pr->stock_minimo,
                    ]);
                }
            } elseif ($tipo === 'vacunaciones') {
                fputcsv($handle, ['ID Procedimiento', 'Fecha y Hora', 'Paciente', 'Especie/Raza', 'Propietario', 'Teléfono', 'Vacuna/Producto', 'Sitio Aplicación', 'Próxima Dosis', 'Profesional Responsable']);
                $vacunaciones = Vacunacion::with([
                    'procedimiento.atencionVeterinaria.atencion.paciente.propietario.persona',
                    'procedimiento.atencionVeterinaria.atencion.paciente.raza.especie',
                    'procedimiento.atencionVeterinaria.atencion.participaciones.profesional.persona',
                    'administracionMedicamento.usoProductoAtencion.producto',
                ])->whereHas('procedimiento', function ($q) use ($desde, $hasta) {
                    $q->whereBetween('fecha_hora', [$desde, $hasta]);
                })->get();

                foreach ($vacunaciones as $v) {
                    $proc = $v->procedimiento;
                    $at = $proc?->atencionVeterinaria?->atencion;
                    $pac = $at?->paciente;
                    $prop = $pac?->propietario?->persona;
                    $prof = $at?->participaciones?->first()?->profesional?->persona;
                    $vacunaNombre = $proc?->descripcion ?: ($v->administracionMedicamento?->usoProductoAtencion?->producto?->nombre ?? 'Vacuna');

                    fputcsv($handle, [
                        $v->id_procedimiento,
                        $proc?->fecha_hora ?? '',
                        $this->csvSafe($pac?->nombre ?? 'N/A'),
                        $this->csvSafe(($pac?->raza?->especie?->nombre ?? '').' - '.($pac?->raza?->nombre ?? '')),
                        $this->csvSafe(trim(($prop?->nombre ?? '').' '.($prop?->apellido1 ?? ''))),
                        $this->csvSafe($prop?->telefono ?? ''),
                        $this->csvSafe($vacunaNombre),
                        $this->csvSafe($v->sitio_aplicacion ?? ''),
                        $this->csvSafe($v->proxima_dosis ?? ''),
                        $this->csvSafe(trim(($prof?->nombre ?? '').' '.($prof?->apellido1 ?? ''))),
                    ]);
                }
            } elseif ($tipo === 'pacientes_atendidos') {
                fputcsv($handle, ['ID Atención', 'Fecha/Hora Inicio', 'Paciente', 'Especie', 'Raza', 'Propietario', 'Teléfono', 'Tipo Atención', 'Profesional(es)', 'Motivo / Diagnóstico', 'Estado']);
                $atenciones = Atencion::with([
                    'paciente.propietario.persona',
                    'paciente.raza.especie',
                    'atencionVeterinaria',
                    'atencionEstetica',
                    'participaciones.profesional.persona',
                ])->whereBetween('fecha_hora_inicio', [$desde, $hasta])->orderBy('fecha_hora_inicio', 'desc')->get();

                foreach ($atenciones as $at) {
                    $pac = $at->paciente;
                    $prop = $pac?->propietario?->persona;
                    $profs = $at->participaciones->map(fn ($p) => trim(($p->profesional->persona->nombre ?? '').' '.($p->profesional->persona->apellido1 ?? '')))->filter()->implode(', ');
                    $isUrgente = $at->origen === 'URGENCIA' || ($at->atencionVeterinaria && $at->atencionVeterinaria->tipo === 'EMERGENCIA');
                    $tipoNombre = $isUrgente ? 'URGENCIA' : ($at->atencionVeterinaria ? 'CONSULTA CLÍNICA' : ($at->atencionEstetica ? 'ESTÉTICA' : 'GENERAL'));
                    $motivo = $at->atencionVeterinaria?->motivo_consulta ?: ($at->atencionEstetica?->modalidad ?: $at->observaciones);

                    fputcsv($handle, [
                        $at->id_atencion,
                        $at->fecha_hora_inicio,
                        $this->csvSafe($pac?->nombre ?? 'N/A'),
                        $this->csvSafe($pac?->raza?->especie?->nombre ?? ''),
                        $this->csvSafe($pac?->raza?->nombre ?? ''),
                        $this->csvSafe(trim(($prop?->nombre ?? '').' '.($prop?->apellido1 ?? ''))),
                        $this->csvSafe($prop?->telefono ?? ''),
                        $this->csvSafe($tipoNombre),
                        $this->csvSafe($profs),
                        $this->csvSafe($motivo ?? ''),
                        $this->csvSafe($at->estado),
                    ]);
                }
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * CU38: Generar reporte de vacunaciones.
     */
    public function vacunaciones(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('reportes')) {
            abort(403, 'No tienes permisos para acceder al reporte de vacunaciones.');
        }

        $desde = $request->input('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->subMonths(3)->startOfDay();

        $hasta = $request->input('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        $query = Vacunacion::with([
            'procedimiento.atencionVeterinaria.atencion.paciente.propietario.persona',
            'procedimiento.atencionVeterinaria.atencion.paciente.raza.especie',
            'procedimiento.atencionVeterinaria.atencion.participaciones.profesional.persona',
            'administracionMedicamento.usoProductoAtencion.producto',
        ])->whereHas('procedimiento', function ($q) use ($desde, $hasta) {
            $q->whereBetween('fecha_hora', [$desde, $hasta]);
        });

        if ($paciente = $request->input('paciente')) {
            $query->whereHas('procedimiento.atencionVeterinaria.atencion.paciente', function ($q) use ($paciente) {
                $q->where('nombre', 'ilike', "%{$paciente}%")
                    ->orWhere('id_paciente', is_numeric($paciente) ? (int) $paciente : 0);
            });
        }

        if ($cliente = $request->input('cliente')) {
            $query->whereHas('procedimiento.atencionVeterinaria.atencion.paciente.propietario.persona', function ($q) use ($cliente) {
                $q->where('nombre', 'ilike', "%{$cliente}%")
                    ->orWhere('apellido1', 'ilike', "%{$cliente}%")
                    ->orWhere('documento', 'ilike', "%{$cliente}%");
            });
        }

        if ($vacuna = $request->input('vacuna')) {
            $query->where(function ($q) use ($vacuna) {
                $q->whereHas('procedimiento', function ($qp) use ($vacuna) {
                    $qp->where('descripcion', 'ilike', "%{$vacuna}%");
                })->orWhereHas('administracionMedicamento.usoProductoAtencion.producto', function ($qp) use ($vacuna) {
                    $qp->where('nombre', 'ilike', "%{$vacuna}%");
                });
            });
        }

        if ($profesional = $request->input('profesional')) {
            $query->whereHas('procedimiento.atencionVeterinaria.atencion.participaciones', function ($q) use ($profesional) {
                $q->where('id_profesional', $profesional);
            });
        }

        $vacunaciones = $query->paginate(15)->withQueryString();
        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->get();

        // KPIs
        $totalVacunas = (clone $query)->count();
        $proximasDosisMes = (clone $query)->whereNotNull('proxima_dosis')
            ->whereBetween('proxima_dosis', [Carbon::now(), Carbon::now()->addMonth()])
            ->count();

        AuditService::log(
            'GENERAR_REPORTE',
            'REPORTES',
            [
                'tipo' => 'VACUNACIONES',
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
                'total_registros' => $totalVacunas,
            ],
            null,
            $currentUser->id_usuario,
            'EXITO',
            "Reporte de vacunaciones generado con {$totalVacunas} registros"
        );

        return view('reportes.vacunaciones', [
            'vacunaciones' => $vacunaciones,
            'desde' => $desde,
            'hasta' => $hasta,
            'profesionales' => $profesionales,
            'totalVacunas' => $totalVacunas,
            'proximasDosisMes' => $proximasDosisMes,
            'filters' => $request->only(['desde', 'hasta', 'paciente', 'cliente', 'vacuna', 'profesional']),
        ]);
    }

    /**
     * CU39: Generar reporte de pacientes atendidos.
     */
    public function pacientesAtendidos(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('reportes')) {
            abort(403, 'No tienes permisos para acceder al reporte de pacientes atendidos.');
        }

        $desde = $request->input('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->subMonths(1)->startOfDay();

        $hasta = $request->input('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : Carbon::now()->endOfDay();

        $query = Atencion::with([
            'paciente.propietario.persona',
            'paciente.raza.especie',
            'atencionVeterinaria',
            'atencionEstetica',
            'participaciones.profesional.persona',
        ])->whereBetween('fecha_hora_inicio', [$desde, $hasta]);

        if ($tipo = $request->input('tipo')) {
            if ($tipo === 'URGENCIA') {
                $query->where(function ($q) {
                    $q->where('origen', 'URGENCIA')
                        ->orWhereHas('atencionVeterinaria', function ($qv) {
                            $qv->where('tipo', 'EMERGENCIA');
                        });
                });
            } elseif ($tipo === 'REGULAR') {
                $query->where(function ($q) {
                    $q->where(function ($qo) {
                        $qo->whereNull('origen')->orWhere('origen', '!=', 'URGENCIA');
                    })->where(function ($qv) {
                        $qv->whereDoesntHave('atencionVeterinaria')
                            ->orWhereHas('atencionVeterinaria', function ($qsub) {
                                $qsub->whereNull('tipo')->orWhere('tipo', '!=', 'EMERGENCIA');
                            });
                    });
                });
            } elseif ($tipo === 'CLINICA') {
                $query->has('atencionVeterinaria');
            } elseif ($tipo === 'ESTETICA') {
                $query->has('atencionEstetica');
            }
        }

        if ($profesional = $request->input('profesional')) {
            $query->whereHas('participaciones', function ($q) use ($profesional) {
                $q->where('id_profesional', $profesional);
            });
        }

        if ($especie = $request->input('especie')) {
            $query->whereHas('paciente.raza', function ($q) use ($especie) {
                $q->where('id_especie', $especie);
            });
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('paciente', function ($qp) use ($search) {
                    $qp->where('nombre', 'ilike', "%{$search}%")
                        ->orWhereHas('propietario.persona', function ($qprop) use ($search) {
                            $qprop->where('nombre', 'ilike', "%{$search}%")
                                ->orWhere('apellido1', 'ilike', "%{$search}%")
                                ->orWhere('documento', 'ilike', "%{$search}%");
                        });
                })->orWhere('observaciones', 'ilike', "%{$search}%");
            });
        }

        $allMatching = (clone $query)->get();

        // Totales y estadísticas sin duplicados
        $totalAtenciones = $allMatching->count();
        $pacientesUnicos = $allMatching->pluck('id_paciente')->unique()->count();
        $totalUrgencias = $allMatching->filter(fn ($a) => $a->origen === 'URGENCIA' || ($a->atencionVeterinaria && $a->atencionVeterinaria->tipo === 'EMERGENCIA'))->count();
        $totalRegulares = max(0, $totalAtenciones - $totalUrgencias);

        $atenciones = $query->orderBy('fecha_hora_inicio', 'desc')->paginate(15)->withQueryString();
        $profesionales = Profesional::with('persona')->where('estado', 'ACTIVO')->get();
        $especies = Especie::orderBy('nombre')->get();

        AuditService::log(
            'GENERAR_REPORTE',
            'REPORTES',
            [
                'tipo' => 'PACIENTES_ATENDIDOS',
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
                'total_atenciones' => $totalAtenciones,
                'pacientes_unicos' => $pacientesUnicos,
            ],
            null,
            $currentUser->id_usuario,
            'EXITO',
            "Reporte de pacientes atendidos generado: {$totalAtenciones} atenciones ({$pacientesUnicos} pacientes únicos)"
        );

        return view('reportes.pacientes_atendidos', [
            'atenciones' => $atenciones,
            'desde' => $desde,
            'hasta' => $hasta,
            'profesionales' => $profesionales,
            'especies' => $especies,
            'totalAtenciones' => $totalAtenciones,
            'pacientesUnicos' => $pacientesUnicos,
            'totalUrgencias' => $totalUrgencias,
            'totalRegulares' => $totalRegulares,
            'filters' => $request->only(['desde', 'hasta', 'profesional', 'tipo', 'especie', 'search']),
        ]);
    }
}
