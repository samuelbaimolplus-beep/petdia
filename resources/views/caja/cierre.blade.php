@extends('layouts.app')

@section('title', 'Arqueo y Cierre de Caja — La Petdiatría')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
        totalSistema: {{ (float) ($saldoNetoEsperado ?? $totalRegistrado) }},
        totalFisico: '{{ (float) ($saldoNetoEsperado ?? $totalRegistrado) }}',
        confirmarModalOpen: false,

        get diferencia() {
            const fisico = parseFloat(this.totalFisico) || 0;
            return fisico - this.totalSistema;
        }
    }">

        <!-- Encabezado con breadcrumb -->
        <div class="flex items-center justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-400">
                    <a href="{{ route('caja.index') }}" class="hover:text-primary-600 transition-colors">Caja y Cobros</a>
                    <span>/</span>
                    <span class="text-slate-600 font-medium">Arqueo y Cierre</span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Arqueo y Cierre de Caja</h1>
                <p class="text-sm text-slate-500">Cuadre de movimientos del turno y liquidación final del saldo.</p>
            </div>
            <a
                href="{{ route('caja.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
                <span>Volver a Caja</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Columna Izquierda: Desglose por Método y Arqueo (2 cols) -->
            <div class="md:col-span-2 space-y-6">

                <!-- Resumen de Movimientos del Turno -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                                <i data-lucide="scale" class="size-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Movimientos a Liquidar</h3>
                                <p class="text-xs text-slate-500">Transacciones registradas desde la apertura del turno</p>
                            </div>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700">
                            {{ $pagosSinCierre->count() }} transacciones
                        </span>
                    </div>

                    <!-- Datos del Turno -->
                    <div class="mt-4 grid grid-cols-2 gap-4 text-xs">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-slate-400">Inicio del Turno</p>
                            <p class="font-bold text-slate-800 text-sm mt-0.5">{{ $fechaApertura->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-slate-400">Cierre estimado</p>
                            <p class="font-bold text-slate-800 text-sm mt-0.5">{{ now()->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <!-- Tabla de Desglose por Método -->
                    <div class="mt-5 space-y-2">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ingresos: Desglose por Método de Pago</p>
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                            @forelse ($totalesPorMetodo as $item)
                                <div class="flex items-center justify-between p-3 text-xs hover:bg-slate-50/50">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="credit-card" class="size-4 text-slate-400"></i>
                                        <span class="font-semibold text-slate-800">{{ $item->metodoPago?->nombre ?? 'OTRO' }}</span>
                                        <span class="text-slate-400">({{ $item->transacciones }} ops)</span>
                                    </div>
                                    <span class="font-mono text-sm font-bold text-slate-900">${{ number_format((float)$item->subtotal, 2) }}</span>
                                </div>
                            @empty
                                <div class="p-4 text-center text-xs text-slate-400">
                                    No hay cobros pendientes de cierre en este momento.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Tabla de Egresos a Liquidar -->
                    @if (isset($egresosSinCierre) && $egresosSinCierre->count() > 0)
                        <div class="mt-5 space-y-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-rose-500">Egresos de Caja Chica a Liquidar ({{ $egresosSinCierre->count() }})</p>
                            <div class="divide-y divide-rose-100 border border-rose-100 bg-rose-50/20 rounded-xl overflow-hidden">
                                @foreach ($egresosSinCierre as $egreso)
                                    <div class="flex items-center justify-between p-3 text-xs">
                                        <div class="flex items-center gap-2">
                                            <i data-lucide="arrow-down-right" class="size-4 text-rose-500"></i>
                                            <div>
                                                <span class="font-semibold text-slate-800">{{ $egreso->concepto }}</span>
                                                <span class="text-[10px] text-slate-400 block">{{ $egreso->categoria }} · {{ \Carbon\Carbon::parse($egreso->fecha_hora)->format('H:i') }}</span>
                                            </div>
                                        </div>
                                        <span class="font-mono text-sm font-bold text-rose-600">-${{ number_format((float)$egreso->monto, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Resumen y Total Sistema Neto -->
                    <div class="mt-5 p-4 rounded-xl bg-slate-50 border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>Total Ingresos Recaudados:</span>
                            <span class="font-mono font-semibold text-emerald-700">+${{ number_format($totalRegistrado, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span>Total Egresos Caja Chica:</span>
                            <span class="font-mono font-semibold text-rose-600">-${{ number_format($totalEgresos ?? 0, 2) }}</span>
                        </div>
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between">
                            <div>
                                <span class="text-sm font-bold text-slate-900 uppercase">Saldo Neto Esperado en Caja:</span>
                                <p class="text-[11px] text-slate-500">Monto exacto de arqueo contra el que se compara el conteo físico</p>
                            </div>
                            <span class="font-mono text-2xl font-bold text-primary-700">${{ number_format($saldoNetoEsperado ?? $totalRegistrado, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Historial de Cierres Anteriores -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                    <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Últimos Cierres Registrados</h3>
                    <div class="mt-4 divide-y divide-slate-100 text-xs">
                        @forelse ($cierresAnteriores as $cierre)
                            <div class="py-3 flex items-center justify-between">
                                <div>
                                    <p class="font-bold text-slate-800">Cierre #{{ $cierre->id_cierre }} · {{ \Carbon\Carbon::parse($cierre->fecha_hora_cierre)->format('d/m/Y H:i') }}</p>
                                    <p class="text-slate-500 mt-0.5">Responsable: {{ $cierre->usuario?->persona?->nombre ?? 'Usuario' }}</p>
                                    @if ($cierre->observaciones)
                                        <p class="text-slate-400 italic text-[11px] mt-0.5">{{ $cierre->observaciones }}</p>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="font-mono font-bold text-slate-900 text-sm">${{ number_format((float)$cierre->total_registrado, 2) }}</span>
                                    <span class="block text-[10px] text-emerald-600 font-semibold">Cerrado</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-slate-400 text-center py-3">No hay cierres anteriores registrados.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Columna Derecha: Formulario de Arqueo Físico (1 col) -->
            <div>
                <div class="sticky top-20 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-5">
                    <h3 class="text-base font-bold text-slate-900 border-b border-slate-100 pb-3">Arqueo Físico</h3>

                    <form id="formCierre" action="{{ route('caja.cierre.store') }}" method="POST" class="space-y-4">
                        @csrf

                        <!-- Total Físico Contado -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                Efectivo y Valores Físicos Contados ($) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="total_fisico"
                                x-model="totalFisico"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-base font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>

                        <!-- Estado del Cuadre (Alpine) -->
                        <div class="rounded-xl p-3 text-xs" :class="{
                            'bg-emerald-50 text-emerald-800 border border-emerald-200': Math.abs(diferencia) < 0.01,
                            'bg-amber-50 text-amber-800 border border-amber-200': diferencia > 0.01,
                            'bg-rose-50 text-rose-800 border border-rose-200': diferencia < -0.01
                        }">
                            <div class="flex items-center justify-between font-bold">
                                <span>Diferencia de Cuadre:</span>
                                <span class="font-mono text-sm" x-text="(diferencia >= 0 ? '+$' : '-$') + Math.abs(diferencia).toFixed(2)"></span>
                            </div>
                            <p class="mt-1 text-[11px]" x-show="Math.abs(diferencia) < 0.01">
                                ✔ Caja cuadrada con exactitud.
                            </p>
                            <p class="mt-1 text-[11px]" x-show="diferencia > 0.01">
                                ⚠ Hay un sobrante de dinero respecto al sistema.
                            </p>
                            <p class="mt-1 text-[11px]" x-show="diferencia < -0.01">
                                ⚠ Hay un faltante de dinero respecto al sistema.
                            </p>
                        </div>

                        <!-- Observaciones -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Observaciones del cierre</label>
                            <textarea
                                name="observaciones"
                                rows="3"
                                placeholder="Notas sobre el arqueo, incidencias o billetes..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                            ></textarea>
                        </div>

                        <!-- Botón de Cierre (Abre Modal de Confirmación) -->
                        <button
                            type="button"
                            @click="confirmarModalOpen = true"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-sm font-semibold text-white shadow-xs hover:bg-slate-800 transition-colors"
                        >
                            <i data-lucide="lock" class="size-4"></i>
                            <span>Proceder al Cierre Definitivo</span>
                        </button>
                    </form>

                    <p class="text-center text-[11px] text-slate-400">
                        Al cerrar la caja, los movimientos del turno quedarán bloqueados y vinculados al arqueo.
                    </p>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Cierre Definitivo (Acción Irreversible) -->
        <div
            x-show="confirmarModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            @keydown.escape.window="confirmarModalOpen = false"
        >
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="confirmarModalOpen = false"></div>

                <div
                    class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                    x-show="confirmarModalOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                >
                    <div class="flex size-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600 mb-4">
                        <i data-lucide="lock" class="size-6"></i>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900">¿Confirmar Cierre de Caja?</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Esta acción cerrará el turno actual por un total registrado de <strong class="text-slate-800">${{ number_format($totalRegistrado, 2) }}</strong>. Los pagos asociados pasarán a estado cerrado y no podrán ser modificados.
                    </p>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <button
                            type="button"
                            @click="confirmarModalOpen = false"
                            class="rounded-xl border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="button"
                            onclick="document.getElementById('formCierre').submit()"
                            class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors shadow-xs"
                        >
                            Sí, Cerrar Caja
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
