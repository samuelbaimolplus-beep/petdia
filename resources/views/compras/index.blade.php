@extends('layouts.app')

@section('title', 'Compras y Proveedores — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    detalleModalOpen: false,
    compraSeleccionada: null,

    verDetalle(compra) {
        this.compraSeleccionada = compra;
        this.detalleModalOpen = true;
    }
}">

    <!-- Encabezado de la página -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Compras y Proveedores</h1>
            <p class="text-sm text-slate-500 mt-1">Registro de órdenes de compra, abastecimiento de insumos e impacto en stock.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a
                href="{{ route('compras.proveedores') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="users" class="size-4 text-slate-500"></i>
                <span>Directorio Proveedores</span>
            </a>

            <a
                href="{{ route('compras.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
            >
                <i data-lucide="plus" class="size-4"></i>
                <span>Registrar Compra</span>
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Inversión Mes -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Inversión del Mes</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <i data-lucide="dollar-sign" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">${{ number_format($totalComprasMes, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Gasto total en compras este mes</p>
        </div>

        <!-- Órdenes Registradas -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Órdenes este Mes</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i data-lucide="shopping-bag" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $cantidadComprasMes }}</p>
            <p class="mt-1 text-xs text-slate-500">Ingresos de mercadería registrados</p>
        </div>

        <!-- Proveedores Activos -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Proveedores</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <i data-lucide="truck" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $proveedoresActivos }}</p>
            <p class="mt-1 text-xs text-slate-500">Empresas aliadas y laboratorios</p>
        </div>

        <!-- Última Compra -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Última Entrada</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <i data-lucide="clock" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-base font-bold text-slate-900">
                {{ $ultimaCompra ? '#COM-' . str_pad($ultimaCompra->id_compra, 4, '0', STR_PAD_LEFT) : 'Sin compras' }}
            </p>
            <p class="mt-1 text-xs text-slate-500">
                {{ $ultimaCompra && $ultimaCompra->fecha_hora ? \Carbon\Carbon::parse($ultimaCompra->fecha_hora)->format('d/m/Y') : 'N/A' }}
            </p>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form action="{{ route('compras.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por orden #, proveedor, NIT u observaciones..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-ring"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select
                    name="id_proveedor"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                >
                    <option value="">Todos los proveedores</option>
                    @foreach ($proveedores as $prov)
                        <option value="{{ $prov->id_proveedor }}" {{ request('id_proveedor') == $prov->id_proveedor ? 'selected' : '' }}>
                            {{ $prov->nombre }}
                        </option>
                    @endforeach
                </select>

                <input
                    type="date"
                    name="fecha"
                    value="{{ request('fecha') }}"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                />

                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                >
                    Filtrar
                </button>

                @if (request()->hasAny(['search', 'id_proveedor', 'fecha']))
                    <a
                        href="{{ route('compras.index') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Compras -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">N° Orden</th>
                        <th class="px-4 py-3.5">Fecha y Hora</th>
                        <th class="px-4 py-3.5">Proveedor</th>
                        <th class="px-4 py-3.5">Artículos Adquiridos</th>
                        <th class="px-4 py-3.5 text-right">Total ($)</th>
                        <th class="px-4 py-3.5 text-center">Estado</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($compras as $compra)
                        @php
                            $cantItems = $compra->detalleCompras->sum('cantidad');
                            $itemsData = $compra->detalleCompras->map(fn($d) => [
                                'nombre' => $d->producto?->nombre ?? 'Producto',
                                'cantidad' => (float) $d->cantidad,
                                'precio_unitario' => (float) $d->precio_unitario,
                                'subtotal' => ((float) $d->cantidad) * ((float) $d->precio_unitario),
                            ])->values()->all();
                            $compraData = [
                                'id_compra' => $compra->id_compra,
                                'orden_num' => '#COM-' . str_pad($compra->id_compra, 4, '0', STR_PAD_LEFT),
                                'fecha' => $compra->fecha_hora ? \Carbon\Carbon::parse($compra->fecha_hora)->format('d/m/Y H:i') : 'N/A',
                                'proveedor' => $compra->proveedor?->nombre ?? 'Proveedor General',
                                'nit' => $compra->proveedor?->nit ?? 'S/N',
                                'total' => number_format((float)$compra->total, 2),
                                'observaciones' => $compra->observaciones ?? 'Sin notas',
                                'items' => $itemsData,
                            ];
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">
                                #COM-{{ str_pad($compra->id_compra, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                {{ $compra->fecha_hora ? \Carbon\Carbon::parse($compra->fecha_hora)->format('d/m/Y H:i') : 'N/A' }}
                            </td>

                            <td class="px-4 py-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $compra->proveedor?->nombre ?? 'Proveedor General' }}</div>
                                @if ($compra->proveedor?->nit)
                                    <div class="text-[11px] text-slate-400">NIT: {{ $compra->proveedor->nit }}</div>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 text-xs text-slate-600">
                                <span class="font-semibold text-slate-800">{{ $cantItems }} unidades</span>
                                <span class="text-slate-400">({{ $compra->detalleCompras->count() }} referencias)</span>
                            </td>

                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs">
                                ${{ number_format((float)$compra->total, 2) }}
                            </td>

                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    {{ $compra->estado ?? 'COMPLETADA' }}
                                </span>
                            </td>

                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <button
                                    type="button"
                                    data-compra="{{ json_encode($compraData) }}"
                                    @click="verDetalle(JSON.parse($el.dataset.compra))"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                                >
                                    <i data-lucide="eye" class="size-3.5 text-slate-400"></i>
                                    <span>Ver Detalle</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <x-empty-state
                                    title="No hay compras registradas"
                                    description="Registra la primera orden de abastecimiento a proveedores."
                                >
                                    <x-slot:icon>
                                        <i data-lucide="truck" class="size-8 text-slate-300"></i>
                                    </x-slot:icon>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($compras->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $compras->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Detalle de Compra e Impacto de Inventario -->
    <div
        x-show="detalleModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="detalleModalOpen = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="detalleModalOpen = false"></div>

            <div
                class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                x-show="detalleModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                            <i data-lucide="package-check" class="size-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900" x-text="'Orden ' + compraSeleccionada?.orden_num"></h3>
                            <p class="text-xs text-slate-500" x-text="'Proveedor: ' + compraSeleccionada?.proveedor + ' · NIT: ' + compraSeleccionada?.nit"></p>
                        </div>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700">Impacto en Stock Aplicado</span>
                </div>

                <!-- Tabla de Artículos Ingresados -->
                <div class="mt-4 space-y-2">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Artículos Ingresados</p>
                    <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden text-xs">
                        <template x-for="item in compraSeleccionada?.items" :key="item.nombre">
                            <div class="p-3 flex items-center justify-between hover:bg-slate-50/50">
                                <div>
                                    <p class="font-bold text-slate-800" x-text="item.nombre"></p>
                                    <p class="text-[11px] text-slate-400" x-text="item.cantidad + ' uds x $' + item.precio_unitario.toFixed(2)"></p>
                                </div>
                                <span class="font-mono font-bold text-slate-900" x-text="'$' + item.subtotal.toFixed(2)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Total y Observaciones -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[11px] text-slate-400">Notas:</span>
                        <p class="text-xs text-slate-600 italic" x-text="compraSeleccionada?.observaciones"></p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 uppercase">Total de la Orden:</span>
                        <p class="font-mono text-xl font-bold text-slate-900" x-text="'$' + compraSeleccionada?.total"></p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        @click="detalleModalOpen = false"
                        class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
