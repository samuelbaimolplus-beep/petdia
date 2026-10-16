@extends('layouts.app')

@section('title', 'Pet Shop — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    vista: 'grid',
    carritoOpen: false,
    carrito: [],
    idCliente: '',
    idMetodoPago: '{{ $metodosPago->first()?->id_metodo_pago ?? 1 }}',
    descuentoGeneral: 0,
    observaciones: '',
    codigoBarraInput: '',
    barcodeStatus: '',
    barcodeError: '',
    barcodeLoading: false,

    async escanearCodigo() {
        const codigo = this.codigoBarraInput.trim();
        if (!codigo) return;
        this.barcodeError = '';
        this.barcodeLoading = true;
        this.barcodeStatus = 'Buscando producto...';

        try {
            const resp = await fetch(`{{ route('petshop.buscar-codigo') }}?codigo=${encodeURIComponent(codigo)}`);
            const data = await resp.json();
            if (data.success && data.producto) {
                this.agregarAlCarrito(data.producto);
                this.barcodeStatus = `✔ ${data.producto.nombre} agregado!`;
                this.codigoBarraInput = '';
                this.$refs.barcodeInput?.focus();
                setTimeout(() => { if (this.barcodeStatus.startsWith('✔')) this.barcodeStatus = ''; }, 2500);
            } else {
                this.barcodeError = data.message || 'Producto no encontrado';
                this.barcodeStatus = '';
                this.$refs.barcodeInput?.select();
                setTimeout(() => { this.barcodeError = ''; }, 3500);
            }
        } catch (e) {
            this.barcodeError = 'Error al consultar código';
            this.barcodeStatus = '';
        } finally {
            this.barcodeLoading = false;
        }
    },

    agregarAlCarrito(producto) {
        const item = this.carrito.find(i => i.id_producto === producto.id_producto);
        if (item) {
            item.cantidad += 1;
        } else {
            this.carrito.push({
                id_producto: producto.id_producto,
                nombre: producto.nombre,
                precio_unitario: parseFloat(producto.precio_venta),
                cantidad: 1,
                descuento: 0,
                stock_disponible: producto.stock
            });
        }
        this.carritoOpen = true;
    },

    modificarCantidad(id, delta) {
        const item = this.carrito.find(i => i.id_producto === id);
        if (item) {
            item.cantidad += delta;
            if (item.cantidad <= 0) {
                this.quitarDelCarrito(id);
            }
        }
    },

    quitarDelCarrito(id) {
        this.carrito = this.carrito.filter(i => i.id_producto !== id);
    },

    get subtotalCarrito() {
        return this.carrito.reduce((sum, i) => sum + (i.cantidad * i.precio_unitario - i.descuento), 0);
    },

    get totalCarrito() {
        return Math.max(0, this.subtotalCarrito - (parseFloat(this.descuentoGeneral) || 0));
    },

    get cantidadItems() {
        return this.carrito.reduce((sum, i) => sum + i.cantidad, 0);
    }
}">

    <!-- Encabezado de la página -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Pet Shop</h1>
            <p class="text-sm text-slate-500 mt-1">Catálogo de productos, control de stock y ventas de mostrador.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <!-- Botón Carrito -->
            <button
                type="button"
                @click="carritoOpen = true"
                class="relative inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="shopping-cart" class="size-4 text-primary-600"></i>
                <span>Carrito</span>
                <span
                    x-show="cantidadItems > 0"
                    x-text="cantidadItems"
                    class="ml-0.5 inline-flex items-center justify-center rounded-full bg-primary-600 px-2 py-0.5 text-[11px] font-bold text-white"
                ></span>
            </button>

            <!-- Historial de Ventas -->
            <a
                href="{{ route('petshop.ventas') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="receipt" class="size-4 text-slate-500"></i>
                <span>Historial de Ventas</span>
            </a>

            <!-- Nuevo Producto -->
            <a
                href="{{ route('petshop.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
            >
                <i data-lucide="plus" class="size-4"></i>
                <span>Nuevo Producto</span>
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Productos -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Productos</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-primary-50 text-primary-600">
                    <i data-lucide="package" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $totalProductos }}</p>
            <p class="mt-1 text-xs text-slate-500">Artículos activos en catálogo</p>
        </div>

        <!-- Ventas Hoy -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ventas Hoy</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                    <i data-lucide="shopping-bag" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">{{ $ventasHoy }}</p>
            <p class="mt-1 text-xs text-slate-500">Tickets emitidos hoy</p>
        </div>

        <!-- Ingresos Ventas Hoy -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Ingresos Hoy</p>
                <div class="flex size-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600">
                    <i data-lucide="dollar-sign" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight text-slate-900">${{ number_format($ingresosVentasHoy, 2) }}</p>
            <p class="mt-1 text-xs text-slate-500">Recaudación en tienda</p>
        </div>

        <!-- Alertas de Stock Bajo -->
        <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Stock Crítico</p>
                <div class="flex size-9 items-center justify-center rounded-lg {{ $productosAlerta > 0 ? 'bg-amber-50 text-amber-600' : 'bg-slate-50 text-slate-400' }}">
                    <i data-lucide="alert-triangle" class="size-5"></i>
                </div>
            </div>
            <p class="mt-3 text-3xl font-bold tracking-tight {{ $productosAlerta > 0 ? 'text-amber-600' : 'text-slate-900' }}">
                {{ $productosAlerta }}
            </p>
            <p class="mt-1 text-xs text-slate-500">Productos en stock mínimo o agotados</p>
        </div>
    </div>

    <!-- Escáner y Lector de Código de Barras Rápido (POS) -->
    <div class="rounded-2xl border-2 border-primary-200/80 bg-gradient-to-r from-primary-50/50 via-white to-primary-50/30 p-4 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-xl bg-primary-600 text-white shadow-xs">
                    <i data-lucide="scan-barcode" class="size-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-bold text-slate-900">Lector de Código de Barras (POS)</h3>
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border border-emerald-200">
                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Pistola Activa
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">Apunta con la pistola lectora o escribe el código EAN/ID y presiona Enter.</p>
                </div>
            </div>

            <!-- Input con Autofoco y captura de Enter -->
            <div class="flex items-center gap-2 sm:w-96">
                <div class="relative flex-1">
                    <i data-lucide="barcode" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                    <input
                        type="text"
                        x-ref="barcodeInput"
                        x-model="codigoBarraInput"
                        @keydown.enter.prevent="escanearCodigo()"
                        placeholder="Escanear código de barras..."
                        autofocus
                        class="w-full rounded-xl border border-primary-200 bg-white pl-9 pr-3 py-2 text-sm font-mono font-semibold text-slate-900 placeholder:text-slate-400 placeholder:font-sans focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                    />
                </div>
                <button
                    type="button"
                    @click="escanearCodigo()"
                    :disabled="barcodeLoading || !codigoBarraInput.trim()"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs hover:bg-primary-700 disabled:opacity-50 transition-colors"
                >
                    <i data-lucide="corner-down-left" class="size-3.5"></i>
                    <span>Agregar</span>
                </button>
            </div>
        </div>

        <!-- Notificaciones de estado del escáner -->
        <div class="mt-2 text-xs" x-show="barcodeStatus || barcodeError" x-cloak>
            <div x-show="barcodeStatus" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-50 px-2.5 py-1 text-emerald-700 font-semibold text-xs border border-emerald-200">
                <i data-lucide="check-circle-2" class="size-3.5"></i>
                <span x-text="barcodeStatus"></span>
            </div>
            <div x-show="barcodeError" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1 text-rose-700 font-semibold text-xs border border-rose-200">
                <i data-lucide="alert-circle" class="size-3.5"></i>
                <span x-text="barcodeError"></span>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros y Modo de Vista -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs">
        <form action="{{ route('petshop.index') }}" method="GET" class="flex flex-col gap-3 lg:flex-row lg:items-center">
            <!-- Buscador -->
            <div class="relative flex-1">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar producto por nombre, marca o descripción..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-4 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus-ring"
                />
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Filtro Categoría -->
                <select
                    name="categoria"
                    class="rounded-xl border border-slate-200 bg-slate-50/50 px-3 py-2 text-xs font-medium text-slate-700 focus-ring"
                >
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias as $cat)
                        <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>
                            {{ $cat }}
                        </option>
                    @endforeach
                </select>

                <!-- Checkbox Solo con Stock -->
                <label class="flex items-center gap-1.5 text-xs text-slate-600 cursor-pointer px-2">
                    <input
                        type="checkbox"
                        name="solo_con_stock"
                        value="1"
                        {{ request('solo_con_stock') ? 'checked' : '' }}
                        class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    />
                    <span>Con stock</span>
                </label>

                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                >
                    Filtrar
                </button>

                @if (request()->hasAny(['search', 'categoria', 'solo_con_stock']))
                    <a
                        href="{{ route('petshop.index') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                    >
                        Limpiar
                    </a>
                @endif

                <!-- Switch Vista Grid / Table -->
                <div class="ml-auto flex items-center rounded-xl border border-slate-200 p-1 bg-slate-50/50">
                    <button
                        type="button"
                        @click="vista = 'grid'"
                        :class="vista === 'grid' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
                        class="rounded-lg p-1.5 transition-colors"
                        title="Vista en Cuadrícula"
                    >
                        <i data-lucide="layout-grid" class="size-4"></i>
                    </button>
                    <button
                        type="button"
                        @click="vista = 'table'"
                        :class="vista === 'table' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-500 hover:text-slate-800'"
                        class="rounded-lg p-1.5 transition-colors"
                        title="Vista en Tabla"
                    >
                        <i data-lucide="list" class="size-4"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Catálogo en Modo Cuadrícula (Cards) -->
    <div x-show="vista === 'grid'" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @forelse ($productos as $prod)
            @php
                $stock = (float) ($prod->stock_calculado ?? 0);
                $minimo = (float) ($prod->stock_minimo ?? 5);
                $sinStock = ($stock <= 0);
                $bajoStock = ($stock > 0 && $stock <= $minimo);
            @endphp
            <div class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs hover:border-slate-300 hover:shadow-sm transition-all">
                <div>
                    <!-- Categoría y Estado de Stock -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
                            {{ $prod->categoria ?? 'General' }}
                        </span>

                        @if ($sinStock)
                            <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-700">
                                Agotado
                            </span>
                        @elseif ($bajoStock)
                            <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700">
                                Stock bajo ({{ $stock }})
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700">
                                Stock: {{ $stock }}
                            </span>
                        @endif
                    </div>

                    <!-- Icono / Imagen del Producto -->
                    <div class="my-4 flex size-14 items-center justify-center rounded-2xl bg-primary-50/60 text-primary-600 mx-auto group-hover:scale-105 transition-transform">
                        <i data-lucide="{{ str_contains(strtolower($prod->categoria), 'alimento') ? 'bone' : 'package' }}" class="size-7"></i>
                    </div>

                    <!-- Título y Marca -->
                    <div class="text-center">
                        <h3 class="font-bold text-slate-900 text-sm leading-tight line-clamp-2" title="{{ $prod->nombre }}">
                            {{ $prod->nombre }}
                        </h3>
                        @if ($prod->marca)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $prod->marca }}</p>
                        @endif
                    </div>
                </div>

                <!-- Precio y Acción Añadir -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase">Precio</span>
                        <p class="font-mono text-base font-bold text-slate-900">${{ number_format((float)$prod->precio_venta, 2) }}</p>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <a
                            href="{{ route('petshop.edit', $prod->id_producto) }}"
                            class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                            title="Editar producto"
                        >
                            <i data-lucide="edit-3" class="size-4"></i>
                        </a>

                        @php
                            $datosProd = [
                                'id_producto' => $prod->id_producto,
                                'nombre' => $prod->nombre,
                                'precio_venta' => (float) $prod->precio_venta,
                                'stock' => $stock,
                            ];
                        @endphp
                        <button
                            type="button"
                            data-item="{{ json_encode($datosProd) }}"
                            @click="agregarAlCarrito(JSON.parse($el.dataset.item))"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-3 py-2 text-xs font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
                        >
                            <i data-lucide="plus" class="size-3.5"></i>
                            <span>Vender</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12">
                <x-empty-state
                    title="No hay productos en catálogo"
                    description="No se encontraron artículos con los filtros aplicados."
                >
                    <x-slot:icon>
                        <i data-lucide="shopping-bag" class="size-8 text-slate-300"></i>
                    </x-slot:icon>
                </x-empty-state>
            </div>
        @endforelse
    </div>

    <!-- Catálogo en Modo Tabla -->
    <div x-show="vista === 'table'" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="border-b border-slate-100 bg-slate-50/75 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-5 py-3.5">Producto</th>
                        <th class="px-4 py-3.5">Categoría</th>
                        <th class="px-4 py-3.5">Marca</th>
                        <th class="px-4 py-3.5 text-center">Stock Actual</th>
                        <th class="px-4 py-3.5 text-right">Precio Venta</th>
                        <th class="px-5 py-3.5 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $prod)
                        @php
                            $stock = (float) ($prod->stock_calculado ?? 0);
                            $minimo = (float) ($prod->stock_minimo ?? 5);
                            $sinStock = ($stock <= 0);
                            $bajoStock = ($stock > 0 && $stock <= $minimo);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-slate-900 text-xs">{{ $prod->nombre }}</div>
                                <div class="text-[11px] text-slate-400">{{ $prod->unidad_medida ?? 'Unidad' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-xs">
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 font-medium text-slate-700">
                                    {{ $prod->categoria ?? 'General' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-xs text-slate-600">{{ $prod->marca ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap">
                                @if ($sinStock)
                                    <span class="inline-flex items-center rounded-full bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700">
                                        0 (Agotado)
                                    </span>
                                @elseif ($bajoStock)
                                    <span class="inline-flex items-center rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">
                                        {{ $stock }} (Bajo)
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700">
                                        {{ $stock }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-900 text-xs">
                                ${{ number_format((float)$prod->precio_venta, 2) }}
                            </td>
                            <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <a
                                        href="{{ route('petshop.edit', $prod->id_producto) }}"
                                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
                                        title="Editar"
                                    >
                                        <i data-lucide="edit-3" class="size-4"></i>
                                    </a>
                                    @php
                                        $datosProdTab = [
                                            'id_producto' => $prod->id_producto,
                                            'nombre' => $prod->nombre,
                                            'precio_venta' => (float) $prod->precio_venta,
                                            'stock' => $stock,
                                        ];
                                    @endphp
                                    <button
                                        type="button"
                                        data-item="{{ json_encode($datosProdTab) }}"
                                        @click="agregarAlCarrito(JSON.parse($el.dataset.item))"
                                        class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-2.5 py-1.5 text-xs font-semibold text-primary-700 hover:bg-primary-100 transition-colors"
                                    >
                                        <i data-lucide="plus" class="size-3.5"></i>
                                        <span>Vender</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <x-empty-state
                                    title="No hay productos en catálogo"
                                    description="No se encontraron artículos con los filtros aplicados."
                                >
                                    <x-slot:icon>
                                        <i data-lucide="shopping-bag" class="size-8 text-slate-300"></i>
                                    </x-slot:icon>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginación -->
    @if ($productos->hasPages())
        <div class="border-t border-slate-100 pt-4">
            {{ $productos->links() }}
        </div>
    @endif

    <!-- Drawer Lateral del Carrito de Ventas (Alpine.js) -->
    <div
        x-show="carritoOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-hidden"
        @keydown.escape.window="carritoOpen = false"
    >
        <!-- Backdrop translúcido -->
        <div
            class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity"
            @click="carritoOpen = false"
        ></div>

        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
            <div
                class="w-screen max-w-md bg-white shadow-pop flex flex-col justify-between"
                x-show="carritoOpen"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                <!-- Cabecera del Carrito -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                            <i data-lucide="shopping-cart" class="size-5"></i>
                        </div>
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Venta Rápida Pet Shop</h2>
                            <p class="text-xs text-slate-400" x-text="cantidadItems + ' productos seleccionados'"></p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="carritoOpen = false"
                        class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>

                <!-- Lista de Productos en Carrito -->
                <div class="flex-1 overflow-y-auto p-6 space-y-3">
                    <template x-if="carrito.length === 0">
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="shopping-bag" class="size-10 mx-auto mb-2 text-slate-300"></i>
                            <p class="text-sm font-semibold text-slate-600">El carrito está vacío</p>
                            <p class="text-xs text-slate-400 mt-1">Haz clic en '+ Vender' en cualquier producto del catálogo para agregarlo aquí.</p>
                        </div>
                    </template>

                    <template x-for="item in carrito" :key="item.id_producto">
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/50 text-xs">
                            <div class="min-w-0 pr-2">
                                <p class="font-bold text-slate-800 truncate" x-text="item.nombre"></p>
                                <p class="font-mono text-slate-500 mt-0.5" x-text="'$' + item.precio_unitario.toFixed(2) + ' c/u'"></p>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <!-- Controles Cantidad -->
                                <div class="flex items-center rounded-lg border border-slate-200 bg-white">
                                    <button
                                        type="button"
                                        @click="modificarCantidad(item.id_producto, -1)"
                                        class="px-2 py-1 text-slate-500 hover:bg-slate-50"
                                    >-</button>
                                    <span class="px-2 font-mono font-bold text-slate-800" x-text="item.cantidad"></span>
                                    <button
                                        type="button"
                                        @click="modificarCantidad(item.id_producto, 1)"
                                        class="px-2 py-1 text-slate-500 hover:bg-slate-50"
                                    >+</button>
                                </div>

                                <!-- Subtotal Ítem -->
                                <span class="font-mono font-bold text-slate-900 w-16 text-right" x-text="'$' + (item.cantidad * item.precio_unitario).toFixed(2)"></span>

                                <!-- Quitar -->
                                <button
                                    type="button"
                                    @click="quitarDelCarrito(item.id_producto)"
                                    class="text-slate-300 hover:text-rose-600"
                                >
                                    <i data-lucide="trash-2" class="size-4"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Formulario y Pie del Carrito -->
                <form action="{{ route('petshop.venta.store') }}" method="POST" class="p-6 border-t border-slate-100 bg-slate-50/40 space-y-4">
                    @csrf

                    <!-- Inputs ocultos para los ítems -->
                    <template x-for="(item, idx) in carrito" :key="item.id_producto">
                        <div>
                            <input type="hidden" :name="'items[' + idx + '][id_producto]'" :value="item.id_producto" />
                            <input type="hidden" :name="'items[' + idx + '][cantidad]'" :value="item.cantidad" />
                            <input type="hidden" :name="'items[' + idx + '][precio_unitario]'" :value="item.precio_unitario" />
                            <input type="hidden" :name="'items[' + idx + '][descuento]'" :value="item.descuento" />
                        </div>
                    </template>

                    <!-- Cliente / Propietario -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Cliente / Propietario</label>
                        <select
                            name="id_propietario"
                            x-model="idCliente"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 focus-ring"
                        >
                            <option value="">-- Cliente Mostrador / Sin Registrar --</option>
                            @foreach ($propietarios as $prop)
                                <option value="{{ $prop->id_persona }}">
                                    {{ $prop->persona?->nombre }} {{ $prop->persona?->apellido1 }} · CI: {{ $prop->persona?->documento ?? 'S/D' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Método de Pago -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Método de Pago</label>
                        <select
                            name="id_metodo_pago"
                            x-model="idMetodoPago"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 focus-ring"
                        >
                            @foreach ($metodosPago as $met)
                                <option value="{{ $met->id_metodo_pago }}">{{ $met->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Descuento General Opcional -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-700 mb-1">Descuento Global ($)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="descuento_general"
                            x-model="descuentoGeneral"
                            placeholder="0.00"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-mono text-slate-800 focus-ring"
                        />
                    </div>

                    <!-- Total y Liquidación -->
                    <div class="pt-3 border-t border-slate-200/80 flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-900 uppercase">Total a Cobrar:</span>
                        <span class="font-mono text-2xl font-bold text-primary-600" x-text="'$' + totalCarrito.toFixed(2)"></span>
                    </div>

                    <!-- Botón Cobrar -->
                    <button
                        type="submit"
                        :disabled="carrito.length === 0 || totalCarrito <= 0"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 py-3 text-sm font-semibold text-white shadow-xs hover:bg-primary-700 disabled:opacity-50 transition-colors"
                    >
                        <i data-lucide="check-circle" class="size-4"></i>
                        <span>Cobrar y Emitir Ticket</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
