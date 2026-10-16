@extends('layouts.app')

@section('title', 'Registrar Compra — La Petdiatría')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    productosDisponibles: {{ Js::from($productos->map(fn($p) => [
        'id_producto' => $p->id_producto,
        'nombre' => $p->nombre,
        'precio_referencial' => (float) $p->precio_venta,
        'stock_actual' => (float) ($p->stock_actual ?? 0),
        'control_lotes' => (bool) $p->control_lotes
    ])) }},
    items: [
        {
            id_producto: '{{ $productos->first()?->id_producto ?? "" }}',
            cantidad: 1,
            precio_unitario: 0,
            lote: '',
            fecha_vencimiento: ''
        }
    ],

    agregarItem() {
        this.items.push({
            id_producto: this.productosDisponibles[0]?.id_producto || '',
            cantidad: 1,
            precio_unitario: 0,
            lote: '',
            fecha_vencimiento: ''
        });
    },

    eliminarItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },

    getStockPrevio(id) {
        const prod = this.productosDisponibles.find(p => p.id_producto == id);
        return prod ? prod.stock_actual : 0;
    },

    get totalCompra() {
        return this.items.reduce((sum, i) => sum + ((parseFloat(i.cantidad) || 0) * (parseFloat(i.precio_unitario) || 0)), 0);
    }
}">

    <!-- Breadcrumb y encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('compras.index') }}" class="hover:text-primary-600 transition-colors">Compras</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Registrar Compra</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Registrar Compra a Proveedor</h1>
            <p class="text-sm text-slate-500">Ingreso de mercadería, insumos o medicamentos con actualización directa de stock.</p>
        </div>
        <a
            href="{{ route('compras.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
        >
            <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
            <span>Volver</span>
        </a>
    </div>

    <!-- Formulario de Compra -->
    <form action="{{ route('compras.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Datos Principales de la Orden -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-4">
            <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Datos del Proveedor y Factura</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="id_proveedor" class="block text-xs font-semibold text-slate-700 mb-1">Proveedor <span class="text-rose-500">*</span></label>
                    <select
                        id="id_proveedor"
                        name="id_proveedor"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    >
                        <option value="">-- Selecciona el proveedor --</option>
                        @foreach ($proveedores as $prov)
                            <option value="{{ $prov->id_proveedor }}" {{ old('id_proveedor') == $prov->id_proveedor ? 'selected' : '' }}>
                                {{ $prov->nombre }} (NIT: {{ $prov->nit ?? 'S/N' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="fecha" class="block text-xs font-semibold text-slate-700 mb-1">Fecha de la Compra <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        id="fecha"
                        name="fecha"
                        value="{{ old('fecha', date('Y-m-d')) }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    />
                </div>
            </div>

            <div>
                <label for="observaciones" class="block text-xs font-semibold text-slate-700 mb-1">Observaciones / N° de Factura del Proveedor</label>
                <input
                    type="text"
                    id="observaciones"
                    name="observaciones"
                    value="{{ old('observaciones') }}"
                    placeholder="Ej: Factura N° F-9821, entrega completa en almacén central"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                />
            </div>
        </div>

        <!-- Detalle de Productos a Ingresar -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Detalle de Mercadería Ingresada</h3>
                    <p class="text-xs text-slate-500">El stock en inventario se actualizará automáticamente (Antes vs Después)</p>
                </div>
                <button
                    type="button"
                    @click="agregarItem()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                >
                    <i data-lucide="plus" class="size-3.5"></i>
                    <span>Añadir Producto</span>
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, index) in items" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-500" x-text="'Ítem #' + (index + 1)"></span>
                            <button
                                type="button"
                                @click="eliminarItem(index)"
                                x-show="items.length > 1"
                                class="text-slate-400 hover:text-rose-600 transition-colors p-1"
                                title="Eliminar ítem"
                            >
                                <i data-lucide="trash-2" class="size-4"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <!-- Producto -->
                            <div class="sm:col-span-6">
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Producto <span class="text-rose-500">*</span></label>
                                <select
                                    :name="'items[' + index + '][id_producto]'"
                                    x-model="item.id_producto"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 focus-ring"
                                >
                                    <template x-for="prod in productosDisponibles" :key="prod.id_producto">
                                        <option :value="prod.id_producto" x-text="prod.nombre"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- Cantidad Ingresada -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Cantidad <span class="text-rose-500">*</span></label>
                                <input
                                    type="number"
                                    step="1"
                                    min="1"
                                    :name="'items[' + index + '][cantidad]'"
                                    x-model="item.cantidad"
                                    required
                                    class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                                />
                            </div>

                            <!-- Precio Costo Unitario -->
                            <div class="sm:col-span-3">
                                <label class="block text-[11px] font-semibold text-slate-700 mb-1">Precio Unitario ($) <span class="text-rose-500">*</span></label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :name="'items[' + index + '][precio_unitario]'"
                                    x-model="item.precio_unitario"
                                    required
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                                />
                            </div>
                        </div>

                        <!-- Lote y Vencimiento opcionales -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200/50">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">N° de Lote (opcional)</label>
                                <input
                                    type="text"
                                    :name="'items[' + index + '][lote]'"
                                    x-model="item.lote"
                                    placeholder="Ej: L-2026-09"
                                    class="w-full rounded-xl border border-slate-200 bg-white p-1.5 text-xs text-slate-800 focus-ring"
                                />
                            </div>

                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Fecha de Vencimiento (opcional)</label>
                                <input
                                    type="date"
                                    :name="'items[' + index + '][fecha_vencimiento]'"
                                    x-model="item.fecha_vencimiento"
                                    class="w-full rounded-xl border border-slate-200 bg-white p-1.5 text-xs text-slate-800 focus-ring"
                                />
                            </div>
                        </div>

                        <!-- Indicador de Impacto en Stock (Antes vs Después) -->
                        <div class="flex items-center justify-between pt-2 border-t border-slate-200/50 text-[11px]">
                            <div class="flex items-center gap-2 text-slate-500">
                                <span>Stock actual: <strong class="text-slate-700 font-mono" x-text="getStockPrevio(item.id_producto)"></strong></span>
                                <span>&rarr;</span>
                                <span class="text-emerald-700 font-bold">Nuevo stock: <span class="font-mono" x-text="getStockPrevio(item.id_producto) + (parseFloat(item.cantidad) || 0)"></span></span>
                            </div>
                            <div class="font-mono text-xs font-bold text-slate-900">
                                Subtotal: $<span x-text="((parseFloat(item.cantidad) || 0) * (parseFloat(item.precio_unitario) || 0)).toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Total de la Orden -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-between">
                <div>
                    <span class="text-xs text-slate-500 font-medium">Condición de Pago:</span>
                    <span class="ml-1 text-xs font-bold text-slate-800">Contado / Ingreso Directo</span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Orden de Compra:</span>
                    <p class="font-mono text-2xl font-bold text-primary-600" x-text="'$' + totalCompra.toFixed(2)"></p>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="flex items-center justify-end gap-2">
            <a
                href="{{ route('compras.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
            >
                Cancelar
            </a>
            <button
                type="submit"
                :disabled="totalCompra <= 0"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700 disabled:opacity-50 transition-colors shadow-xs"
            >
                <i data-lucide="check-circle" class="size-4"></i>
                <span>Confirmar Compra e Ingresar a Inventario</span>
            </button>
        </div>
    </form>

</div>
@endsection
