@extends('layouts.app')

@section('title', 'Editar Producto — Pet Shop')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb y encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('petshop.index') }}" class="hover:text-primary-600 transition-colors">Pet Shop</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Editar Producto</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Editar: {{ $producto->nombre }}</h1>
        </div>
        <a
            href="{{ route('petshop.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
        >
            <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
            <span>Volver</span>
        </a>
    </div>

    <!-- Formulario de Edición -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <form action="{{ route('petshop.update', $producto->id_producto) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Producto <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre', $producto->nombre) }}"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                />
            </div>

            <!-- Categoría y Marca -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="categoria" class="block text-xs font-semibold text-slate-700 mb-1">Categoría <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="categoria"
                        name="categoria"
                        value="{{ old('categoria', $producto->categoria) }}"
                        required
                        list="listaCategorias"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    />
                    <datalist id="listaCategorias">
                        @foreach ($categorias as $cat)
                            <option value="{{ $cat }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label for="marca" class="block text-xs font-semibold text-slate-700 mb-1">Marca / Fabricante</label>
                    <input
                        type="text"
                        id="marca"
                        name="marca"
                        value="{{ old('marca', $producto->marca) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    />
                </div>
            </div>

            <!-- Código de Barras (EAN / UPC) -->
            <div>
                <label for="codigo_barra" class="block text-xs font-semibold text-slate-700 mb-1">
                    Código de Barras (EAN / UPC / SKU)
                    <span class="text-[11px] text-slate-400 font-normal">(opcional, para lector/pistola rápida)</span>
                </label>
                <div class="relative">
                    <i data-lucide="barcode" class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-slate-400"></i>
                    <input
                        type="text"
                        id="codigo_barra"
                        name="codigo_barra"
                        value="{{ old('codigo_barra', $producto->codigo_barra) }}"
                        placeholder="Ej: 7791234567890 o escanea con la pistola..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 pl-9 pr-3 p-2.5 text-xs font-mono text-slate-800 focus-ring"
                    />
                </div>
                @error('codigo_barra')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Precios y Stock Mínimo -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="precio_venta" class="block text-xs font-semibold text-slate-700 mb-1">Precio Venta ($) <span class="text-rose-500">*</span></label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        id="precio_venta"
                        name="precio_venta"
                        value="{{ old('precio_venta', $producto->precio_venta) }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs font-mono font-bold text-slate-900 focus-ring"
                    />
                </div>

                <div>
                    <label for="stock_minimo" class="block text-xs font-semibold text-slate-700 mb-1">Alerta Stock Mínimo</label>
                    <input
                        type="number"
                        min="0"
                        id="stock_minimo"
                        name="stock_minimo"
                        value="{{ old('stock_minimo', $producto->stock_minimo) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs font-mono text-slate-800 focus-ring"
                    />
                </div>

                <div>
                    <label for="estado" class="block text-xs font-semibold text-slate-700 mb-1">Estado Catálogo</label>
                    <select
                        id="estado"
                        name="estado"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    >
                        <option value="ACTIVO" {{ old('estado', $producto->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                        <option value="INACTIVO" {{ old('estado', $producto->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo (Ocultar)</option>
                    </select>
                </div>
            </div>

            <!-- Unidad de Medida -->
            <div>
                <label for="unidad_medida" class="block text-xs font-semibold text-slate-700 mb-1">Unidad de Medida</label>
                <select
                    id="unidad_medida"
                    name="unidad_medida"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >
                    <option value="UNIDAD" {{ old('unidad_medida', $producto->unidad_medida) === 'UNIDAD' ? 'selected' : '' }}>Unidad (Pza / Bolsa / Frasco)</option>
                    <option value="KG" {{ old('unidad_medida', $producto->unidad_medida) === 'KG' ? 'selected' : '' }}>Kilogramo (Kg)</option>
                    <option value="LITRO" {{ old('unidad_medida', $producto->unidad_medida) === 'LITRO' ? 'selected' : '' }}>Litro (L)</option>
                    <option value="CAJA" {{ old('unidad_medida', $producto->unidad_medida) === 'CAJA' ? 'selected' : '' }}>Caja / Pack</option>
                </select>
            </div>

            <!-- Descripción -->
            <div>
                <label for="descripcion" class="block text-xs font-semibold text-slate-700 mb-1">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="3"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >{{ old('descripcion', $producto->descripcion) }}</textarea>
            </div>

            <!-- Botones -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <a
                    href="{{ route('petshop.index') }}"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-xs"
                >
                    Actualizar Cambios
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
