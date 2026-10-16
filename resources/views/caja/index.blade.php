@extends('layouts.app')

@section('title', 'Caja y Cobros — La Petdiatría')

@section('content')
    <div class="space-y-6" x-data="{
        anularModalOpen: false,
        reciboModalOpen: false,
        egresoModalOpen: false,
        pagoSeleccionado: null,
        motivoAnulacion: '',
        anularUrl: '',
        
        abrirAnular(pago) {
            this.pagoSeleccionado = pago;
            this.motivoAnulacion = '';
            this.anularUrl = '/caja/pagos/' + pago.id_pago + '/anular';
            this.anularModalOpen = true;
        },

        verRecibo(pago) {
            this.pagoSeleccionado = pago;
            this.reciboModalOpen = true;
        }
    }">

        <!-- Encabezado de la página -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Caja y Cobros</h1>
                <p class="text-sm text-slate-500 mt-1">Control de ingresos, cobro de atenciones, egresos de caja chica y arqueo.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a
                    href="{{ route('caja.deudas') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                >
                    <i data-lucide="book-open" class="size-4 text-slate-500"></i>
                    <span>Cuentas por Cobrar</span>
                </a>

                <a
                    href="{{ route('caja.cierre') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                >
                    <i data-lucide="calculator" class="size-4 text-slate-500"></i>
                    <span>Arqueo y Cierre</span>
                </a>

                <button
                    type="button"
                    @click="egresoModalOpen = true"
                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-sm font-semibold text-rose-700 shadow-2xs hover:bg-rose-100 hover:border-rose-300 transition-colors"
                >
                    <i data-lucide="arrow-up-right" class="size-4 text-rose-600"></i>
                    <span>Registrar Egreso</span>
                </button>

                <a
                    href="{{ route('caja.cobro') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
                >
                    <i data-lucide="plus" class="size-4"></i>
                    <span>Nuevo Cobro</span>
                </a>
            </div>
        </div>

        <!-- 5 Tarjetas KPI -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <!-- Ingresos Hoy -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ingresos Hoy</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                        <i data-lucide="trending-up" class="size-5"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight text-emerald-700">${{ number_format($ingresosHoy, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Cobrado en la jornada</p>
            </div>

            <!-- Egresos Hoy -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Egresos Hoy</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-rose-50 text-rose-600">
                        <i data-lucide="trending-down" class="size-5"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight text-rose-700">${{ number_format($egresosHoy, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Gastos caja chica</p>
            </div>

            <!-- Saldo Neto Hoy -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Saldo Neto</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                        <i data-lucide="wallet" class="size-5"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">${{ number_format($saldoNetoHoy, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Ingresos - Egresos</p>
            </div>

            <!-- Atenciones por Cobrar -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Por Cobrar</p>
                    <div class="flex size-9 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="clock" class="size-5"></i>
                    </div>
                </div>
                <p class="mt-3 text-2xl font-bold tracking-tight text-slate-900">{{ $atencionesPendientesCount }}</p>
                <p class="mt-1 text-xs text-slate-500">Finalizadas sin liquidar</p>
            </div>

            <!-- Estado de Caja -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Estado de Caja</p>
                    <div class="flex size-9 items-center justify-center rounded-lg {{ $cierreHoy ? 'bg-slate-100 text-slate-600' : 'bg-primary-50 text-primary-600' }}">
                        <i data-lucide="vault" class="size-5"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-center gap-2">
                    @if ($cierreHoy)
                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                            Cerrada
                        </span>
                        <span class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($cierreHoy->fecha_hora_cierre)->format('H:i') }}</span>
                    @else
                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                            Abierta
                        </span>
                        <span class="text-xs text-slate-500">{{ ($hayPagosSinCierre || $hayEgresosSinCierre) ? 'Movimientos activos' : 'Sin movimientos' }}</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-slate-500">Turno de caja activo</p>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
            <form action="{{ route('caja.index') }}" method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <!-- Buscador de texto -->
                <div class="relative flex-1">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Buscar por recibo #, cliente, mascota o CI..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-ring"
                    />
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Filtro Método de Pago -->
                    <select
                        name="id_metodo_pago"
                        class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                    >
                        <option value="">Todos los métodos</option>
                        @foreach ($metodosPago as $metodo)
                            <option value="{{ $metodo->id_metodo_pago }}" {{ request('id_metodo_pago') == $metodo->id_metodo_pago ? 'selected' : '' }}>
                                {{ $metodo->nombre }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filtro Estado -->
                    <select
                        name="estado"
                        class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                    >
                        <option value="">Todos los estados</option>
                        <option value="COMPLETADO" {{ request('estado') === 'COMPLETADO' ? 'selected' : '' }}>Completado</option>
                        <option value="ANULADO" {{ request('estado') === 'ANULADO' ? 'selected' : '' }}>Anulado</option>
                    </select>

                    <!-- Filtro Fecha Desde -->
                    <input
                        type="date"
                        name="fecha_desde"
                        value="{{ request('fecha_desde') }}"
                        class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                        title="Fecha desde"
                    />

                    <!-- Filtro Fecha Hasta -->
                    <input
                        type="date"
                        name="fecha_hasta"
                        value="{{ request('fecha_hasta') }}"
                        class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                        title="Fecha hasta"
                    />

                    <button
                        type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                    >
                        Filtrar
                    </button>

                    @if (request()->hasAny(['search', 'id_metodo_pago', 'estado', 'fecha_desde', 'fecha_hasta']))
                        <a
                            href="{{ route('caja.index') }}"
                            class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Limpiar
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de Historial de Pagos -->
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3.5">Recibo</th>
                            <th class="px-4 py-3.5">Fecha y Hora</th>
                            <th class="px-4 py-3.5">Cliente / Paciente</th>
                            <th class="px-4 py-3.5">Concepto</th>
                            <th class="px-4 py-3.5">Método de Pago</th>
                            <th class="px-4 py-3.5 text-right">Monto Total</th>
                            <th class="px-4 py-3.5 text-center">Estado</th>
                            <th class="px-5 py-3.5 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pagos as $pago)
                            @php
                                $cliente = $pago->cuenta?->propietario?->persona;
                                $atencion = $pago->pagoAtencions->first()?->atencion;
                                $mascota = $atencion?->paciente;
                                $total = $pago->monto_total;
                                $esAnulado = ($pago->estado === 'ANULADO');
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors {{ $esAnulado ? 'opacity-60 bg-slate-50/40' : '' }}">
                                <!-- Recibo -->
                                <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">
                                    #REC-{{ str_pad($pago->id_pago, 5, '0', STR_PAD_LEFT) }}
                                </td>

                                <!-- Fecha y Hora -->
                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                    {{ $pago->fecha_hora ? \Carbon\Carbon::parse($pago->fecha_hora)->format('d/m/Y H:i') : 'N/A' }}
                                </td>

                                <!-- Cliente / Paciente -->
                                <td class="px-4 py-3.5">
                                    <div class="font-medium text-slate-900 text-xs">
                                        {{ $cliente ? $cliente->nombre . ' ' . $cliente->apellido1 : 'Cliente Mostrador' }}
                                    </div>
                                    @if ($mascota)
                                        <div class="text-[11px] text-primary-600 font-medium">
                                            🐾 {{ $mascota->nombre }}
                                        </div>
                                    @endif
                                </td>

                                <!-- Concepto -->
                                <td class="px-4 py-3.5 text-xs text-slate-600 max-w-xs truncate" title="{{ $pago->observaciones }}">
                                    {{ $pago->observaciones ?? ($atencion ? 'Atención #' . $atencion->id_atencion : 'Venta / Cobro de Caja') }}
                                </td>

                                <!-- Método de Pago -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse ($pago->detallePagos as $detalle)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700">
                                                <i data-lucide="credit-card" class="size-3 text-slate-400"></i>
                                                {{ $detalle->metodoPago?->nombre ?? 'OTRO' }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">Sin detalle</span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Monto Total -->
                                <td class="px-4 py-3.5 text-right font-mono text-sm font-bold {{ $esAnulado ? 'line-through text-slate-400' : 'text-slate-900' }}">
                                    ${{ number_format($total, 2) }}
                                </td>

                                <!-- Estado -->
                                <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                    @if ($esAnulado)
                                        <span class="inline-flex items-center rounded-full bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                            Anulado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            Completado
                                        </span>
                                    @endif
                                </td>

                                <!-- Acciones -->
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- Ver Recibo -->
                                        @php
                                            $datosRecibo = [
                                                'id_pago' => $pago->id_pago,
                                                'recibo_num' => '#REC-' . str_pad($pago->id_pago, 5, '0', STR_PAD_LEFT),
                                                'fecha' => $pago->fecha_hora ? \Carbon\Carbon::parse($pago->fecha_hora)->format('d/m/Y H:i') : 'N/A',
                                                'cliente' => $cliente ? ($cliente->nombre . ' ' . $cliente->apellido1) : 'Cliente Mostrador',
                                                'documento' => $cliente?->documento ?? 'S/D',
                                                'mascota' => $mascota?->nombre ?? 'N/A',
                                                'concepto' => $pago->observaciones ?? ($atencion ? 'Atención #' . $atencion->id_atencion : 'Cobro en caja'),
                                                'total' => number_format($total, 2),
                                                'estado' => $pago->estado ?? 'COMPLETADO',
                                                'detalles' => $pago->detallePagos->map(fn($d) => [
                                                    'metodo' => $d->metodoPago?->nombre ?? 'OTRO',
                                                    'monto' => number_format((float)$d->monto, 2),
                                                    'referencia' => $d->referencia ?? '—',
                                                ])->values()->all(),
                                            ];
                                            $datosAnular = [
                                                'id_pago' => $pago->id_pago,
                                                'recibo_num' => '#REC-' . str_pad($pago->id_pago, 5, '0', STR_PAD_LEFT),
                                                'total' => number_format($total, 2),
                                                'cliente' => $cliente ? ($cliente->nombre . ' ' . $cliente->apellido1) : 'Cliente Mostrador',
                                            ];
                                        @endphp
                                        <a
                                            href="{{ route('caja.comprobante', $pago->id_pago) }}"
                                            target="_blank"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                                            title="Imprimir / Descargar Comprobante PDF"
                                        >
                                            <i data-lucide="printer" class="size-4"></i>
                                        </a>

                                        <button
                                            type="button"
                                            data-recibo="{{ json_encode($datosRecibo) }}"
                                            @click="verRecibo(JSON.parse($el.dataset.recibo))"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                                            title="Ver comprobante en pantalla"
                                        >
                                            <i data-lucide="file-text" class="size-4"></i>
                                        </button>

                                        <!-- Anular Pago -->
                                        @if (! $esAnulado)
                                            <button
                                                type="button"
                                                data-anular="{{ json_encode($datosAnular) }}"
                                                @click="abrirAnular(JSON.parse($el.dataset.anular))"
                                                class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors"
                                                title="Anular pago"
                                            >
                                                <i data-lucide="ban" class="size-4"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <x-empty-state
                                        title="No hay pagos registrados"
                                        description="No se encontraron transacciones con los filtros seleccionados."
                                    >
                                        <x-slot:icon>
                                            <i data-lucide="credit-card" class="size-8 text-slate-300"></i>
                                        </x-slot:icon>
                                    </x-empty-state>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if ($pagos->hasPages())
                <div class="border-t border-slate-100 px-5 py-3">
                    {{ $pagos->links() }}
                </div>
            @endif
        </div>

        <!-- Tabla de Egresos de Caja Chica -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-xs overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="trending-down" class="size-4 text-rose-500"></i>
                        Egresos de Caja Chica Recientes
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Gastos operativos menores y salidas de efectivo registradas</p>
                </div>
                <button
                    type="button"
                    @click="egresoModalOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition-colors"
                >
                    <i data-lucide="plus" class="size-3.5"></i>
                    <span>Nuevo Egreso</span>
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Fecha y Hora</th>
                            <th class="px-5 py-3">Concepto</th>
                            <th class="px-5 py-3">Categoría</th>
                            <th class="px-5 py-3">Comprobante</th>
                            <th class="px-5 py-3">Registrado por</th>
                            <th class="px-5 py-3">Estado</th>
                            <th class="px-5 py-3 text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                        @forelse ($egresosRecientes as $egreso)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3 whitespace-nowrap text-slate-700 font-medium">
                                    {{ \Carbon\Carbon::parse($egreso->fecha_hora)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-3 max-w-xs truncate font-semibold text-slate-900">
                                    {{ $egreso->concepto }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-700">
                                        {{ $egreso->categoria }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-500 font-mono">
                                    {{ $egreso->comprobante ?? '—' }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap text-slate-600">
                                    {{ $egreso->usuario->nombre ?? 'Sistema' }}
                                </td>
                                <td class="px-5 py-3 whitespace-nowrap">
                                    @if ($egreso->id_cierre)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                            Liquidado en Cierre #{{ $egreso->id_cierre }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700">
                                            Pendiente de Cierre
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-rose-600 whitespace-nowrap">
                                    -${{ number_format($egreso->monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-xs text-slate-400">
                                    No hay egresos de caja chica registrados recientemente.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal de Visualización de Recibo / Comprobante -->
        <div
            x-show="reciboModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="reciboModalOpen = false"
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="reciboModalOpen = false"></div>

                <div
                    class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                    x-show="reciboModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                >
                    <!-- Encabezado del Recibo -->
                    <div class="text-center pb-4 border-b border-dashed border-slate-200">
                        <div class="flex size-11 items-center justify-center rounded-xl bg-primary-50 text-primary-600 mx-auto mb-2">
                            <i data-lucide="paw-print" class="size-6"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">La Petdiatría</h3>
                        <p class="text-xs text-slate-500">Comprobante de Pago Oficial</p>
                        <p class="font-mono text-xs font-bold text-primary-700 mt-1" x-text="pagoSeleccionado?.recibo_num"></p>
                    </div>

                    <!-- Datos del Recibo -->
                    <div class="mt-4 space-y-2 text-xs">
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Fecha y Hora:</span>
                            <span class="font-medium text-slate-800" x-text="pagoSeleccionado?.fecha"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Cliente:</span>
                            <span class="font-medium text-slate-800" x-text="pagoSeleccionado?.cliente"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Documento / CI:</span>
                            <span class="font-medium text-slate-800" x-text="pagoSeleccionado?.documento"></span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-slate-100">
                            <span class="text-slate-500">Paciente:</span>
                            <span class="font-medium text-slate-800" x-text="pagoSeleccionado?.mascota"></span>
                        </div>
                        <div class="py-1">
                            <span class="text-slate-500">Concepto:</span>
                            <p class="mt-0.5 font-medium text-slate-800 bg-slate-50 p-2 rounded-lg" x-text="pagoSeleccionado?.concepto"></p>
                        </div>
                    </div>

                    <!-- Desglose Métodos de Pago -->
                    <div class="mt-4">
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Forma de Pago</p>
                        <template x-for="item in pagoSeleccionado?.detalles" :key="item.metodo">
                            <div class="flex justify-between text-xs py-1 border-b border-slate-100">
                                <span class="font-medium text-slate-700" x-text="item.metodo + (item.referencia !== '—' ? ' (Ref: ' + item.referencia + ')' : '')"></span>
                                <span class="font-mono font-bold text-slate-900" x-text="'$' + item.monto"></span>
                            </div>
                        </template>
                    </div>

                    <!-- Total Recibo -->
                    <div class="mt-4 pt-3 border-t-2 border-slate-900 flex justify-between items-center">
                        <span class="text-sm font-bold text-slate-900 uppercase">Total Pagado:</span>
                        <span class="font-mono text-xl font-bold text-slate-900" x-text="'$' + pagoSeleccionado?.total"></span>
                    </div>

                    <!-- Botón de Cerrar / Imprimir -->
                    <div class="mt-6 flex items-center justify-end gap-2">
                        <a
                            :href="'/caja/pagos/' + pagoSeleccionado?.id_pago + '/comprobante'"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors"
                        >
                            <i data-lucide="file-down" class="size-3.5"></i>
                            <span>Descargar PDF</span>
                        </a>
                        <button
                            type="button"
                            onclick="window.print()"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50"
                        >
                            <i data-lucide="printer" class="size-3.5"></i>
                            <span>Imprimir</span>
                        </button>
                        <button
                            type="button"
                            @click="reciboModalOpen = false"
                            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                        >
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Anulación (Acción Destructiva) -->
        <div
            x-show="anularModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="anularModalOpen = false"
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="anularModalOpen = false"></div>

                <div
                    class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                    x-show="anularModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                >
                    <div class="flex size-11 items-center justify-center rounded-xl bg-rose-50 text-rose-600 mb-4">
                        <i data-lucide="alert-triangle" class="size-6"></i>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">¿Anular comprobante de pago?</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Esta acción anulará el recibo <strong class="text-slate-800" x-text="pagoSeleccionado?.recibo_num"></strong> por valor de <strong class="text-slate-800" x-text="'$' + pagoSeleccionado?.total"></strong> y registrará una devolución en el sistema contable.
                    </p>

                    <form :action="anularUrl" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo de la anulación <span class="text-rose-500">*</span></label>
                            <textarea
                                name="motivo"
                                x-model="motivoAnulacion"
                                required
                                rows="3"
                                placeholder="Indica el motivo de la anulación o devolución..."
                                class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                            ></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button
                                type="button"
                                @click="anularModalOpen = false"
                                class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                :disabled="!motivoAnulacion.trim()"
                                class="rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 disabled:opacity-50 transition-colors shadow-xs"
                            >
                                Confirmar Anulación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Registrar Egreso de Caja Chica -->
        <div
            x-show="egresoModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="egresoModalOpen = false"
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="egresoModalOpen = false"></div>

                <div
                    class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                    x-show="egresoModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                >
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="flex size-10 items-center justify-center rounded-xl bg-rose-50 text-rose-600">
                                <i data-lucide="trending-down" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Registrar Egreso de Caja Chica</h3>
                                <p class="text-xs text-slate-500">Salida de efectivo autorizada para gastos operativos</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            @click="egresoModalOpen = false"
                            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        >
                            <i data-lucide="x" class="size-4"></i>
                        </button>
                    </div>

                    <form action="{{ route('caja.egresos.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Monto ($) <span class="text-rose-500">*</span></label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">$</span>
                                <input
                                    type="number"
                                    name="monto"
                                    step="0.01"
                                    min="0.01"
                                    required
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 pl-8 pr-3 py-2 text-sm text-slate-900 font-bold focus-ring"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría <span class="text-rose-500">*</span></label>
                            <select
                                name="categoria"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus-ring"
                            >
                                <option value="Insumos y Limpieza">Insumos y Limpieza</option>
                                <option value="Farmacia y Material Menor">Farmacia y Material Menor</option>
                                <option value="Servicios y Mantenimiento">Servicios y Mantenimiento</option>
                                <option value="Viáticos y Transporte">Viáticos y Transporte</option>
                                <option value="Caja / Vuelto">Caja / Vuelto</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Concepto / Motivo <span class="text-rose-500">*</span></label>
                            <textarea
                                name="concepto"
                                required
                                rows="2"
                                placeholder="Describe el detalle o justificación del gasto..."
                                class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">N° Comprobante / Recibo de respaldo (opcional)</label>
                            <input
                                type="text"
                                name="comprobante"
                                placeholder="Ej: Factura 001-234, Vale de caja..."
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                            />
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button
                                type="button"
                                @click="egresoModalOpen = false"
                                class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-700 transition-colors shadow-xs"
                            >
                                <i data-lucide="check" class="size-4"></i>
                                <span>Guardar Egreso</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection
