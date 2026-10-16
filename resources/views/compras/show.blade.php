@extends('layouts.app')

@section('title', 'Detalle de Compra #' . $compra->id_compra)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('compras.index') }}" class="hover:text-indigo-600 transition-colors">Compras</a>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Orden #{{ $compra->id_compra }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Orden de Compra #{{ $compra->id_compra }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    {{ $compra->estado ?? 'COMPLETADA' }}
                </span>
            </div>
            <p class="text-sm text-slate-500">Registrada el {{ \Carbon\Carbon::parse($compra->fecha_hora)->format('d/m/Y \a \l\a\s H:i') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('compras.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a Órdenes
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold shadow-xs">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir Comprobante
            </button>
        </div>
    </div>

    <!-- Datos del Proveedor y Resumen Financiero -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Tarjeta Proveedor -->
        <div class="md:col-span-2 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Información del Proveedor</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <span class="text-xs text-slate-400 block mb-0.5">Razón Social / Nombre</span>
                    <p class="text-base font-semibold text-slate-900">{{ $compra->proveedor->nombre }}</p>
                    <span class="text-xs text-slate-500 font-mono">NIT / RUC: {{ $compra->proveedor->nit ?: 'Sin registrar' }}</span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block mb-0.5">Contacto</span>
                    <p class="text-sm text-slate-800">{{ $compra->proveedor->telefono ?: 'Sin teléfono' }}</p>
                    <p class="text-xs text-slate-500">{{ $compra->proveedor->email ?: 'Sin correo' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-xs text-slate-400 block mb-0.5">Dirección comercial</span>
                    <p class="text-sm text-slate-700">{{ $compra->proveedor->direccion ?: 'No especificada' }}</p>
                </div>
                @if($compra->observaciones)
                    <div class="sm:col-span-2 pt-3 border-t border-slate-100">
                        <span class="text-xs text-slate-400 block mb-0.5">Observaciones de la Orden</span>
                        <p class="text-sm text-slate-700 italic">"{{ $compra->observaciones }}"</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tarjeta Totalizador -->
        <div class="bg-gradient-to-br from-indigo-900 to-indigo-800 text-white p-6 rounded-2xl shadow-sm flex flex-col justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-200 block mb-1">Monto Total de Compra</span>
                <div class="text-3xl font-extrabold tracking-tight mt-1">
                    Bs. {{ number_format($compra->total, 2) }}
                </div>
                <p class="text-xs text-indigo-300 mt-2">
                    {{ $compra->detalleCompras->count() }} ítem(s) abastecidos e ingresados al inventario.
                </p>
            </div>
            <div class="pt-4 border-t border-indigo-700/60 mt-4 flex items-center justify-between text-xs text-indigo-200">
                <span>Estado de Stock:</span>
                <span class="font-semibold text-emerald-300 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Stock Ingresado
                </span>
            </div>
        </div>
    </div>

    <!-- Items Comprados e Impacto en Inventario -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50/75 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Detalle de Insumos / Productos Ingresados</h3>
            <span class="text-xs text-slate-500 font-mono">{{ $compra->detalleCompras->count() }} producto(s)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Producto</th>
                        <th class="py-3 px-4">Lote / Vencimiento</th>
                        <th class="py-3 px-4 text-center">Cantidad Recibida</th>
                        <th class="py-3 px-4 text-right">Precio Unitario</th>
                        <th class="py-3 px-4 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($compra->detalleCompras as $det)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $det->producto->nombre }}</span>
                                <span class="text-xs text-slate-400">Código/ID: #{{ $det->producto->id_producto }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($det->lotes && $det->lotes->isNotEmpty())
                                    @foreach($det->lotes as $lt)
                                        <div class="text-xs space-y-0.5">
                                            <span class="font-mono font-semibold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">Lote: {{ $lt->numero_lote }}</span>
                                            @if($lt->fecha_vencimiento)
                                                <p class="text-slate-500">Vence: {{ \Carbon\Carbon::parse($lt->fecha_vencimiento)->format('d/m/Y') }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <span class="text-xs text-slate-400">Sin lote específico</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-900">
                                {{ number_format($det->cantidad, 0) }} u.
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono text-slate-700">
                                Bs. {{ number_format($det->precio_unitario, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900">
                                Bs. {{ number_format($det->cantidad * $det->precio_unitario, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50/75 border-t border-slate-100 font-semibold text-slate-800">
                    <tr>
                        <td colspan="4" class="py-3.5 px-4 text-right uppercase text-xs tracking-wider">Total Compra:</td>
                        <td class="py-3.5 px-4 text-right font-mono text-base text-indigo-700">
                            Bs. {{ number_format($compra->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
