@extends('layouts.app')

@section('title', 'Historial de Movimientos de Inventario - La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    openModalMovimiento: false,
    movimientoData: {
        id_producto: '',
        tipo_movimiento: 'ENTRADA',
        cantidad: '',
        motivo: 'AJUSTE',
        numero_lote: '',
        fecha_vencimiento: ''
    }
}">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <a href="{{ route('inventario.index') }}" class="hover:text-primary-600 transition-colors">Inventario Médico</a>
                <i data-lucide="chevron-right" class="size-3"></i>
                <span class="text-primary-600 font-semibold">Historial de Movimientos</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Movimientos de Stock</h1>
            <p class="text-sm text-slate-500">Trazabilidad detallada de entradas, salidas, bajas y ajustes de productos médicos.</p>
        </div>

        <div class="flex items-center gap-2.5">
            @if ($canEdit)
                <button
                    type="button"
                    @click="openModalMovimiento = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors"
                >
                    <i data-lucide="plus" class="size-4"></i>
                    <span>Nuevo Movimiento</span>
                </button>
            @endif
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
                class="flex items-center gap-2 pb-3 text-sm font-semibold border-b-2 border-primary-600 text-primary-600"
            >
                <i data-lucide="arrow-left-right" class="size-4"></i>
                <span>Historial de Movimientos</span>
            </a>
            <a
                href="{{ route('inventario.alertas') }}"
                class="flex items-center gap-2 pb-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors"
            >
                <i data-lucide="alert-circle" class="size-4"></i>
                <span>Panel de Alertas</span>
            </a>
        </nav>
    </div>

    <!-- Filtros de Movimientos -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('inventario.movimientos') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4">
                <select
                    name="id_producto"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Todos los Productos</option>
                    @foreach ($productos as $prod)
                        <option value="{{ $prod->id_producto }}" {{ request('id_producto') == $prod->id_producto ? 'selected' : '' }}>
                            {{ $prod->nombre }} ({{ $prod->marca ?? 'Genérico' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <select
                    name="tipo_movimiento"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Tipo de Movimiento</option>
                    <option value="ENTRADA" {{ request('tipo_movimiento') === 'ENTRADA' ? 'selected' : '' }}>Entradas (+)</option>
                    <option value="SALIDA" {{ request('tipo_movimiento') === 'SALIDA' ? 'selected' : '' }}>Salidas (-)</option>
                </select>
            </div>

            <div class="sm:col-span-3">
                <select
                    name="motivo"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Todos los Motivos</option>
                    <option value="COMPRA" {{ request('motivo') === 'COMPRA' ? 'selected' : '' }}>Compra / Reabastecimiento</option>
                    <option value="USO_ATENCION" {{ request('motivo') === 'USO_ATENCION' ? 'selected' : '' }}>Uso en Atención</option>
                    <option value="VENTA" {{ request('motivo') === 'VENTA' ? 'selected' : '' }}>Venta Directa</option>
                    <option value="AJUSTE" {{ request('motivo') === 'AJUSTE' ? 'selected' : '' }}>Ajuste de Inventario</option>
                    <option value="BAJA_VENCIMIENTO" {{ request('motivo') === 'BAJA_VENCIMIENTO' ? 'selected' : '' }}>Baja por Vencimiento</option>
                    <option value="BAJA_DANO" {{ request('motivo') === 'BAJA_DANO' ? 'selected' : '' }}>Baja por Daño</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="w-full rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors shadow-sm"
                >
                    Filtrar
                </button>
                @if(request()->hasAny(['id_producto', 'tipo_movimiento', 'motivo']))
                    <a
                        href="{{ route('inventario.movimientos') }}"
                        class="rounded-xl border border-slate-200 px-2.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                        title="Limpiar filtros"
                    >
                        <i data-lucide="rotate-ccw" class="size-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Movimientos -->
    <div class="rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3.5 px-4">Fecha y Hora</th>
                        <th class="py-3.5 px-4">Producto</th>
                        <th class="py-3.5 px-3">Lote</th>
                        <th class="py-3.5 px-3 text-center">Tipo</th>
                        <th class="py-3.5 px-3 text-right">Cantidad</th>
                        <th class="py-3.5 px-4">Motivo</th>
                        <th class="py-3.5 px-4">Responsable</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($movimientos as $mov)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap font-medium text-slate-800">
                                {{ \Carbon\Carbon::parse($mov->fecha_hora)->format('d/m/Y H:i') }}
                            </td>

                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900 text-sm">
                                    {{ $mov->producto->nombre ?? 'Producto no disponible' }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $mov->producto->marca ?? 'Genérico' }} · {{ $mov->producto->unidad_medida ?? '' }}
                                </div>
                            </td>

                            <td class="py-3.5 px-3 whitespace-nowrap">
                                @if ($mov->lote)
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 text-[11px] font-mono text-slate-700">
                                        {{ $mov->lote->numero_lote }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">—</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                @if ($mov->tipo_movimiento === 'ENTRADA')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 border border-emerald-200">
                                        <i data-lucide="arrow-down-left" class="size-3"></i>
                                        ENTRADA
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-0.5 text-[11px] font-bold text-rose-700 border border-rose-200">
                                        <i data-lucide="arrow-up-right" class="size-3"></i>
                                        SALIDA
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-right whitespace-nowrap font-semibold">
                                <span class="{{ $mov->tipo_movimiento === 'ENTRADA' ? 'text-emerald-700' : 'text-rose-700' }}">
                                    {{ $mov->tipo_movimiento === 'ENTRADA' ? '+' : '-' }}{{ number_format(abs($mov->cantidad), 2) }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800">
                                    {{ str_replace('_', ' ', $mov->motivo) }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 whitespace-nowrap text-slate-700">
                                {{ $mov->usuario->persona->nombre_completo ?? ($mov->usuario->nombre_usuario ?? 'Sistema') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                                    <i data-lucide="activity" class="size-6"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900">No hay movimientos registrados</h3>
                                <p class="text-xs text-slate-500 mt-1">Registra una entrada, salida o ajuste para ver el historial.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($movimientos->hasPages())
            <div class="border-t border-slate-200/80 px-4 py-3">
                {{ $movimientos->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: REGISTRAR MOVIMIENTO -->
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
                        <h3 class="text-base font-bold text-slate-900">Registrar Movimiento de Inventario</h3>
                        <p class="text-xs text-slate-500">Selecciona el producto y tipo de ajuste a realizar.</p>
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Producto *</label>
                        <select
                            name="id_producto"
                            x-model="movimientoData.id_producto"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                        >
                            <option value="">Selecciona un producto...</option>
                            @foreach ($productos as $prod)
                                <option value="{{ $prod->id_producto }}">
                                    {{ $prod->nombre }} ({{ $prod->marca ?? 'Genérico' }}) - {{ $prod->unidad_medida }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Movimiento *</label>
                        <select
                            name="tipo_movimiento"
                            x-model="movimientoData.tipo_movimiento"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                        >
                            <option value="ENTRADA">Entrada (+) Aumentar Stock</option>
                            <option value="SALIDA">Salida (-) Disminuir Stock</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cantidad *</label>
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

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo *</label>
                        <select
                            name="motivo"
                            x-model="movimientoData.motivo"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                        >
                            <option value="AJUSTE">Ajuste manual / Conteo físico</option>
                            <option value="COMPRA">Compra / Reabastecimiento</option>
                            <option value="USO_ATENCION">Uso en Atención</option>
                            <option value="BAJA_VENCIMIENTO">Baja por Vencimiento</option>
                            <option value="BAJA_DANO">Baja por Daño / Pérdida</option>
                        </select>
                    </div>

                    <div x-show="movimientoData.tipo_movimiento === 'ENTRADA'" class="grid grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">N° Lote (Opcional)</label>
                            <input
                                type="text"
                                name="numero_lote"
                                placeholder="Ej. LOT-2026-B"
                                class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-900"
                            >
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Vencimiento</label>
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
                            Guardar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
