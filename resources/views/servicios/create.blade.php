@extends('layouts.app')

@section('title', 'Nuevo Servicio — La Petdiatría')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Breadcrumb y encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('servicios.index') }}" class="hover:text-primary-600 transition-colors">Servicios</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Nuevo Servicio</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Registrar Servicio</h1>
        </div>
        <a
            href="{{ route('servicios.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
        >
            <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
            <span>Volver</span>
        </a>
    </div>

    <!-- Formulario de Creación -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <form action="{{ route('servicios.store') }}" method="POST" class="space-y-4">
            @csrf

            <!-- Nombre -->
            <div>
                <label for="nombre" class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Servicio <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    value="{{ old('nombre') }}"
                    required
                    placeholder="Ej: Consulta General, Vacunación Séxtuple, Baño Terapéutico..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                />
                @error('nombre')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
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
                        <option value="VETERINARIA" {{ old('categoria') === 'VETERINARIA' ? 'selected' : '' }}>Veterinaria (Médico)</option>
                        <option value="ESTETICA" {{ old('categoria') === 'ESTETICA' ? 'selected' : '' }}>Estética / SPA</option>
                        <option value="CIRUGIA" {{ old('categoria') === 'CIRUGIA' ? 'selected' : '' }}>Cirugía / Quirófano</option>
                        <option value="LABORATORIO" {{ old('categoria') === 'LABORATORIO' ? 'selected' : '' }}>Laboratorio / Diagnóstico</option>
                        <option value="OTRO" {{ old('categoria') === 'OTRO' ? 'selected' : '' }}>Otro Servicio</option>
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
                        value="{{ old('precio_base') }}"
                        required
                        placeholder="0.00"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs font-mono font-bold text-slate-900 focus-ring"
                    />
                    @error('precio_base')
                        <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Switch Domicilio -->
            <div class="pt-2">
                <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer hover:bg-slate-50">
                    <input
                        type="checkbox"
                        name="disponible_domicilio"
                        value="1"
                        {{ old('disponible_domicilio') ? 'checked' : '' }}
                        class="size-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                    />
                    <div>
                        <span class="block text-xs font-bold text-slate-800">Habilitar Servicio a Domicilio</span>
                        <span class="block text-[11px] text-slate-500">Permite asignar este servicio para visitas veterinarias o transporte con conductor.</span>
                    </div>
                </label>
            </div>

            <!-- Duración Estimada Inicial (opcional) -->
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/30 space-y-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Duración Estimada para Agenda (Opcional)</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="id_especie_defecto" class="block text-[11px] font-semibold text-slate-700 mb-1">Especie Base</label>
                        <select
                            id="id_especie_defecto"
                            name="id_especie_defecto"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs text-slate-800 focus-ring"
                        >
                            <option value="">-- No especificar ahora --</option>
                            @foreach ($especies as $esp)
                                <option value="{{ $esp->id_especie }}">{{ $esp->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="duracion_minutos_defecto" class="block text-[11px] font-semibold text-slate-700 mb-1">Duración (Minutos)</label>
                        <input
                            type="number"
                            min="5"
                            max="480"
                            step="5"
                            id="duracion_minutos_defecto"
                            name="duracion_minutos_defecto"
                            value="{{ old('duracion_minutos_defecto', 30) }}"
                            placeholder="30"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-mono text-slate-800 focus-ring"
                        />
                    </div>
                </div>
                <p class="text-[11px] text-slate-400">Podrás afinar más duraciones según peso y raza en la pantalla de edición.</p>
            </div>

            <!-- Descripción y Observaciones -->
            <div>
                <label for="descripcion" class="block text-xs font-semibold text-slate-700 mb-1">Descripción</label>
                <textarea
                    id="descripcion"
                    name="descripcion"
                    rows="2"
                    placeholder="Descripción pública del servicio o procedimiento..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >{{ old('descripcion') }}</textarea>
            </div>

            <div>
                <label for="observaciones" class="block text-xs font-semibold text-slate-700 mb-1">Observaciones internas</label>
                <textarea
                    id="observaciones"
                    name="observaciones"
                    rows="2"
                    placeholder="Indicaciones para el personal de recepción o consultorio..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >{{ old('observaciones') }}</textarea>
            </div>

            <!-- Botones -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <a
                    href="{{ route('servicios.index') }}"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-xs"
                >
                    Guardar Servicio
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
