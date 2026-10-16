@extends('layouts.app')

@section('title', 'Programar Servicio a Domicilio — La Petdiatría')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Breadcrumb y encabezado -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('domicilios.index') }}" class="hover:text-primary-600 transition-colors">Servicio a Domicilio</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Programar Visita</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Programar Visita o Traslado</h1>
        </div>
        <a
            href="{{ route('domicilios.index') }}"
            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
        >
            <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
            <span>Volver</span>
        </a>
    </div>

    <!-- Formulario de Solicitud -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <form action="{{ route('domicilios.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Paciente / Mascota -->
            <div>
                <label for="id_paciente" class="block text-xs font-semibold text-slate-700 mb-1">Paciente / Mascota <span class="text-rose-500">*</span></label>
                <select
                    id="id_paciente"
                    name="id_paciente"
                    required
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >
                    <option value="">-- Selecciona el paciente --</option>
                    @foreach ($pacientes as $pac)
                        <option value="{{ $pac->id_paciente }}" {{ old('id_paciente') == $pac->id_paciente ? 'selected' : '' }}>
                            🐾 {{ $pac->nombre }} — Tutor: {{ $pac->propietario?->persona?->nombre }} {{ $pac->propietario?->persona?->apellido1 }} (Tel: {{ $pac->propietario?->persona?->telefono ?? 'S/T' }})
                        </option>
                    @endforeach
                </select>
                @error('id_paciente')
                    <p class="text-[11px] text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Fecha y Hora -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="fecha" class="block text-xs font-semibold text-slate-700 mb-1">Fecha de la Visita <span class="text-rose-500">*</span></label>
                    <input
                        type="date"
                        id="fecha"
                        name="fecha"
                        value="{{ old('fecha', date('Y-m-d')) }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    />
                </div>

                <div>
                    <label for="hora" class="block text-xs font-semibold text-slate-700 mb-1">Hora Estimada de Recogida/Llegada <span class="text-rose-500">*</span></label>
                    <input
                        type="time"
                        id="hora"
                        name="hora"
                        value="{{ old('hora', '10:00') }}"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                    />
                </div>
            </div>

            <!-- Dirección y Referencia -->
            <div class="space-y-3 p-4 rounded-xl border border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <i data-lucide="map-pin" class="size-4 text-rose-500"></i>
                    <span>Ubicación del Domicilio</span>
                </div>

                <div>
                    <label for="direccion" class="block text-xs font-semibold text-slate-700 mb-1">Dirección Exacta (Calle, N°, Zona) <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        id="direccion"
                        name="direccion"
                        value="{{ old('direccion') }}"
                        required
                        placeholder="Ej: Av. Las Américas #450, Edif. Los Pinos, Dpto 3B"
                        class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus-ring"
                    />
                </div>

                <div>
                    <label for="referencia" class="block text-xs font-semibold text-slate-700 mb-1">Referencias para el Conductor</label>
                    <input
                        type="text"
                        id="referencia"
                        name="referencia"
                        value="{{ old('referencia') }}"
                        placeholder="Ej: Frente al parque, portón de rejas negras con timbre lateral"
                        class="w-full rounded-xl border border-slate-200 bg-white p-2.5 text-xs text-slate-800 focus-ring"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="distancia_estimada" class="block text-xs font-semibold text-slate-700 mb-1">Distancia Estimada (km)</label>
                        <input
                            type="number"
                            step="0.1"
                            min="0"
                            id="distancia_estimada"
                            name="distancia_estimada"
                            value="{{ old('distancia_estimada', 5.0) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-mono text-slate-800 focus-ring"
                        />
                    </div>

                    <div>
                        <label for="costo_domicilio" class="block text-xs font-semibold text-slate-700 mb-1">Costo Adicional Traslado ($)</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="costo_domicilio"
                            name="costo_domicilio"
                            value="{{ old('costo_domicilio', 30.00) }}"
                            class="w-full rounded-xl border border-slate-200 bg-white p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                        />
                    </div>
                </div>
            </div>

            <!-- Servicios Solicitados a Domicilio -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-2">Servicios Solicitados <span class="text-rose-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 border border-slate-200 rounded-xl bg-slate-50/30">
                    @foreach ($serviciosDomicilio as $serv)
                        <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-100 hover:bg-slate-50 cursor-pointer">
                            <input
                                type="checkbox"
                                name="servicios[]"
                                value="{{ $serv->id_servicio }}"
                                class="rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                            />
                            <div class="text-xs">
                                <span class="font-medium text-slate-800">{{ $serv->nombre }}</span>
                                <span class="text-slate-400 font-mono ml-1">(${{ number_format((float)$serv->precio_base, 2) }})</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Profesional Asignado -->
            <div>
                <label for="id_profesional" class="block text-xs font-semibold text-slate-700 mb-1">Profesional / Veterinario Asignado (opcional)</label>
                <select
                    id="id_profesional"
                    name="id_profesional"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >
                    <option value="">-- Conductor de traslado general / Sin veterinario --</option>
                    @foreach ($profesionales as $pro)
                        <option value="{{ $pro->id_persona }}" {{ old('id_profesional') == $pro->id_persona ? 'selected' : '' }}>
                            Dr(a). {{ $pro->persona?->nombre }} {{ $pro->persona?->apellido1 }} ({{ $pro->especialidad ?? 'General' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Observaciones -->
            <div>
                <label for="observaciones" class="block text-xs font-semibold text-slate-700 mb-1">Notas especiales de la recogida</label>
                <textarea
                    id="observaciones"
                    name="observaciones"
                    rows="2"
                    placeholder="Ej: Mascota nerviosa con vehículos, llevar transportadora grande..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50/50 p-2.5 text-xs text-slate-800 focus-ring"
                >{{ old('observaciones') }}</textarea>
            </div>

            <!-- Botones -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <a
                    href="{{ route('domicilios.index') }}"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                >
                    Cancelar
                </a>
                <button
                    type="submit"
                    class="rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-xs"
                >
                    Agendar Servicio a Domicilio
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
