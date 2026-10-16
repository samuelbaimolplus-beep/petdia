@extends('layouts.app')

@php
    $paciente = $internacion->atencionVeterinaria?->atencion?->paciente;
    $propietario = $paciente?->propietario?->persona;
    $esActiva = ($internacion->estado === 'ACTIVA');
@endphp

@section('title', 'Evolución de Internación: ' . ($paciente?->nombre ?? 'Paciente') . ' — La Petdiatría')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto" x-data="{
    evolucionModalOpen: false,
    altaModalOpen: false,
    cambiarJaulaModalOpen: false
}">

    <!-- Breadcrumb y Acciones Superiores -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400">
                <a href="{{ route('internaciones.index') }}" class="hover:text-primary-600 transition-colors">Hospitalización</a>
                <span>/</span>
                <span class="text-slate-600 font-medium">Internación #{{ $internacion->id_internacion }}</span>
            </div>
            <div class="flex items-center gap-3 mt-1">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    {{ $paciente?->nombre ?? 'Paciente' }}
                </h1>
                @if ($esActiva)
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-200">
                        <span class="size-2 rounded-full bg-rose-500 animate-pulse"></span>
                        Hospitalizado · Día {{ $diasInternado }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                        <i data-lucide="check" class="size-3.5"></i>
                        Alta Médica ({{ \Carbon\Carbon::parse($internacion->fecha_hora_alta)->format('d/m/Y') }})
                    </span>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a
                href="{{ route('internaciones.index') }}"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
            >
                <i data-lucide="arrow-left" class="size-4 text-slate-400"></i>
                <span>Volver a Caniles</span>
            </a>

            @if ($paciente)
                <a
                    href="{{ route('historial.show', $paciente->id_paciente) }}"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                >
                    <i data-lucide="clipboard-list" class="size-4 text-slate-500"></i>
                    <span>Historial Clínico</span>
                </a>
            @endif

            @if ($esActiva)
                <button
                    type="button"
                    @click="cambiarJaulaModalOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors"
                >
                    <i data-lucide="shuffle" class="size-4 text-slate-500"></i>
                    <span>Reubicar Canil</span>
                </button>

                <button
                    type="button"
                    @click="altaModalOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100 transition-colors"
                >
                    <i data-lucide="check-circle" class="size-4"></i>
                    <span>Dar Alta Médica</span>
                </button>

                <button
                    type="button"
                    @click="evolucionModalOpen = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
                >
                    <i data-lucide="plus" class="size-4"></i>
                    <span>Registrar Evolución de Turno</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Banner Principal del Paciente Internado -->
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Datos del Paciente -->
            <div class="md:col-span-2 flex items-start gap-4">
                <div class="flex size-16 shrink-0 items-center justify-center rounded-2xl bg-primary-100/70 text-primary-700 font-bold text-2xl shadow-xs">
                    {{ substr($paciente?->nombre ?? 'P', 0, 1) }}
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-900">{{ $paciente?->nombre ?? 'Paciente' }}</h2>
                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                            {{ $paciente?->raza?->especie?->nombre ?? 'Mascota' }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        {{ $paciente?->raza?->nombre ?? 'Mestizo' }} · {{ $paciente?->sexo === 'M' ? 'Macho' : 'Hembra' }}
                        @if ($paciente?->fecha_nacimiento)
                            · {{ \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age }} años
                        @endif
                    </p>

                    @if ($propietario)
                        <div class="pt-2 flex items-center gap-3 text-xs">
                            <span class="text-slate-500">Tutor: <strong class="text-slate-800">{{ $propietario->nombre }} {{ $propietario->apellido1 }}</strong></span>
                            @if ($propietario->telefono)
                                @php
                                    $msgWp = urlencode("Hola {$propietario->nombre}, te escribimos de La Petdiatría para informarte sobre el estado de {$paciente->nombre}.");
                                @endphp
                                <a
                                    href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $propietario->telefono) }}?text={{ $msgWp }}"
                                    target="_blank"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700 hover:bg-emerald-100 transition-colors"
                                >
                                    <i data-lucide="message-circle" class="size-3.5"></i>
                                    <span>WhatsApp Tutor</span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Canil Asignado & Duración -->
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                <span class="text-[11px] font-bold uppercase text-slate-400">Canil Asignado</span>
                <p class="font-mono text-base font-bold text-slate-900 flex items-center gap-1.5">
                    <i data-lucide="door-closed" class="size-4 text-primary-600"></i>
                    {{ $internacion->jaula_numero ?? 'Sin Canil' }}
                </p>
                <div class="pt-2 text-xs text-slate-500 border-t border-slate-200/60">
                    <p>Ingreso: <strong>{{ \Carbon\Carbon::parse($internacion->fecha_hora_ingreso)->format('d/m/Y H:i') }}</strong></p>
                    @if ($internacion->fecha_hora_alta)
                        <p>Alta: <strong>{{ \Carbon\Carbon::parse($internacion->fecha_hora_alta)->format('d/m/Y H:i') }}</strong></p>
                    @endif
                </div>
            </div>

            <!-- Diagnóstico / Motivo -->
            <div class="rounded-xl bg-slate-50 p-4 border border-slate-100 space-y-1">
                <span class="text-[11px] font-bold uppercase text-slate-400">Diagnóstico de Ingreso</span>
                <p class="text-xs font-semibold text-slate-800 line-clamp-3">
                    {{ $internacion->motivo }}
                </p>
                @if ($internacion->observaciones)
                    <p class="text-[11px] text-slate-500 italic mt-1 line-clamp-2">
                        {{ $internacion->observaciones }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- TARJETAS DE CONSTANTES VITALES ACTUALES (ÚLTIMO CHEQUEO) -->
    <div>
        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Último Control Clínico Registrado</h3>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <!-- Temperatura -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase">Temperatura</span>
                    <i data-lucide="thermometer" class="size-4 text-rose-500"></i>
                </div>
                <p class="mt-2 text-xl font-bold font-mono text-slate-900">
                    {{ $ultimoSeguimiento?->temperatura ? $ultimoSeguimiento->temperatura . ' °C' : '—' }}
                </p>
                <span class="text-[10px] text-slate-400">Ref: 38.0 - 39.2 °C</span>
            </div>

            <!-- Frecuencia Cardíaca -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase">Frec. Cardíaca</span>
                    <i data-lucide="heart-pulse" class="size-4 text-rose-500"></i>
                </div>
                <p class="mt-2 text-xl font-bold font-mono text-slate-900">
                    {{ $ultimoSeguimiento?->frecuencia_cardiaca ? $ultimoSeguimiento->frecuencia_cardiaca . ' lpm' : '—' }}
                </p>
                <span class="text-[10px] text-slate-400">Pulsaciones / min</span>
            </div>

            <!-- Frecuencia Respiratoria -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase">Frec. Resp.</span>
                    <i data-lucide="wind" class="size-4 text-cyan-500"></i>
                </div>
                <p class="mt-2 text-xl font-bold font-mono text-slate-900">
                    {{ $ultimoSeguimiento?->frecuencia_respiratoria ? $ultimoSeguimiento->frecuencia_respiratoria . ' rpm' : '—' }}
                </p>
                <span class="text-[10px] text-slate-400">Respiraciones / min</span>
            </div>

            <!-- Peso Actual -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase">Peso Control</span>
                    <i data-lucide="scale" class="size-4 text-primary-500"></i>
                </div>
                <p class="mt-2 text-xl font-bold font-mono text-slate-900">
                    {{ $ultimoSeguimiento?->peso ? $ultimoSeguimiento->peso . ' kg' : '—' }}
                </p>
                <span class="text-[10px] text-slate-400">Balanza hospital</span>
            </div>

            <!-- Estado de Ánimo -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase">Ánimo</span>
                    <i data-lucide="smile" class="size-4 text-amber-500"></i>
                </div>
                <p class="mt-2 text-sm font-bold text-slate-900 truncate">
                    {{ $ultimoSeguimiento?->estado_animo ?? 'Estable' }}
                </p>
                <span class="text-[10px] text-slate-400">Comportamiento</span>
            </div>

            <!-- Alimentación / Hidratación -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 shadow-2xs">
                <div class="flex items-center justify-between text-slate-400">
                    <span class="text-[11px] font-bold uppercase">Alimentación</span>
                    <i data-lucide="utensils" class="size-4 text-emerald-500"></i>
                </div>
                <p class="mt-2 text-sm font-bold text-slate-900 truncate">
                    {{ $ultimoSeguimiento?->alimentacion ?? 'Normal' }}
                </p>
                <span class="text-[10px] text-slate-400">{{ $ultimoSeguimiento?->hidratacion ?? 'Hidratado' }}</span>
            </div>
        </div>
    </div>

    <!-- LÍNEA DE TIEMPO DE EVOLUCIÓN CLÍNICA Y TURNOS MÉDICOS -->
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-xs overflow-hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="activity" class="size-4 text-primary-600"></i>
                    Bitácora de Evolución por Turnos ({{ $internacion->seguimientos->count() }})
                </h3>
                <p class="text-xs text-slate-500">Registro cronológico de signos vitales, administración de fármacos y observaciones médicas</p>
            </div>

            @if ($esActiva)
                <button
                    type="button"
                    @click="evolucionModalOpen = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-primary-50 px-3 py-1.5 text-xs font-semibold text-primary-700 hover:bg-primary-100 transition-colors"
                >
                    <i data-lucide="plus" class="size-3.5"></i>
                    <span>Nuevo Registro</span>
                </button>
            @endif
        </div>

        <div class="p-6">
            @if ($internacion->seguimientos->isEmpty())
                <div class="py-12 text-center">
                    <div class="flex size-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-400 mx-auto mb-3">
                        <i data-lucide="clock" class="size-6"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800">Sin evoluciones de turno aún</h4>
                    <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
                        Registra el primer control de signos vitales y cuidados médicos administrados al paciente en su canil.
                    </p>
                    @if ($esActiva)
                        <button
                            type="button"
                            @click="evolucionModalOpen = true"
                            class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white shadow-xs hover:bg-primary-700 transition-colors"
                        >
                            <i data-lucide="plus" class="size-3.5"></i>
                            <span>Registrar Primer Turno</span>
                        </button>
                    @endif
                </div>
            @else
                <div class="relative pl-6 space-y-8 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                    @foreach ($internacion->seguimientos as $seg)
                        <div class="relative">
                            <!-- Bullet en la línea de tiempo -->
                            <div class="absolute -left-[27px] top-1 size-3 rounded-full border-2 border-white bg-primary-600 shadow-2xs"></div>

                            <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 hover:bg-white hover:shadow-xs transition-all">
                                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 pb-3 border-b border-slate-200/60">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-slate-900">
                                            {{ \Carbon\Carbon::parse($seg->fecha_hora)->format('d/m/Y · H:i') }}
                                        </span>
                                        <span class="text-slate-400">({{ \Carbon\Carbon::parse($seg->fecha_hora)->diffForHumans() }})</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-600 font-medium">
                                        <i data-lucide="user" class="size-3.5 text-slate-400"></i>
                                        <span>Dr(a). {{ $seg->profesional?->persona?->nombre ?? 'Veterinario' }} {{ $seg->profesional?->persona?->apellido1 ?? '' }}</span>
                                    </div>
                                </div>

                                <!-- Grid de Signos Vitales de este turno -->
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    @if ($seg->temperatura)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 font-semibold text-slate-700 border border-slate-200 shadow-2xs">
                                            🌡 {{ $seg->temperatura }} °C
                                        </span>
                                    @endif
                                    @if ($seg->frecuencia_cardiaca)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 font-semibold text-slate-700 border border-slate-200 shadow-2xs">
                                            💓 {{ $seg->frecuencia_cardiaca }} lpm
                                        </span>
                                    @endif
                                    @if ($seg->frecuencia_respiratoria)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 font-semibold text-slate-700 border border-slate-200 shadow-2xs">
                                            🫁 {{ $seg->frecuencia_respiratoria }} rpm
                                        </span>
                                    @endif
                                    @if ($seg->peso)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1 font-semibold text-slate-700 border border-slate-200 shadow-2xs">
                                            ⚖ {{ $seg->peso }} kg
                                        </span>
                                    @endif
                                    @if ($seg->estado_animo)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1 font-semibold text-amber-800 border border-amber-200">
                                            Ánimo: {{ $seg->estado_animo }}
                                        </span>
                                    @endif
                                    @if ($seg->alimentacion)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-800 border border-emerald-200">
                                            Comida: {{ $seg->alimentacion }}
                                        </span>
                                    @endif
                                    @if ($seg->deposiciones)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
                                            Heces: {{ $seg->deposiciones }}
                                        </span>
                                    @endif
                                    @if ($seg->orina)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2.5 py-1 font-semibold text-slate-700">
                                            Orina: {{ $seg->orina }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Cuidados Médicos & Farmacología administrada -->
                                @if ($seg->cuidados)
                                    <div class="mt-3 pt-2 text-xs">
                                        <p class="font-bold text-slate-700 uppercase text-[10px]">Tratamiento / Fluidoterapia / Medicación:</p>
                                        <p class="text-slate-800 mt-0.5 whitespace-pre-line">{{ $seg->cuidados }}</p>
                                    </div>
                                @endif

                                <!-- Observaciones clínicas -->
                                @if ($seg->observaciones)
                                    <div class="mt-2 text-xs text-slate-600 italic bg-white p-2 rounded-lg border border-slate-100">
                                        "{{ $seg->observaciones }}"
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- MODAL 1: REGISTRAR EVOLUCIÓN DE TURNO -->
    <div
        x-show="evolucionModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="evolucionModalOpen = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="evolucionModalOpen = false"></div>

            <div
                class="relative w-full max-w-2xl rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                x-show="evolucionModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-primary-50 text-primary-600">
                            <i data-lucide="clipboard-pen" class="size-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Registrar Evolución de Turno</h3>
                            <p class="text-xs text-slate-500">Signos vitales, hidratación y notas de guardia para {{ $paciente?->nombre }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="evolucionModalOpen = false"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                    >
                        <i data-lucide="x" class="size-4"></i>
                    </button>
                </div>

                <form action="{{ route('internaciones.seguimiento.store', $internacion->id_internacion) }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <!-- Profesional responsable -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Médico Veterinario de Guardia</label>
                        <select
                            name="id_profesional"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-800 focus-ring"
                        >
                            @foreach ($profesionales as $prof)
                                <option value="{{ $prof->id_persona }}">
                                    Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }} ({{ $prof->especialidad ?? 'Veterinario' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Constantes vitales (4 cols) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Temp (°C)</label>
                            <input
                                type="number"
                                step="0.1"
                                min="30"
                                max="45"
                                name="temperatura"
                                placeholder="Ej: 38.5"
                                class="w-full rounded-xl border border-slate-200 p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">FC (lpm)</label>
                            <input
                                type="number"
                                min="30"
                                max="300"
                                name="frecuencia_cardiaca"
                                placeholder="Ej: 110"
                                class="w-full rounded-xl border border-slate-200 p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">FR (rpm)</label>
                            <input
                                type="number"
                                min="5"
                                max="150"
                                name="frecuencia_respiratoria"
                                placeholder="Ej: 24"
                                class="w-full rounded-xl border border-slate-200 p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Peso (kg)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0.1"
                                max="120"
                                name="peso"
                                placeholder="Ej: 14.5"
                                class="w-full rounded-xl border border-slate-200 p-2 text-xs font-mono font-bold text-slate-900 focus-ring"
                            />
                        </div>
                    </div>

                    <!-- Estado clínico y funciones (4 cols) -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ánimo</label>
                            <select name="estado_animo" class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus-ring">
                                <option value="Alerta y Activo">Alerta y Activo</option>
                                <option value="Tranquilo / Calmo">Tranquilo / Calmo</option>
                                <option value="Decaído / Letárgico">Decaído / Letárgico</option>
                                <option value="Comatoso / Crítico">Comatoso / Crítico</option>
                                <option value="Agresivo / Ansioso">Agresivo / Ansioso</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Alimentación</label>
                            <select name="alimentacion" class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus-ring">
                                <option value="Come voluntario">Come voluntario</option>
                                <option value="Apetito regular">Apetito regular</option>
                                <option value="Anorexia">Anorexia</option>
                                <option value="Alimentación forzada">Alimentación forzada</option>
                                <option value="En ayuno médico">En ayuno médico</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Deposiciones</label>
                            <select name="deposiciones" class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus-ring">
                                <option value="Normal">Normal</option>
                                <option value="Blandas">Blandas</option>
                                <option value="Diarrea">Diarrea</option>
                                <option value="Melena / Con sangre">Melena / Con sangre</option>
                                <option value="Sin defecar">Sin defecar</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Micción</label>
                            <select name="orina" class="w-full rounded-xl border border-slate-200 p-2 text-xs text-slate-800 focus-ring">
                                <option value="Normal">Normal</option>
                                <option value="Hematuria (sangre)">Hematuria (sangre)</option>
                                <option value="Anuria (sin orinar)">Anuria (sin orinar)</option>
                                <option value="Poliuria">Poliuria</option>
                            </select>
                        </div>
                    </div>

                    <!-- Cuidados y Medicación -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tratamiento Administrado / Sueroterapia</label>
                        <textarea
                            name="cuidados"
                            rows="2"
                            placeholder="Ej: Se aplicó Cefazolina 500mg IV + Tramadol 25mg SC. Suero a 20 ml/h..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                        ></textarea>
                    </div>

                    <!-- Observaciones de Guardia -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Comentarios Médicos y Novedades del Turno</label>
                        <textarea
                            name="observaciones"
                            rows="2"
                            placeholder="Detalla cualquier novedad, respuesta al tratamiento o instrucciones para el siguiente turno..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="evolucionModalOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-xs"
                        >
                            <i data-lucide="check" class="size-4"></i>
                            <span>Guardar Evolución</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 2: DAR ALTA MÉDICA -->
    <div
        x-show="altaModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="altaModalOpen = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="altaModalOpen = false"></div>

            <div
                class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                x-show="altaModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="award" class="size-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Otorgar Alta Médica</h3>
                        <p class="text-xs text-slate-500">Paciente {{ $paciente?->nombre }} · Canil {{ $internacion->jaula_numero }}</p>
                    </div>
                </div>

                <form action="{{ route('internaciones.alta', $internacion->id_internacion) }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div class="rounded-xl bg-emerald-50/60 p-3 text-xs text-emerald-900 border border-emerald-200">
                        <p class="font-bold">✔ Al confirmar el alta:</p>
                        <p class="mt-0.5">El estado de la internación pasará a <strong>ALTA</strong> y el canil <strong>{{ $internacion->jaula_numero }}</strong> quedará liberado automáticamente para nuevos pacientes.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Resumen Clínico de Egreso e Indicaciones Domiciliarias <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            name="observaciones_alta"
                            required
                            rows="4"
                            placeholder="Ej: Paciente estable con hidratación normal. Se da de alta médica con tratamiento ambulatorio: Amoxicilina 250mg cada 12h por 5 días. Control en 48h..."
                            class="w-full rounded-xl border border-slate-200 p-2.5 text-xs text-slate-800 placeholder:text-slate-400 focus-ring"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="altaModalOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors shadow-xs"
                        >
                            <i data-lucide="check-circle" class="size-4"></i>
                            <span>Confirmar Alta Médica</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL 3: CAMBIAR / REUBICAR CANIL -->
    <div
        x-show="cambiarJaulaModalOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="cambiarJaulaModalOpen = false"
    >
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="cambiarJaulaModalOpen = false"></div>

            <div
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop border border-slate-200/80"
                x-show="cambiarJaulaModalOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
                        <i data-lucide="shuffle" class="size-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Reubicar Canil</h3>
                        <p class="text-xs text-slate-500">Canil actual: {{ $internacion->jaula_numero }}</p>
                    </div>
                </div>

                <form action="{{ route('internaciones.cambiar-jaula', $internacion->id_internacion) }}" method="POST" class="mt-4 space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nuevo Canil o Área</label>
                        <select
                            name="jaula_numero"
                            required
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-mono font-semibold text-slate-800 focus-ring"
                        >
                            @foreach ($canilesDisponibles as $cn)
                                <option value="{{ $cn }}" {{ $internacion->jaula_numero === $cn ? 'selected' : '' }}>
                                    {{ $cn }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button
                            type="button"
                            @click="cambiarJaulaModalOpen = false"
                            class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 transition-colors shadow-xs"
                        >
                            <i data-lucide="check" class="size-4"></i>
                            <span>Guardar Cambio</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
