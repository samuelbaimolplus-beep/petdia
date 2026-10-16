@extends('layouts.app')

@section('title', 'Reporte de Pacientes Atendidos — La Petdiatría')

@section('content')
<div class="space-y-6">
    <!-- Header de navegación y acciones -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition-colors">Reportes</a>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Pacientes Atendidos</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reporte de Pacientes Atendidos</h1>
            <p class="text-sm text-slate-500">Mascotas atendidas en clínica y estética con distinción de atenciones regulares y de urgencia</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('reportes.exportar', array_merge(['tipo' => 'pacientes_atendidos'], request()->query())) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exportar CSV
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- KPIs de Pacientes Atendidos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pacientes Únicos -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Pacientes Únicos</span>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $pacientesUnicos }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Sin contar duplicados</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>

        <!-- Total Atenciones Realizadas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Atenciones</span>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $totalAtenciones }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Eventos asistenciales</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
        </div>

        <!-- Urgencias Médicas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Atenciones Urgentes</span>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $totalUrgencias }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Sin cita previa / Triaje</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
        </div>

        <!-- Regulares / Programadas -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Atenciones Regulares</span>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalRegulares }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Consultas o agenda normal</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('reportes.pacientes-atendidos') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $desde->toDateString() }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tipo de Atención</label>
                <select name="tipo" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todos los tipos</option>
                    <option value="URGENCIA" {{ ($filters['tipo'] ?? '') === 'URGENCIA' ? 'selected' : '' }}>Solo Urgencias</option>
                    <option value="REGULAR" {{ ($filters['tipo'] ?? '') === 'REGULAR' ? 'selected' : '' }}>Solo Regulares / Consultas</option>
                    <option value="CLINICA" {{ ($filters['tipo'] ?? '') === 'CLINICA' ? 'selected' : '' }}>Clínica Veterinaria</option>
                    <option value="ESTETICA" {{ ($filters['tipo'] ?? '') === 'ESTETICA' ? 'selected' : '' }}>Peluquería & Spa</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Profesional</label>
                <select name="profesional" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todos los profesionales</option>
                    @foreach($profesionales as $prof)
                        <option value="{{ $prof->id_persona }}" {{ (string)($filters['profesional'] ?? '') === (string)$prof->id_persona ? 'selected' : '' }}>
                            Dr(a). {{ $prof->persona?->nombre }} {{ $prof->persona?->apellido1 }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Especie</label>
                <select name="especie" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todas las especies</option>
                    @foreach($especies as $esp)
                        <option value="{{ $esp->id_especie }}" {{ (string)($filters['especie'] ?? '') === (string)$esp->id_especie ? 'selected' : '' }}>
                            {{ $esp->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-xs">
                    Filtrar
                </button>
                <a href="{{ route('reportes.pacientes-atendidos') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors" title="Limpiar">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Listado de Atenciones Reales -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <th class="px-5 py-3.5">ID / Fecha</th>
                        <th class="px-5 py-3.5">Paciente</th>
                        <th class="px-5 py-3.5">Propietario / Tutor</th>
                        <th class="px-5 py-3.5">Tipo de Atención</th>
                        <th class="px-5 py-3.5">Profesionales</th>
                        <th class="px-5 py-3.5">Motivo / Evaluación</th>
                        <th class="px-5 py-3.5">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($atenciones as $at)
                        @php
                            $pac = $at->paciente;
                            $prop = $pac?->propietario?->persona;
                            $isUrgente = $at->origen === 'URGENCIA' || ($at->atencionVeterinaria && $at->atencionVeterinaria->tipo === 'EMERGENCIA');
                            $motivo = $at->atencionVeterinaria?->motivo_consulta ?: ($at->atencionEstetica?->modalidad ?: $at->observaciones);
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-900">#{{ $at->id_atencion }}</span>
                                <div class="text-[11px] text-slate-400">{{ \Carbon\Carbon::parse($at->fecha_hora_inicio)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($pac)
                                    <div class="font-semibold text-slate-900">{{ $pac->nombre }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $pac->raza?->especie?->nombre ?? 'N/A' }} &bull; {{ $pac->raza?->nombre ?? 'N/A' }}</div>
                                @else
                                    <span class="text-slate-400 italic">No identificado</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($prop)
                                    <div class="font-medium text-slate-800">{{ $prop->nombre }} {{ $prop->apellido1 }}</div>
                                    <div class="text-[11px] text-slate-400">Tel: {{ $prop->telefono ?? 'N/A' }} &bull; CI: {{ $prop->documento ?? 'N/A' }}</div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($isUrgente)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <svg class="w-3.5 h-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                        URGENCIA
                                    </span>
                                @elseif($at->atencionVeterinaria)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Clínica Regular
                                    </span>
                                @elseif($at->atencionEstetica)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-100">
                                        Estética / Spa
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">
                                        General
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-slate-700">
                                @forelse($at->participaciones as $part)
                                    <div class="text-xs">
                                        Dr(a). {{ $part->profesional?->persona?->nombre }} {{ $part->profesional?->persona?->apellido1 }}
                                        <span class="text-[10px] text-slate-400">({{ $part->rol_participacion ?: 'Principal' }})</span>
                                    </div>
                                @empty
                                    <span class="text-slate-400">Sin asignar</span>
                                @endforelse
                            </td>
                            <td class="px-5 py-3.5 max-w-xs truncate" title="{{ $motivo }}">
                                {{ $motivo ?: 'Sin observaciones registradas' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $at->estado === 'COMPLETADA' || $at->estado === 'FINALIZADA' ? 'bg-emerald-50 text-emerald-700' : ($at->estado === 'EN_CURSO' || $at->estado === 'EN_PROCESO' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $at->estado }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <p class="font-semibold text-slate-600">No se encontraron pacientes atendidos</p>
                                    <p class="text-xs text-slate-400 mt-1">Ajusta los filtros de fecha, tipo o profesional.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($atenciones->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $atenciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
