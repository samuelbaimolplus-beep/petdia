@extends('layouts.app')

@section('title', 'Alertas de Inventario Médico - La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    openModalMovimiento: false,
    movimientoData: {
        id_producto: '',
        nombre: '',
        tipo_movimiento: 'ENTRADA',
        cantidad: '',
        motivo: 'COMPRA'
    },
    quickRestock(id, name) {
        this.movimientoData.id_producto = id;
        this.movimientoData.nombre = name;
        this.openModalMovimiento = true;
    }
}">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('inventario.index') }}" class="hover:text-primary-600 transition-colors">Inventario Médico</a>
                <i data-lucide="chevron-right" class="size-3"></i>
                <span class="text-primary-600 font-semibold">Panel de Alertas</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Alertas de Inventario</h1>
            <p class="text-sm text-slate-500">Monitoreo crítico de productos por agotarse y lotes con caducidad próxima.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('inventario.index') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shadow-sm"
            >
                <i data-lucide="arrow-left" class="size-4"></i>
                <span>Volver a Catálogo</span>
            </a>
        </div>
    </div>

    <!-- Navegación por Pestañas -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6">
            <a
                href="{{ route('inventario.index') }}"
                class="flex items-center gap-2 pb-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors"
            >
                <i data-lucide="boxes" class="size-4"></i>
                <span>Catálogo e Inventario</span>
            </a>
            <a
                href="{{ route('inventario.movimientos') }}"
                class="flex items-center gap-2 pb-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors"
            >
                <i data-lucide="arrow-left-right" class="size-4"></i>
                <span>Historial de Movimientos</span>
            </a>
            <a
                href="{{ route('inventario.alertas') }}"
                class="flex items-center gap-2 pb-3 text-sm font-semibold border-b-2 border-primary-600 text-primary-600"
            >
                <i data-lucide="alert-circle" class="size-4"></i>
                <span>Panel de Alertas</span>
            </a>
        </nav>
    </div>

    <!-- Contenedor de 2 Paneles de Alertas -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- PANEL 1: STOCK BAJO -->
        <div class="rounded-2xl border border-amber-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
            <div class="border-b border-amber-100 bg-amber-50/50 p-4 sm:p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-amber-100 p-2 text-amber-700">
                        <i data-lucide="alert-triangle" class="size-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Productos con Stock Bajo o Agotado</h2>
                        <p class="text-xs text-slate-500">Cantidad actual menor o igual al umbral mínimo configurado.</p>
                    </div>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-800">
                    {{ $productosStockBajo->count() }}
                </span>
            </div>

            <div class="p-4 sm:p-5 flex-1 divide-y divide-slate-100 overflow-y-auto max-h-[550px]">
                @forelse ($productosStockBajo as $prod)
                    @php
                        $stockActual = (float) ($prod->stock_calculado ?? 0);
                        $stockMinimo = (float) $prod->stock_minimo;
                        $esAgotado = $stockActual <= 0;
                    @endphp
                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-slate-900 text-sm truncate">{{ $prod->nombre }}</h3>
                                <span class="rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $prod->tipo === 'MEDICAMENTO' ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $prod->tipo }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $prod->marca ?? 'Genérico' }} · Unidad: {{ $prod->unidad_medida }}
                            </p>
                            <div class="flex items-center gap-3 mt-1.5 text-xs">
                                <span class="font-medium {{ $esAgotado ? 'text-rose-600 font-bold' : 'text-amber-700 font-bold' }}">
                                    Actual: {{ number_format($stockActual, 1) }}
                                </span>
                                <span class="text-slate-400">·</span>
                                <span class="text-slate-500">
                                    Mínimo: {{ number_format($stockMinimo, 1) }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if ($esAgotado)
                                <span class="rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                                    Agotado
                                </span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                    Crítico
                                </span>
                            @endif

                            @if ($canEdit)
                                <button
                                    type="button"
                                    @click="quickRestock({{ $prod->id_producto }}, @js($prod->nombre))"
                                    class="rounded-xl border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-primary-600 transition-colors shadow-2xs"
                                    title="Registrar reabastecimiento"
                                >
                                    <i data-lucide="plus" class="size-3.5"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                            <i data-lucide="check-circle" class="size-6"></i>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900">Todo el stock en niveles óptimos</h4>
                        <p class="text-xs text-slate-500 mt-1">No hay productos por debajo del stock mínimo configurado.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- PANEL 2: PRÓXIMOS A VENCER / VENCIDOS -->
        <div class="rounded-2xl border border-rose-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
            <div class="border-b border-rose-100 bg-rose-50/50 p-4 sm:p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="rounded-xl bg-rose-100 p-2 text-rose-700">
                        <i data-lucide="clock" class="size-5"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Lotes Próximos a Vencer o Caducados</h2>
                        <p class="text-xs text-slate-500">Caducan en los próximos 30 días o ya han superado su fecha límite.</p>
                    </div>
                </div>
                <span class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-800">
                    {{ $lotesAlertas->count() }}
                </span>
            </div>

            <div class="p-4 sm:p-5 flex-1 divide-y divide-slate-100 overflow-y-auto max-h-[550px]">
                @forelse ($lotesAlertas as $lote)
                    @php
                        $expDate = \Carbon\Carbon::parse($lote->fecha_vencimiento);
                        $isPast = $expDate->isPast();
                        $daysDiff = (int) now()->diffInDays($expDate, false);
                    @endphp
                    <div class="py-3.5 first:pt-0 last:pb-0 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-900 text-sm truncate">
                                {{ $lote->producto->nombre ?? 'Producto no disponible' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Lote: <span class="font-mono font-medium text-slate-700">{{ $lote->numero_lote }}</span>
                                · {{ $lote->producto->marca ?? 'Genérico' }}
                            </p>
                            <p class="text-xs text-slate-600 mt-1 font-medium">
                                Fecha de vencimiento: {{ $expDate->format('d/m/Y') }}
                            </p>
                        </div>

                        <div class="text-right shrink-0">
                            @if ($isPast)
                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-800 border border-rose-200">
                                    <i data-lucide="x-circle" class="size-3.5"></i>
                                    Vencido
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 border border-amber-200">
                                    <i data-lucide="clock" class="size-3.5"></i>
                                    Quedan {{ $daysDiff }} días
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                            <i data-lucide="shield-check" class="size-6"></i>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900">Sin caducidades inmediatas</h4>
                        <p class="text-xs text-slate-500 mt-1">Ningún lote tiene fecha de vencimiento dentro de los próximos 30 días.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- MODAL: REGISTRAR REABASTECIMIENTO RÁPIDO -->
    @if ($canEdit)
        <div
            x-show="openModalMovimiento"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4"
        >
            <div
                @click.away="openModalMovimiento = false"
                class="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Reabastecer Producto</h3>
                        <p class="text-xs text-slate-500" x-text="movimientoData.nombre"></p>
                    </div>
                    <button
                        type="button"
                        @click="openModalMovimiento = false"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('inventario.movimientos.store') }}" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="id_producto" :value="movimientoData.id_producto">
                    <input type="hidden" name="tipo_movimiento" value="ENTRADA">
                    <input type="hidden" name="motivo" value="COMPRA">

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cantidad a ingresar *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="cantidad"
                            x-model="movimientoData.cantidad"
                            required
                            placeholder="0.00"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">N° Lote (Opcional)</label>
                            <input
                                type="text"
                                name="numero_lote"
                                placeholder="Ej. LOT-2026-X"
                                class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-900"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Fecha Vencimiento</label>
                            <input
                                type="date"
                                name="fecha_vencimiento"
                                class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-900"
                            >
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            @click="openModalMovimiento = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors"
                        >
                            Ingresar al Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
