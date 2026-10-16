@extends('layouts.app')

@section('title', 'Editar Servicio — La Petdiatría')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Breadcrumb y encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('servicios.index') }}" class="hover:text-primary-600 transition-colors">Servicios</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Editar Servicio</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Editar: {{ $servicio->nombre }}</h1>
        </div>
        <a
            href="{{ route('servicios.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
        >
            <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
            <span>Volver</span>
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Columna Izquierda: Datos Generales (2 cols) -->
        <div class="md:col-span-2 space-y-6">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
                <h3 class="text-base font-bold text-slate-900 pb-3 border-b border-slate-100">Información General</h3>

                <form action="{{ route('servicios.update', $servicio->id_servicio) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <!-- Nombre -->
                    <div>
                        <label for="nombre" class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Servicio <span class="text-rose-500">*</span></label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre', $servicio->nombre) }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                        />
                    </div>

                    <!-- Categoría y Precio Base -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="categoria" class="block text-xs font-semibold text-slate-700 mb-1">Categoría <span class="text-rose-500">*</span></label>
                            <select
                                id="categoria"
                                name="categoria"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                            >
                                <option value="VETERINARIA" {{ old('categoria', $servicio->categoria) === 'VETERINARIA' ? 'selected' : '' }}>Veterinaria (Médico)</option>
                                <option value="ESTETICA" {{ old('categoria', $servicio->categoria) === 'ESTETICA' ? 'selected' : '' }}>Estética / SPA</option>
                                <option value="CIRUGIA" {{ old('categoria', $servicio->categoria) === 'CIRUGIA' ? 'selected' : '' }}>Cirugía / Quirófano</option>
                                <option value="LABORATORIO" {{ old('categoria', $servicio->categoria) === 'LABORATORIO' ? 'selected' : '' }}>Laboratorio / Diagnóstico</option>
                                <option value="OTRO" {{ old('categoria', $servicio->categoria) === 'OTRO' ? 'selected' : '' }}>Otro Servicio</option>
                            </select>
                        </div>

                        <div>
                            <label for="precio_base" class="block text-xs font-semibold text-slate-700 mb-1">Precio Base ($) <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="precio_base"
                                name="precio_base"
                                value="{{ old('precio_base', $servicio->precio_base) }}"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>
                    </div>

                    <!-- Estado y Domicilio -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="estado" class="block text-xs font-semibold text-slate-700 mb-1">Estado Catálogo</label>
                            <select
                                id="estado"
                                name="estado"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                            >
                                <option value="ACTIVO" {{ old('estado', $servicio->estado) === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                                <option value="INACTIVO" {{ old('estado', $servicio->estado) === 'INACTIVO' ? 'selected' : '' }}>Inactivo (Ocultar)</option>
                            </select>
                        </div>

                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="disponible_domicilio"
                                    value="1"
                                    {{ old('disponible_domicilio', $servicio->disponible_domicilio) ? 'checked' : '' }}
                                    class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                                />
                                <span>Disponible a Domicilio</span>
                            </label>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label for="descripcion" class="block text-xs font-semibold text-slate-700 mb-1">Descripción</label>
                        <textarea
                            id="descripcion"
                            name="descripcion"
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                        >{{ old('descripcion', $servicio->descripcion) }}</textarea>
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label for="observaciones" class="block text-xs font-semibold text-slate-700 mb-1">Observaciones</label>
                        <textarea
                            id="observaciones"
                            name="observaciones"
                            rows="2"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                        >{{ old('observaciones', $servicio->observaciones) }}</textarea>
                    </div>

                    <!-- Botón Guardar -->
                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                        <button
                            type="submit"
                            class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-xs"
                        >
                            Guardar Cambios Generales
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Columna Derecha: Configuración de Duración Estimada según Especie/Peso (1 col) -->
        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Duración Estimada</h3>
                    <p class="text-xs text-slate-500">Bloque de tiempo para la agenda según especie y peso</p>
                </div>

                <!-- Lista de Reglas Configuradas -->
                <div class="space-y-2">
                    @forelse ($servicio->tiemposEstimados as $tiempo)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50 text-xs">
                            <div>
                                <span class="font-bold text-slate-800">{{ $tiempo->especie?->nombre ?? 'Especie general' }}</span>
                                <div class="text-[11px] text-slate-500">
                                    @if ($tiempo->peso_min || $tiempo->peso_max)
                                        Peso: {{ $tiempo->peso_min ?? '0' }}kg - {{ $tiempo->peso_max ?? '∞' }}kg
                                    @else
                                        Cualquier peso
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="font-mono font-bold text-primary-700 bg-primary-50 px-2 py-0.5 rounded-md">
                                    {{ $tiempo->duracion_minutos }} min
                                </span>

                                <form action="{{ route('servicios.tiempos.delete', $tiempo->id_tiempo_estimado) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="text-slate-400 hover:text-rose-600 p-1 rounded"
                                        title="Eliminar regla"
                                    >
                                        <i data-lucide="trash-2" class="size-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3 bg-slate-50/50 rounded-xl">
                            No se han definido duraciones específicas. Se asume 30 minutos estándar.
                        </p>
                    @endforelse
                </div>

                <!-- Formulario Rápido para Añadir Regla -->
                <div class="pt-3 border-t border-slate-100">
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Añadir Nueva Regla</p>
                    <form action="{{ route('servicios.tiempos.store', $servicio->id_servicio) }}" method="POST" class="space-y-3">
                        @csrf

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Especie <span class="text-rose-500">*</span></label>
                            <select
                                name="id_especie"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2 text-xs text-slate-800 focus-ring"
                            >
                                @foreach ($especies as $esp)
                                    <option value="{{ $esp->id_especie }}">{{ $esp->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Peso Mín (kg)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    name="peso_min"
                                    placeholder="0"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-1.5 text-xs font-mono text-slate-800 focus-ring"
                                />
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-600 mb-1">Peso Máx (kg)</label>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    name="peso_max"
                                    placeholder="25"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-1.5 text-xs font-mono text-slate-800 focus-ring"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-700 mb-1">Duración (Minutos) <span class="text-rose-500">*</span></label>
                            <input
                                type="number"
                                min="5"
                                max="480"
                                step="5"
                                name="duracion_minutos"
                                value="45"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>

                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-900 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors"
                        >
                            <i data-lucide="plus" class="size-3.5"></i>
                            <span>Agregar Regla de Tiempo</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection
