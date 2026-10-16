@extends('layouts.app')

@section('title', 'Inventario Médico - La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    openModalCreate: false,
    openModalEdit: false,
    openModalMovimiento: false,
    currentTipo: 'MEDICAMENTO',
    editData: {
        id_producto: '',
        nombre: '',
        tipo: 'MEDICAMENTO',
        categoria: 'VETERINARIA',
        marca: '',
        unidad_medida: 'Unidad',
        descripcion: '',
        precio_venta: '',
        stock_minimo: '',
        control_lotes: false,
        estado: 'ACTIVO',
        principio_activo: '',
        presentacion: '',
        concentracion: '',
        unidad_concentracion: ''
    },
    movimientoData: {
        id_producto: '',
        tipo_movimiento: 'ENTRADA',
        cantidad: '',
        motivo: 'AJUSTE',
        numero_lote: '',
        fecha_vencimiento: ''
    },
    openEdit(prod) {
        this.editData = { ...prod };
        this.openModalEdit = true;
    },
    openMovimientoFor(idProd, prodName) {
        this.movimientoData.id_producto = idProd;
        this.movimientoData.nombre = prodName;
        this.openModalMovimiento = true;
    }
}">

    <!-- Breadcrumb & Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <nav class="flex items-center gap-2 text-xs font-medium text-slate-500 mb-1">
                <span>Administración</span>
                <i data-lucide="chevron-right" class="size-3"></i>
                <span class="text-primary-600 font-semibold">Inventario Médico</span>
            </nav>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Inventario Médico e Insumos</h1>
            <p class="text-sm text-slate-500">Gestión de existencias, control de lotes y monitoreo de caducidades.</p>
        </div>

        <div class="flex items-center gap-2.5">
            <a
                href="{{ route('inventario.alertas') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3.5 py-2.5 text-xs font-semibold text-amber-800 hover:bg-amber-100 transition-colors shadow-sm"
            >
                <i data-lucide="alert-triangle" class="size-4 text-amber-600"></i>
                <span>Ver Alertas ({{ $stockBajoCount + $porVencerCount }})</span>
            </a>

            @if ($canEdit)
                <button
                    type="button"
                    @click="openModalCreate = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-xs font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors"
                >
                    <i data-lucide="plus" class="size-4"></i>
                    <span>Nuevo Producto</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Navegación por Pestañas -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6">
            <a
                href="{{ route('inventario.index') }}"
                class="flex items-center gap-2 pb-3 text-sm font-semibold border-b-2 border-primary-600 text-primary-600"
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
                class="flex items-center gap-2 pb-3 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300 transition-colors"
            >
                <i data-lucide="alert-circle" class="size-4"></i>
                <span>Panel de Alertas</span>
            </a>
        </nav>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Productos</span>
                <div class="rounded-xl bg-primary-50 p-2 text-primary-600">
                    <i data-lucide="package" class="size-5"></i>
                </div>
            </div>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ $totalProductos }}</p>
            <p class="mt-1 text-xs text-slate-500">En catálogo de clínica y farmacia</p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-600">Stock Bajo</span>
                <div class="rounded-xl bg-amber-50 p-2 text-amber-600">
                    <i data-lucide="alert-triangle" class="size-5"></i>
                </div>
            </div>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-amber-700">{{ $stockBajoCount }}</p>
            <p class="mt-1 text-xs text-slate-500">Cantidad menor o igual al mínimo</p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-600">Por Vencer (≤ 30d)</span>
                <div class="rounded-xl bg-rose-50 p-2 text-rose-600">
                    <i data-lucide="clock" class="size-5"></i>
                </div>
            </div>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-rose-700">{{ $porVencerCount }}</p>
            <p class="mt-1 text-xs text-slate-500">Lotes próximos a caducar</p>
        </div>

        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Movimientos Mes</span>
                <div class="rounded-xl bg-indigo-50 p-2 text-indigo-600">
                    <i data-lucide="activity" class="size-5"></i>
                </div>
            </div>
            <p class="mt-2 text-3xl font-extrabold tracking-tight text-indigo-900">{{ $movimientosMesCount }}</p>
            <p class="mt-1 text-xs text-slate-500">Entradas y salidas registradas</p>
        </div>
    </div>

    <!-- Filtros y Búsqueda -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('inventario.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5 relative">
                <i data-lucide="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                <input
                    type="text"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    placeholder="Buscar por nombre, marca o principio activo..."
                    class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-2 text-xs text-slate-900 placeholder-slate-400 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
            </div>

            <div class="sm:col-span-3">
                <select
                    name="tipo"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Todos los Tipos</option>
                    <option value="MEDICAMENTO" {{ request('tipo') == 'MEDICAMENTO' ? 'selected' : '' }}>Medicamento</option>
                    <option value="INSUMO" {{ request('tipo') == 'INSUMO' ? 'selected' : '' }}>Insumo</option>
                    <option value="ACCESORIO" {{ request('tipo') == 'ACCESORIO' ? 'selected' : '' }}>Accesorio</option>
                    <option value="HERRAMIENTA" {{ request('tipo') == 'HERRAMIENTA' ? 'selected' : '' }}>Herramienta</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <select
                    name="categoria"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-700 focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">Todas las Categorías</option>
                    <option value="VETERINARIA" {{ request('categoria') == 'VETERINARIA' ? 'selected' : '' }}>Veterinaria</option>
                    <option value="ESTETICA" {{ request('categoria') == 'ESTETICA' ? 'selected' : '' }}>Estética</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button
                    type="submit"
                    class="w-full rounded-xl bg-slate-900 px-3 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors shadow-sm"
                >
                    Filtrar
                </button>
                @if(request()->hasAny(['buscar', 'tipo', 'categoria']))
                    <a
                        href="{{ route('inventario.index') }}"
                        class="rounded-xl border border-slate-200 px-2.5 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                        title="Limpiar filtros"
                    >
                        <i data-lucide="rotate-ccw" class="size-4"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Productos -->
    <div class="rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3.5 px-4">Producto</th>
                        <th class="py-3.5 px-3">Tipo / Categoría</th>
                        <th class="py-3.5 px-3">Fórmula / Presentación</th>
                        <th class="py-3.5 px-3 text-center">Stock Actual</th>
                        <th class="py-3.5 px-3 text-center">Stock Mínimo</th>
                        <th class="py-3.5 px-3">Lote y Vencimiento</th>
                        <th class="py-3.5 px-3">Precio Venta</th>
                        <th class="py-3.5 px-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($productos as $producto)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-slate-900 text-sm">{{ $producto->nombre }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $producto->marca ?? 'Genérico' }} · Unidad: {{ $producto->unidad_medida }}
                                </div>
                            </td>

                            <td class="py-3.5 px-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                    {{ $producto->tipo === 'MEDICAMENTO' ? 'bg-primary-50 text-primary-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $producto->tipo }}
                                </span>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $producto->categoria }}</div>
                            </td>

                            <td class="py-3.5 px-3">
                                @if ($producto->medicamento)
                                    <div class="font-medium text-slate-800">{{ $producto->medicamento->principio_activo }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $producto->medicamento->presentacion }}
                                        @if($producto->medicamento->concentracion)
                                            · {{ $producto->medicamento->concentracion }} {{ $producto->medicamento->unidad_concentracion }}
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 italic">No aplica</span>
                                @endif
                            </td>

                            <!-- Stock actual con badges de estado -->
                            <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                @if ($producto->stock_actual <= 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                                        <i data-lucide="alert-circle" class="size-3.5"></i>
                                        0 {{ $producto->unidad_medida }}
                                    </span>
                                @elseif ($producto->es_stock_bajo)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                        <i data-lucide="alert-triangle" class="size-3.5"></i>
                                        {{ number_format($producto->stock_actual, 1) }} {{ $producto->unidad_medida }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle-2" class="size-3.5"></i>
                                        {{ number_format($producto->stock_actual, 1) }} {{ $producto->unidad_medida }}
                                    </span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 text-center text-slate-600 font-medium">
                                {{ number_format($producto->stock_minimo, 1) }}
                            </td>

                            <!-- Lote y vencimiento con badges -->
                            <td class="py-3.5 px-3 whitespace-nowrap">
                                @if ($producto->lote_proximo)
                                    <div class="text-[11px] font-medium text-slate-800">
                                        Lote: {{ $producto->lote_proximo->numero_lote }}
                                    </div>
                                    @if ($producto->es_vencido)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">
                                            Vencido: {{ \Carbon\Carbon::parse($producto->lote_proximo->fecha_vencimiento)->format('d/m/Y') }}
                                        </span>
                                    @elseif ($producto->es_por_vencer)
                                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">
                                            Vence: {{ \Carbon\Carbon::parse($producto->lote_proximo->fecha_vencimiento)->format('d/m/Y') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] text-slate-500">
                                            Vence: {{ \Carbon\Carbon::parse($producto->lote_proximo->fecha_vencimiento)->format('d/m/Y') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Sin lotes activos</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-3 font-semibold text-slate-900 whitespace-nowrap">
                                Bs. {{ number_format($producto->precio_venta, 2) }}
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    @if ($canEdit)
                                        <button
                                            type="button"
                                            @click="openMovimientoFor({{ $producto->id_producto }}, @js($producto->nombre))"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-indigo-50 hover:text-indigo-600 transition-colors"
                                            title="Registrar Movimiento de Stock"
                                        >
                                            <i data-lucide="arrow-left-right" class="size-4"></i>
                                        </button>

                                        <button
                                            type="button"
                                            @click="openEdit(@js([
                                                'id_producto' => $producto->id_producto,
                                                'nombre' => $producto->nombre,
                                                'tipo' => $producto->tipo,
                                                'categoria' => $producto->categoria,
                                                'marca' => $producto->marca ?? '',
                                                'unidad_medida' => $producto->unidad_medida,
                                                'descripcion' => $producto->descripcion ?? '',
                                                'precio_venta' => (string) $producto->precio_venta,
                                                'stock_minimo' => (string) $producto->stock_minimo,
                                                'control_lotes' => (bool) $producto->control_lotes,
                                                'estado' => $producto->estado,
                                                'principio_activo' => $producto->medicamento->principio_activo ?? '',
                                                'presentacion' => $producto->medicamento->presentacion ?? '',
                                                'concentracion' => (string) ($producto->medicamento->concentracion ?? ''),
                                                'unidad_concentracion' => $producto->medicamento->unidad_concentracion ?? '',
                                            ]))"
                                            class="rounded-lg p-1.5 text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                                            title="Editar Producto"
                                        >
                                            <i data-lucide="pencil" class="size-4"></i>
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Solo lectura</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center">
                                <div class="mx-auto flex size-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                                    <i data-lucide="package-search" class="size-6"></i>
                                </div>
                                <h3 class="text-sm font-semibold text-slate-900">No se encontraron productos</h3>
                                <p class="text-xs text-slate-500 mt-1">Prueba cambiando los filtros de búsqueda o registra un nuevo producto.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($productos->hasPages())
            <div class="border-t border-slate-200/80 px-4 py-3">
                {{ $productos->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL: CREAR PRODUCTO -->
    @if ($canEdit)
        <div
            x-show="openModalCreate"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto"
        >
            <div
                @click.away="openModalCreate = false"
                class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden my-8"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Registrar Nuevo Producto Médico / Insumo</h3>
                        <p class="text-xs text-slate-500">Completa los datos de identificación, clasificación y stock inicial.</p>
                    </div>
                    <button
                        type="button"
                        @click="openModalCreate = false"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('inventario.productos.store') }}" class="p-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre Comercial *</label>
                            <input
                                type="text"
                                name="nombre"
                                required
                                placeholder="Ej. Amoxicilina 250mg, Shampoo Antipulgas..."
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Producto *</label>
                            <select
                                name="tipo"
                                x-model="currentTipo"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                                <option value="MEDICAMENTO">Medicamento</option>
                                <option value="INSUMO">Insumo</option>
                                <option value="ACCESORIO">Accesorio</option>
                                <option value="HERRAMIENTA">Herramienta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría *</label>
                            <select
                                name="categoria"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                                <option value="VETERINARIA">Veterinaria</option>
                                <option value="ESTETICA">Estética</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Marca / Laboratorio</label>
                            <input
                                type="text"
                                name="marca"
                                placeholder="Ej. Zoetis, PetCare, Bayer..."
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unidad de Medida *</label>
                            <input
                                type="text"
                                name="unidad_medida"
                                required
                                placeholder="Ej. Tableta, Dosis, Frasco, Litro, Unidad..."
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Precio de Venta (Bs.) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="precio_venta"
                                required
                                placeholder="0.00"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Stock Mínimo (Alerta) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="stock_minimo"
                                required
                                placeholder="10.00"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                            >
                        </div>
                    </div>

                    <!-- Campos exclusivos de Medicamento -->
                    <div
                        x-show="currentTipo === 'MEDICAMENTO'"
                        class="rounded-xl border border-primary-100 bg-primary-50/40 p-4 space-y-3"
                    >
                        <h4 class="text-xs font-bold text-primary-900 uppercase tracking-wide flex items-center gap-1.5">
                            <i data-lucide="pill" class="size-4 text-primary-600"></i>
                            Especificaciones Farmacológicas
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Principio Activo</label>
                                <input
                                    type="text"
                                    name="principio_activo"
                                    placeholder="Ej. Amoxicilina trihidrato..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Presentación</label>
                                <input
                                    type="text"
                                    name="presentacion"
                                    placeholder="Ej. Suspensión oral, Comprimidos..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Concentración</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="concentracion"
                                    placeholder="Ej. 250"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Unidad Concentración</label>
                                <input
                                    type="text"
                                    name="unidad_concentracion"
                                    placeholder="Ej. mg/ml, mg, %..."
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                >
                            </div>
                        </div>
                    </div>

                    <!-- Stock inicial y Lote inicial (opcional) -->
                    <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 space-y-3">
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide flex items-center gap-1.5">
                            <i data-lucide="layers" class="size-4 text-slate-600"></i>
                            Stock Inicial y Lote (Opcional)
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Cantidad Inicial</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="stock_inicial"
                                    placeholder="0.00"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">N° Lote</label>
                                <input
                                    type="text"
                                    name="lote_numero"
                                    placeholder="Ej. LOT-2026-A"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Fecha Vencimiento</label>
                                <input
                                    type="date"
                                    name="lote_vencimiento"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900 focus:border-primary-500"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            name="control_lotes"
                            id="checkControlLotes"
                            value="1"
                            checked
                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                        >
                        <label for="checkControlLotes" class="text-xs font-medium text-slate-700">Habilitar control de lotes y fechas de vencimiento</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            @click="openModalCreate = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors"
                        >
                            Guardar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: EDITAR PRODUCTO -->
        <div
            x-show="openModalEdit"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto"
        >
            <div
                @click.away="openModalEdit = false"
                class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden my-8"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Editar Producto Médico</h3>
                        <p class="text-xs text-slate-500">Actualiza las especificaciones y niveles de alerta del producto.</p>
                    </div>
                    <button
                        type="button"
                        @click="openModalEdit = false"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i data-lucide="x" class="size-5"></i>
                    </button>
                </div>

                <form
                    method="POST"
                    :action="'{{ url('inventario/productos') }}/' + editData.id_producto"
                    class="p-6 space-y-4"
                >
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre Comercial *</label>
                            <input
                                type="text"
                                name="nombre"
                                x-model="editData.nombre"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo *</label>
                            <select
                                name="tipo"
                                x-model="editData.tipo"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                                <option value="MEDICAMENTO">Medicamento</option>
                                <option value="INSUMO">Insumo</option>
                                <option value="ACCESORIO">Accesorio</option>
                                <option value="HERRAMIENTA">Herramienta</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Categoría *</label>
                            <select
                                name="categoria"
                                x-model="editData.categoria"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                                <option value="VETERINARIA">Veterinaria</option>
                                <option value="ESTETICA">Estética</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Marca / Laboratorio</label>
                            <input
                                type="text"
                                name="marca"
                                x-model="editData.marca"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Unidad de Medida *</label>
                            <input
                                type="text"
                                name="unidad_medida"
                                x-model="editData.unidad_medida"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Precio de Venta (Bs.) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="precio_venta"
                                x-model="editData.precio_venta"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Stock Mínimo (Alerta) *</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="stock_minimo"
                                x-model="editData.stock_minimo"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Estado *</label>
                            <select
                                name="estado"
                                x-model="editData.estado"
                                required
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-primary-500"
                            >
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </div>
                    </div>

                    <!-- Campos de Medicamento en edición -->
                    <div
                        x-show="editData.tipo === 'MEDICAMENTO'"
                        class="rounded-xl border border-primary-100 bg-primary-50/40 p-4 space-y-3"
                    >
                        <h4 class="text-xs font-bold text-primary-900 uppercase tracking-wide">
                            Especificaciones Farmacológicas
                        </h4>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Principio Activo</label>
                                <input
                                    type="text"
                                    name="principio_activo"
                                    x-model="editData.principio_activo"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Presentación</label>
                                <input
                                    type="text"
                                    name="presentacion"
                                    x-model="editData.presentacion"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Concentración</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    name="concentracion"
                                    x-model="editData.concentracion"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900"
                                >
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Unidad Concentración</label>
                                <input
                                    type="text"
                                    name="unidad_concentracion"
                                    x-model="editData.unidad_concentracion"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-900"
                                >
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input
                            type="checkbox"
                            name="control_lotes"
                            id="editCheckControlLotes"
                            value="1"
                            x-model="editData.control_lotes"
                            class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                        >
                        <label for="editCheckControlLotes" class="text-xs font-medium text-slate-700">Habilitar control de lotes y fechas de vencimiento</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                        <button
                            type="button"
                            @click="openModalEdit = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors"
                        >
                            Actualizar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL: REGISTRAR MOVIMIENTO RÁPIDO -->
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
                        <h3 class="text-base font-bold text-slate-900">Registrar Movimiento de Stock</h3>
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
                            <option value="AJUSTE">Ajuste manual / Inventario físico</option>
                            <option value="COMPRA">Compra / Reabastecimiento</option>
                            <option value="USO_ATENCION">Uso en Atención Médica</option>
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
                            Registrar Movimiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
