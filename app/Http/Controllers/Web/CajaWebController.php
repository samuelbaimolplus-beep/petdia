<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Atencion;
use App\Models\Cargo;
use App\Models\CierreCaja;
use App\Models\Cuenta;
use App\Models\DetallePago;
use App\Models\DevolucionPago;
use App\Models\EgresoCaja;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\PagoAtencion;
use App\Models\Propietario;
use App\Models\Usuario;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaWebController extends Controller
{
    /**
     * Panel principal de Caja y Pagos con KPIs, filtros e historial.
     */
    public function index(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        $query = Pago::with([
            'cuenta.propietario.persona',
            'detallePagos.metodoPago',
            'pagoAtencions.atencion.paciente',
            'cierreCaja',
            'devoluciones',
        ])->orderByDesc('id_pago');

        // Búsqueda por recibo/ID o nombre de cliente/mascota
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id_pago', 'ilike', "%{$search}%")
                    ->orWhere('observaciones', 'ilike', "%{$search}%")
                    ->orWhereHas('cuenta.propietario.persona', function ($qp) use ($search) {
                        $qp->where('nombre', 'ilike', "%{$search}%")
                            ->orWhere('apellido1', 'ilike', "%{$search}%")
                            ->orWhere('documento', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('pagoAtencions.atencion.paciente', function ($qpac) use ($search) {
                        $qpac->where('nombre', 'ilike', "%{$search}%");
                    });
            });
        }

        // Filtro por Estado
        if ($estado = $request->input('estado')) {
            $query->where('estado', $estado);
        }

        // Filtro por Método de Pago
        if ($idMetodo = $request->input('id_metodo_pago')) {
            $query->whereHas('detallePagos', function ($q) use ($idMetodo) {
                $q->where('id_metodo_pago', $idMetodo);
            });
        }

        // Filtro por Fecha
        $hoy = Carbon::today()->format('Y-m-d');
        if ($fechaDesde = $request->input('fecha_desde')) {
            $query->whereDate('fecha_hora', '>=', $fechaDesde);
        }
        if ($fechaHasta = $request->input('fecha_hasta')) {
            $query->whereDate('fecha_hora', '<=', $fechaHasta);
        }

        $pagos = $query->paginate(15)->withQueryString();

        // Métodos de pago disponibles
        $metodosPago = MetodoPago::where('estado', 'ACTIVO')->get();

        // KPIs del día
        $pagosHoyQuery = Pago::whereDate('fecha_hora', Carbon::today())
            ->where(function ($q) {
                $q->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
            });

        $cantidadPagosHoy = (clone $pagosHoyQuery)->count();
        $ingresosHoy = (float) DetallePago::whereHas('pago', function ($q) {
            $q->whereDate('fecha_hora', Carbon::today())
                ->where(function ($qp) {
                    $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                });
        })->sum('monto');

        // Desglose por método de hoy
        $desgloseMetodos = DetallePago::with('metodoPago')
            ->whereHas('pago', function ($q) {
                $q->whereDate('fecha_hora', Carbon::today())
                    ->where(function ($qp) {
                        $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                    });
            })
            ->select('id_metodo_pago', DB::raw('SUM(monto) as total'))
            ->groupBy('id_metodo_pago')
            ->get();

        // Atenciones pendientes de cobro
        $atencionesPendientesCount = Atencion::where('estado', 'FINALIZADA')
            ->where('total_final', '>', 0)
            ->whereDoesntHave('pagoAtencions')
            ->count();

        // Estado del cierre de caja hoy
        $cierreHoy = CierreCaja::whereDate('fecha_hora_cierre', Carbon::today())->latest('id_cierre')->first();
        $hayPagosSinCierre = Pago::whereNull('id_cierre')
            ->where(function ($q) {
                $q->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
            })->exists();

        // Control de Egresos de Caja Chica del día
        $egresosHoy = (float) EgresoCaja::whereDate('fecha_hora', Carbon::today())->sum('monto');
        $saldoNetoHoy = $ingresosHoy - $egresosHoy;
        $egresosRecientes = EgresoCaja::with('usuario.persona')->whereDate('fecha_hora', Carbon::today())->orderByDesc('id_egreso')->get();
        $hayEgresosSinCierre = EgresoCaja::whereNull('id_cierre')->exists();

        return view('caja.index', compact(
            'pagos',
            'metodosPago',
            'cantidadPagosHoy',
            'ingresosHoy',
            'egresosHoy',
            'saldoNetoHoy',
            'egresosRecientes',
            'desgloseMetodos',
            'atencionesPendientesCount',
            'cierreHoy',
            'hayPagosSinCierre',
            'hayEgresosSinCierre'
        ));
    }

    /**
     * Formulario para registrar cobro de una atención, producto o cargo directo.
     */
    public function cobroForm(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        $atencionSeleccionada = null;
        if ($idAtencion = $request->input('id_atencion')) {
            $atencionSeleccionada = Atencion::with([
                'paciente.propietario.persona',
                'atencionServicios.servicio',
                'usosProductoAtencion.producto',
                'cita',
            ])->find($idAtencion);
        }

        // Atenciones finalizadas o en proceso que tengan total > 0 y saldo pendiente
        $atencionesPendientes = Atencion::with(['paciente.propietario.persona', 'atencionServicios.servicio'])
            ->where('total_final', '>', 0)
            ->whereDoesntHave('pagoAtencions')
            ->orderByDesc('id_atencion')
            ->limit(20)
            ->get();

        $propietarios = Propietario::with('persona')->limit(30)->get();
        $metodosPago = MetodoPago::where('estado', 'ACTIVO')->get();

        return view('caja.cobro', compact(
            'atencionSeleccionada',
            'atencionesPendientes',
            'propietarios',
            'metodosPago'
        ));
    }

    /**
     * Procesa y registra el cobro de una atención o cuenta en el sistema.
     */
    public function procesarCobro(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        $validated = $request->validate([
            'id_atencion' => ['nullable', 'integer', 'exists:atenciones,id_atencion'],
            'id_propietario' => ['nullable', 'integer', 'exists:propietarios,id_persona'],
            'id_metodo_pago' => ['required', 'integer', 'exists:metodos_pago,id_metodo_pago'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'referencia' => ['nullable', 'string', 'max:100'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated) {
            $atencion = null;
            $idPropietario = $validated['id_propietario'] ?? null;

            if (! empty($validated['id_atencion'])) {
                $atencion = Atencion::with('paciente.propietario')->findOrFail($validated['id_atencion']);
                if ($atencion->paciente && $atencion->paciente->id_propietario) {
                    $idPropietario = $atencion->paciente->id_propietario;
                }
            }

            // Asegurar cuenta para el propietario si existe
            $cuenta = null;
            if ($idPropietario) {
                $cuenta = Cuenta::firstOrCreate(
                    ['id_propietario' => $idPropietario],
                    [
                        'fecha_apertura' => Carbon::now()->toDateString(),
                        'estado' => 'ACTIVA',
                    ]
                );
            }

            // 1. Crear el Pago
            $pago = Pago::create([
                'id_cuenta' => $cuenta?->id_cuenta,
                'fecha_hora' => Carbon::now(),
                'estado' => 'COMPLETADO',
                'observaciones' => $validated['observaciones'] ?? ($atencion ? "Cobro de Atención #{$atencion->id_atencion}" : 'Cobro directo en caja'),
            ]);

            // 2. Crear el Detalle de Pago (método y monto)
            DetallePago::create([
                'id_pago' => $pago->id_pago,
                'id_metodo_pago' => $validated['id_metodo_pago'],
                'monto' => $validated['monto'],
                'referencia' => $validated['referencia'] ?? null,
            ]);

            // 3. Vincular a Atención si aplica
            if ($atencion) {
                PagoAtencion::create([
                    'id_pago' => $pago->id_pago,
                    'id_atencion' => $atencion->id_atencion,
                    'monto_aplicado' => $validated['monto'],
                ]);

                // Si se aplicó descuento adicional, actualizar total_final si es necesario
                if (! empty($validated['descuento']) && $validated['descuento'] > 0) {
                    $nuevoTotal = max(0, ((float) $atencion->total_final) - (float) $validated['descuento']);
                    $atencion->update(['total_final' => $nuevoTotal]);
                }
            }

            return redirect()->route('caja.index')->with('success', "Cobro registrado exitosamente con comprobante Recibo #{$pago->id_pago}.");
        });
    }

    /**
     * Registra un egreso o salida de caja chica.
     */
    public function storeEgreso(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para registrar egresos en caja.');
        }

        $validated = $request->validate([
            'monto' => ['required', 'numeric', 'min:0.01'],
            'concepto' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string', 'max:100'],
            'comprobante' => ['nullable', 'string', 'max:100'],
        ], [
            'monto.required' => 'El monto del egreso es obligatorio.',
            'monto.min' => 'El monto debe ser mayor a 0.',
            'concepto.required' => 'El concepto o motivo del egreso es obligatorio.',
        ]);

        $egreso = EgresoCaja::create([
            'id_usuario' => $currentUser->id_usuario,
            'monto' => $validated['monto'],
            'concepto' => $validated['concepto'],
            'categoria' => $validated['categoria'],
            'comprobante' => $validated['comprobante'] ?? null,
            'fecha_hora' => now(),
        ]);

        AuditService::log('EGRESO_CAJA', 'CAJA', [
            'id_egreso' => $egreso->id_egreso,
            'monto' => $validated['monto'],
            'concepto' => $validated['concepto'],
            'categoria' => $validated['categoria'],
        ], $egreso);

        return redirect()->route('caja.index')->with('success', 'Egreso de caja chica registrado exitosamente.');
    }

    /**
     * Pantalla de Arqueo y Cierre de Caja del día / turno.
     */
    public function cierreForm(): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        // Obtener todos los pagos pendientes de cierre (sin id_cierre)
        $pagosSinCierre = Pago::with(['detallePagos.metodoPago', 'cuenta.propietario.persona'])
            ->whereNull('id_cierre')
            ->where(function ($q) {
                $q->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
            })
            ->orderBy('fecha_hora')
            ->get();

        $totalRegistrado = (float) DetallePago::whereHas('pago', function ($q) {
            $q->whereNull('id_cierre')
                ->where(function ($qp) {
                    $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                });
        })->sum('monto');

        // Egresos pendientes de cierre
        $egresosSinCierre = EgresoCaja::with('usuario.persona')
            ->whereNull('id_cierre')
            ->orderBy('fecha_hora')
            ->get();

        $totalEgresos = (float) $egresosSinCierre->sum('monto');
        $saldoNetoEsperado = $totalRegistrado - $totalEgresos;

        // Desglose por método de pago para el arqueo
        $totalesPorMetodo = DetallePago::with('metodoPago')
            ->whereHas('pago', function ($q) {
                $q->whereNull('id_cierre')
                    ->where(function ($qp) {
                        $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                    });
            })
            ->select('id_metodo_pago', DB::raw('SUM(monto) as subtotal'), DB::raw('COUNT(id_detalle_pago) as transacciones'))
            ->groupBy('id_metodo_pago')
            ->get();

        $primerPago = $pagosSinCierre->first();
        $fechaApertura = $primerPago ? Carbon::parse($primerPago->fecha_hora) : Carbon::today()->setTime(8, 0);

        // Historial de cierres previos
        $cierresAnteriores = CierreCaja::with('usuario.persona')
            ->orderByDesc('id_cierre')
            ->limit(5)
            ->get();

        return view('caja.cierre', compact(
            'pagosSinCierre',
            'totalRegistrado',
            'egresosSinCierre',
            'totalEgresos',
            'saldoNetoEsperado',
            'totalesPorMetodo',
            'fechaApertura',
            'cierresAnteriores'
        ));
    }

    /**
     * Procesa el Cierre de Caja definitivo y arqueo físico.
     */
    public function procesarCierre(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        $validated = $request->validate([
            'total_fisico' => ['required', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ]);

        return DB::transaction(function () use ($validated, $currentUser) {
            $pagosPendientes = Pago::whereNull('id_cierre')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                })
                ->get();

            $totalIngresos = (float) DetallePago::whereHas('pago', function ($q) {
                $q->whereNull('id_cierre')
                    ->where(function ($qp) {
                        $qp->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                    });
            })->sum('monto');

            $egresosPendientes = EgresoCaja::whereNull('id_cierre')->get();
            $totalEgresos = (float) $egresosPendientes->sum('monto');
            $totalSistemaNeto = $totalIngresos - $totalEgresos;

            $primerPago = $pagosPendientes->sortBy('fecha_hora')->first();
            $fechaApertura = $primerPago ? $primerPago->fecha_hora : Carbon::now()->subHours(8);

            $diferencia = (float) $validated['total_fisico'] - $totalSistemaNeto;
            $obsFinal = ($validated['observaciones'] ?? '')." [Arqueo Físico: \${$validated['total_fisico']} | Ingresos: \${$totalIngresos} | Egresos: \${$totalEgresos} | Neto: \${$totalSistemaNeto} | Dif: \${$diferencia}]";

            $cierre = CierreCaja::create([
                'id_usuario' => $currentUser->id_usuario,
                'fecha_hora_apertura' => $fechaApertura,
                'fecha_hora_cierre' => Carbon::now(),
                'total_registrado' => $totalSistemaNeto,
                'observaciones' => trim($obsFinal),
            ]);

            // Asignar id_cierre a todos los pagos pendientes
            Pago::whereNull('id_cierre')
                ->where(function ($q) {
                    $q->whereNull('estado')->orWhere('estado', '!=', 'ANULADO');
                })
                ->update(['id_cierre' => $cierre->id_cierre]);

            // Asignar id_cierre a todos los egresos pendientes
            EgresoCaja::whereNull('id_cierre')
                ->update(['id_cierre' => $cierre->id_cierre]);

            AuditService::log('CIERRE_CAJA', 'CAJA', [
                'id_cierre' => $cierre->id_cierre,
                'total_registrado' => $totalSistemaNeto,
                'total_fisico' => (float) $validated['total_fisico'],
                'diferencia' => $diferencia,
            ], $cierre);

            return redirect()->route('caja.index')->with('success', "Cierre de Caja #{$cierre->id_cierre} completado exitosamente por un total neto de \${$totalSistemaNeto}.");
        });
    }

    /**
     * Anula o devuelve un pago con registro de auditoría en devolucion_pagos.
     */
    public function anularPago(Request $request, Pago $pago): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        $validated = $request->validate([
            'motivo' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        // SEC: Idempotencia — no permitir anular un pago que ya fue anulado
        if ($pago->estado === 'ANULADO') {
            return redirect()->route('caja.index')
                ->with('warning', "El Pago #{$pago->id_pago} ya se encontraba anulado previamente.");
        }

        return DB::transaction(function () use ($pago, $validated, $currentUser) {
            $montoTotal = $pago->monto_total;

            DevolucionPago::create([
                'id_pago' => $pago->id_pago,
                'id_usuario' => $currentUser->id_usuario,
                'fecha_hora' => Carbon::now(),
                'monto' => $montoTotal,
                'motivo' => $validated['motivo'],
                'estado' => 'ANULADO',
            ]);

            $pago->update([
                'estado' => 'ANULADO',
                'observaciones' => ($pago->observaciones ? $pago->observaciones.' | ' : '').'ANULADO: '.$validated['motivo'],
            ]);

            AuditService::log('ANULAR_COBRO', 'CAJA', [
                'id_pago' => $pago->id_pago,
                'monto' => $montoTotal,
                'motivo' => $validated['motivo'],
            ], $pago);

            return redirect()->route('caja.index')->with('success', "El Pago #{$pago->id_pago} ha sido anulado correctamente.");
        });
    }

    /**
     * Listado de cuentas y deudas de propietarios.
     */
    public function deudas(Request $request): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        if (! $currentUser->canAccessModule('caja')) {
            abort(403, 'No tienes permisos para acceder al módulo de caja.');
        }

        $cuentas = Cuenta::with(['propietario.persona', 'cargos', 'pagos.detallePagos'])
            ->paginate(15);

        return view('caja.deudas', compact('cuentas'));
    }
}
