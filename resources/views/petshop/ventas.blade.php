@extends('layouts.app')

@section('title', 'Historial de Ventas — Pet Shop')

@section('content')
<div class="space-y-6">

    <!-- Breadcrumb y encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('petshop.index') }}" class="hover:text-primary-600 transition-colors">Pet Shop</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Historial de Ventas</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Ventas y Facturación de Pet Shop</h1>
            <p class="text-sm text-slate-500">Registro histórico de transacciones comerciales y salidas de mostrador.</p>
        </div>
        <a
            href="{{ route('petshop.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
        >
            <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
            <span>Volver a Tienda</span>
        </a>
    </div>

    <!-- Filtros de Ventas -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form action="{{ route('petshop.ventas') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por ticket # o nombre del cliente..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-ring"
                />
            </div>

            <div class="flex items-center gap-2">
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
                @if (request()->hasAny(['search', 'fecha']))
                    <a
                        href="{{ route('petshop.ventas') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                    >
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Ventas -->
    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Ticket / Venta</th>
                        <th class="px-4 py-3.5">Fecha y Hora</th>
                        <th class="px-4 py-3.5">Cliente</th>
                        <th class="px-4 py-3.5">Artículos Vendidos</th>
                        <th class="px-4 py-3.5">Vendedor</th>
                        <th class="px-4 py-3.5 text-right">Total ($)</th>
                        <th class="px-5 py-3.5 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($ventas as $v)
                        @php
                            $persona = $v->propietario?->persona;
                            $vendedor = $v->usuario?->persona;
                            $cantItems = $v->detalleVentas->sum('cantidad');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-900">
                                #TIK-{{ str_pad($v->id_venta, 5, '0', STR_PAD_LEFT) }}
                            </td>
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                {{ $v->fecha_hora ? \Carbon\Carbon::parse($v->fecha_hora)->format('d/m/Y H:i') : 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 text-xs">
                                <span class="font-semibold text-slate-800">
                                    {{ $persona ? $persona->nombre . ' ' . $persona->apellido1 : 'Cliente Mostrador' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600">
                                <span class="font-medium text-slate-800">{{ $cantItems }} uds</span>
                                <span class="text-slate-400">({{ $v->detalleVentas->count() }} ítems)</span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-500">
                                {{ $vendedor ? $vendedor->nombre : 'Sistema' }}
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs">
                                ${{ number_format((float)$v->total, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                    {{ $v->estado ?? 'COMPLETADA' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <x-empty-state
                                    title="No hay ventas registradas"
                                    description="Las ventas realizadas en la tienda aparecerán aquí automáticamente."
                                >
                                    <x-slot:icon>
                                        <i data-lucide="receipt" class="size-8 text-slate-300"></i>
                                    </x-slot:icon>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($ventas->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">
                {{ $ventas->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
