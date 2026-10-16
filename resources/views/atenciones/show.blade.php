@extends('layouts.app')

@section('title', 'Atención #ATN-' . str_pad($atencion->id_atencion, 4, '0', STR_PAD_LEFT) . ' — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{ finalizarModal: false, cancelarModal: false, diagModal: false, tratModal: false, servModal: false, partModal: false, obsModal: false }">
    {{-- Breadcrumb y Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('atenciones.index') }}" class="hover:text-indigo-600 transition-colors">Atenciones</a>
                <span>/</span>
                <span class="text-slate-700 font-medium">Folio #ATN-{{ str_pad($atencion->id_atencion, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Atención #ATN-{{ str_pad($atencion->id_atencion, 4, '0', STR_PAD_LEFT) }}
                </h1>
                <x-state-badge :status="$atencion->estado" />
            </div>
            <p class="text-sm text-slate-500 mt-1">
                Paciente: <span class="font-semibold text-slate-800">{{ $atencion->paciente->nombre }}</span> ({{ $atencion->paciente->especie->nombre ?? 'Especie' }}) ·
                Propietario: <span class="text-slate-700">{{ $atencion->paciente->propietario->persona->nombre }} {{ $atencion->paciente->propietario->persona->apellido1 }}</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('atenciones.index') }}" class="inline-flex items-center gap-2 px-3.5 py-2 border border-slate-200 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <span>Volver</span>
            </a>

            @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                <button type="button" @click="cancelarModal = true" class="px-3.5 py-2 border border-rose-200 text-rose-700 hover:bg-rose-50 text-sm font-semibold rounded-xl transition-colors">
                    Cancelar Atención
                </button>
                <button type="button" @click="finalizarModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <span>Finalizar Atención</span>
                </button>
            @endif

            @if ($atencion->estado === 'FINALIZADA')
                <a href="{{ route('caja.cobro', ['id_atencion' => $atencion->id_atencion]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>Pasar a Cobro en Caja</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Banner Informativo de Cierre --}}
    @if ($atencion->estado === 'FINALIZADA')
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-950">Atención Completada y Liquidada</h3>
                    <p class="text-xs text-emerald-700">Se generó el cargo total de <span class="font-bold">Bs. {{ number_format($atencion->total_final, 2) }}</span> listo para emitir comprobante de pago en el módulo de Caja.</p>
                </div>
            </div>
            <a href="{{ route('caja.cobro', ['id_atencion' => $atencion->id_atencion]) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shrink-0 transition-colors">
                Cobrar en Caja &rarr;
            </a>
        </div>
    @endif

    {{-- Tarjeta 1: Información de Tiempos y Participantes --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-5 space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Cronograma</span>
            <div>
                <p class="text-xs text-slate-500">Hora de Inicio</p>
                <p class="text-sm font-semibold text-slate-800">{{ \Carbon\Carbon::parse($atencion->fecha_hora_inicio)->isoFormat('D MMM YYYY, HH:mm') }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Hora de Cierre / Fin</p>
                <p class="text-sm font-semibold text-slate-800">
                    {{ $atencion->fecha_hora_fin ? \Carbon\Carbon::parse($atencion->fecha_hora_fin)->isoFormat('D MMM YYYY, HH:mm') : 'En curso actualmente' }}
                </p>
            </div>
            @if ($atencion->cita)
                <div class="pt-2 border-t border-slate-100">
                    <p class="text-xs text-slate-500">Cita Relacionada</p>
                    <a href="{{ route('citas.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">
                        Cita previa #{{ $atencion->cita->id_cita }}
                    </a>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-5 space-y-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Paciente y Tutor</span>
            <div>
                <p class="text-sm font-bold text-slate-900">{{ $atencion->paciente->nombre }}</p>
                <p class="text-xs text-slate-500">{{ $atencion->paciente->especie->nombre ?? 'Especie' }} · {{ $atencion->paciente->raza->nombre ?? 'Raza no especificada' }}</p>
                <p class="text-xs text-slate-400">Microchip: {{ $atencion->paciente->microchip ?? 'Sin chip' }} · Sexo: {{ $atencion->paciente->sexo ?? 'N/D' }}</p>
            </div>
            <div class="pt-2 border-t border-slate-100">
                <p class="text-xs text-slate-500">Propietario / Tutor</p>
                <a href="{{ route('clientes.show', $atencion->paciente->id_propietario) }}" class="text-xs font-medium text-indigo-600 hover:underline">
                    {{ $atencion->paciente->propietario->persona->nombre }} {{ $atencion->paciente->propietario->persona->apellido1 }}
                </a>
                <p class="text-xs text-slate-400">Tel: {{ $atencion->paciente->propietario->persona->telefono ?? 'Sin teléfono' }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-5 space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Profesionales Asignados (CU33)</span>
                @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                    <button type="button" @click="partModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                        + Asignar
                    </button>
                @endif
            </div>
            <div class="space-y-2">
                @forelse ($atencion->participaciones as $part)
                    <div class="flex items-center justify-between text-xs">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $part->profesional->persona->nombre }} {{ $part->profesional->persona->apellido1 }}</p>
                            <p class="text-slate-400">{{ $part->profesional->especialidad }}</p>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $part->rol_atencion }}
                            </span>
                            @if (! $isReadOnly && $atencion->estado === 'EN_CURSO' && $part->rol_atencion !== 'PRINCIPAL')
                                <form action="{{ route('atenciones.participantes.delete', [$atencion->id_atencion, $part->id_profesional]) }}" method="POST" onsubmit="return confirm('¿Quitar este participante?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-0.5" title="Quitar">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-400 italic">No hay profesionales asignados registrados.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tarjeta 2: Detalle Clínico o Estético --}}
    @if ($atencion->atencionVeterinaria)
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-lg bg-blue-50 text-blue-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </span>
                    <h2 class="text-base font-bold text-slate-900">Registro Clínico Veterinario</h2>
                </div>
                <a href="{{ route('historial.show', $atencion->id_paciente) }}" class="text-xs font-semibold text-indigo-600 hover:underline">
                    Ver Historial Completo &rarr;
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="sm:col-span-2">
                    <span class="text-xs font-semibold text-slate-400">Motivo de Consulta</span>
                    <p class="font-medium text-slate-800">{{ $atencion->atencionVeterinaria->motivo_consulta ?? 'Sin registrar' }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-400">Anamnesis</span>
                    <p class="text-slate-700 whitespace-pre-line">{{ $atencion->atencionVeterinaria->anamnesis ?? 'Sin anamnesis registrada' }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-400">Síntomas y Hallazgos</span>
                    <p class="text-slate-700 whitespace-pre-line">{{ $atencion->atencionVeterinaria->sintomas ?? 'Sin síntomas registrados' }}</p>
                </div>
                <div class="sm:col-span-2 bg-slate-50/70 p-3 rounded-lg border border-slate-100">
                    <span class="text-xs font-semibold text-slate-500">Conclusión / Diagnóstico Presuntivo</span>
                    <p class="font-medium text-slate-900 mt-0.5">{{ $atencion->atencionVeterinaria->conclusion ?? 'Pendiente de conclusión médica' }}</p>
                </div>
                @if ($atencion->atencionVeterinaria->fecha_reconsulta)
                    <div>
                        <span class="text-xs font-semibold text-slate-400">Fecha de Reconsulta</span>
                        <p class="font-medium text-indigo-600">{{ \Carbon\Carbon::parse($atencion->atencionVeterinaria->fecha_reconsulta)->isoFormat('D MMM YYYY') }}</p>
                    </div>
                @endif
            </div>

            {{-- Diagnósticos Clínicos (CU9) --}}
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Diagnósticos Clínicos (CU9)</span>
                        <span class="text-xs text-slate-400">({{ $atencion->atencionVeterinaria->diagnosticos->count() }})</span>
                    </div>
                    @if ($canClinical && $atencion->estado === 'EN_CURSO')
                        <button type="button" @click="diagModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            + Diagnóstico
                        </button>
                    @endif
                </div>
                @if ($atencion->atencionVeterinaria->diagnosticos->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($atencion->atencionVeterinaria->diagnosticos as $diag)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">{{ $diag->nombre }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $diag->tipo === 'DEFINITIVO' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20' }}">
                                        {{ $diag->tipo }}
                                    </span>
                                </div>
                                @if ($diag->descripcion)
                                    <p class="text-slate-600 text-[11px]">{{ $diag->descripcion }}</p>
                                @endif
                                @if ($diag->observaciones)
                                    <p class="text-slate-400 text-[10px] italic">{{ $diag->observaciones }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">No se han registrado diagnósticos clínicos.</p>
                @endif
            </div>

            {{-- Tratamientos Indicados (CU9) --}}
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tratamientos e Indicaciones (CU9)</span>
                        <span class="text-xs text-slate-400">({{ $atencion->atencionVeterinaria->tratamientos->count() }})</span>
                    </div>
                    @if ($canClinical && $atencion->estado === 'EN_CURSO')
                        <button type="button" @click="tratModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                            + Tratamiento
                        </button>
                    @endif
                </div>
                @if ($atencion->atencionVeterinaria->tratamientos->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-slate-100 bg-slate-50/75 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                    <th class="py-2 px-3">Medicamento / Terapia</th>
                                    <th class="py-2 px-3">Dosis & Frecuencia</th>
                                    <th class="py-2 px-3">Vía & Duración</th>
                                    <th class="py-2 px-3">Instrucciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($atencion->atencionVeterinaria->tratamientos as $trat)
                                    <tr>
                                        <td class="py-2 px-3 font-semibold text-slate-800">{{ $trat->medicamento }}</td>
                                        <td class="py-2 px-3 text-slate-700">{{ $trat->dosis ?: '—' }} · {{ $trat->frecuencia ?: '—' }}</td>
                                        <td class="py-2 px-3 text-slate-700">{{ $trat->via_administracion ?: 'Oral' }} · {{ $trat->duracion_dias ? $trat->duracion_dias.' días' : '—' }}</td>
                                        <td class="py-2 px-3 text-slate-500 text-[11px]">{{ $trat->instrucciones ?: ($trat->observaciones ?: '—') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">No se han registrado tratamientos médicos.</p>
                @endif
            </div>

            {{-- Procedimientos y Estudios de Laboratorio (CU10) --}}
            @if ($atencion->atencionVeterinaria->procedimientos && $atencion->atencionVeterinaria->procedimientos->isNotEmpty())
                <div class="pt-4 border-t border-slate-100 space-y-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Estudios y Procedimientos Clínicos (CU10)</span>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($atencion->atencionVeterinaria->procedimientos as $proc)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">{{ $proc->tipo_procedimiento }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700">
                                        {{ $proc->estado }}
                                    </span>
                                </div>
                                <p class="text-slate-500 text-[11px]">{{ $proc->descripcion ?: 'Procedimiento clínico' }}</p>
                                @if ($proc->cirugia)
                                    <a href="{{ route('cirugias.show', $proc->cirugia->id_procedimiento) }}" class="text-[11px] font-semibold text-indigo-600 hover:underline block mt-1">
                                        Ver Ficha Quirúrgica #{{ $proc->cirugia->id_procedimiento }} &rarr;
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @elseif ($atencion->atencionEstetica)
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
            <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                <span class="p-1.5 rounded-lg bg-violet-50 text-violet-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.757 5.757a3 3 0 014.243 0l1.414 1.414a3 3 0 010 4.243L14.12 14.12z" />
                    </svg>
                </span>
                <h2 class="text-base font-bold text-slate-900">Registro de Estética y Grooming</h2>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-xs font-semibold text-slate-400">Modalidad Realizada</span>
                    <p class="font-semibold text-slate-800">{{ $atencion->atencionEstetica->modalidad }}</p>
                </div>
                <div>
                    <span class="text-xs font-semibold text-slate-400">Estado del Pelaje al Recibir</span>
                    <p class="font-semibold text-slate-800">{{ $atencion->atencionEstetica->estado_pelaje }}</p>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-xs font-semibold text-slate-400">Instrucciones y Observaciones del Pelaje</span>
                    <p class="text-slate-700 whitespace-pre-line">{{ $atencion->atencionEstetica->caracteristicas_observadas ?? 'Sin observaciones particulares' }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tarjeta 3: Servicios Realizados y Liquidación (CU32) --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h2 class="text-base font-bold text-slate-900">Servicios Realizados y Cargos (CU32)</h2>
            @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                <button type="button" @click="servModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    + Agregar Servicio
                </button>
            @endif
        </div>

        @if ($atencion->atencionServicios->isEmpty())
            <p class="text-sm text-slate-400 italic">No se registraron servicios individuales en esta atención.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-2.5 px-4">Servicio</th>
                            <th class="py-2.5 px-4">Cantidad</th>
                            <th class="py-2.5 px-4">Precio Unitario</th>
                            <th class="py-2.5 px-4">Descuento</th>
                            <th class="py-2.5 px-4 text-right">Subtotal</th>
                            @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                                <th class="py-2.5 px-2 text-right"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($atencion->atencionServicios as $ats)
                            @php
                                $sub = ($ats->cantidad * $ats->precio_unitario) - ($ats->descuento ?? 0);
                            @endphp
                            <tr class="hover:bg-slate-50/50">
                                <td class="py-3 px-4 font-semibold text-slate-800">
                                    {{ $ats->servicio->nombre }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    {{ $ats->cantidad }}
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-600">
                                    Bs. {{ number_format($ats->precio_unitario, 2) }}
                                </td>
                                <td class="py-3 px-4 font-mono text-rose-600">
                                    {{ $ats->descuento > 0 ? '- Bs. ' . number_format($ats->descuento, 2) : '—' }}
                                </td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-900 text-right">
                                    Bs. {{ number_format(max(0, $sub), 2) }}
                                </td>
                                @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                                    <td class="py-3 px-2 text-right">
                                        <form action="{{ route('atenciones.servicios.delete', [$atencion->id_atencion, $ats->id_servicio]) }}" method="POST" onsubmit="return confirm('¿Quitar este servicio?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 p-1" title="Quitar servicio">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 bg-slate-50/50">
                            <td colspan="4" class="py-3 px-4 text-right font-bold text-slate-700">Total Final de la Atención:</td>
                            <td class="py-3 px-4 font-mono font-extrabold text-base text-indigo-600 text-right">
                                Bs. {{ number_format($atencion->total_final, 2) }}
                            </td>
                            @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                                <td></td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Tarjeta 4: Bitácora de Observaciones Clínicas y Administrativas (CU34) --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <div>
                <h2 class="text-base font-bold text-slate-900">Bitácora de Observaciones (CU34)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Diferenciación entre notas médicas clínicas y anotaciones administrativas.</p>
            </div>
            @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
                <button type="button" @click="obsModal = true" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                    + Nueva Observación
                </button>
            @endif
        </div>

        @if ($atencion->observacionesRegistros && $atencion->observacionesRegistros->isNotEmpty())
            <div class="space-y-3">
                @foreach ($atencion->observacionesRegistros as $obs)
                    <div class="p-3.5 rounded-xl border {{ $obs->tipo === 'CLINICA' ? 'border-indigo-100 bg-indigo-50/40' : 'border-amber-100 bg-amber-50/40' }} text-xs space-y-1.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $obs->tipo === 'CLINICA' ? 'bg-indigo-100 text-indigo-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $obs->tipo === 'CLINICA' ? 'Clínica / Médica' : 'Administrativa' }}
                                </span>
                                <span class="font-semibold text-slate-800">
                                    {{ $obs->usuario?->persona?->nombre ?? $obs->usuario?->nombre_usuario ?? 'Usuario' }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400">
                                {{ \Carbon\Carbon::parse($obs->fecha_registro)->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                        <p class="text-slate-800 whitespace-pre-line leading-relaxed">{{ $obs->observacion }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-xs text-slate-400 italic">No se han registrado observaciones en la bitácora de esta atención.</p>
        @endif
    </div>

    {{-- Observaciones y Recomendaciones Generales --}}
    @if ($atencion->observaciones || $atencion->recomendaciones)
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            @if ($atencion->observaciones)
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Observaciones Generales</span>
                    <p class="text-slate-700 mt-1 whitespace-pre-line">{{ $atencion->observaciones }}</p>
                </div>
            @endif
            @if ($atencion->recomendaciones)
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Recomendaciones para el Tutor</span>
                    <p class="text-slate-700 mt-1 whitespace-pre-line">{{ $atencion->recomendaciones }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- MODALES DEL SISTEMA --}}

    {{-- MODAL: NUEVO DIAGNÓSTICO (CU9) --}}
    <div x-show="diagModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="diagModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Registrar Diagnóstico Clínico (CU9)</h2>
                    <button type="button" @click="diagModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('atenciones.diagnosticos.store', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Diagnóstico *</label>
                        <input type="text" name="nombre" required placeholder="Ej. Gastroenteritis infecciosa canina" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Diagnóstico *</label>
                        <select name="tipo" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="PRESUNTIVO">Presuntivo / Sospecha inicial</option>
                            <option value="DEFINITIVO">Definitivo / Confirmado</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción / Hallazgos</label>
                        <textarea name="descripcion" rows="2" placeholder="Detalles de la patología y justificación diagnóstica..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="diagModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg">
                            Guardar Diagnóstico
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: NUEVO TRATAMIENTO (CU9) --}}
    <div x-show="tratModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="tratModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Indicar Tratamiento Farmacológico (CU9)</h2>
                    <button type="button" @click="tratModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('atenciones.tratamientos.store', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Medicamento / Principio Activo *</label>
                        <input type="text" name="medicamento" required placeholder="Ej. Amoxicilina + Ácido Clavulánico 250mg" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Dosis</label>
                            <input type="text" name="dosis" placeholder="Ej. 1 comp (12.5 mg/kg)" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Frecuencia</label>
                            <input type="text" name="frecuencia" placeholder="Ej. Cada 12 horas" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Vía de Administración</label>
                            <select name="via_administracion" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="ORAL">Oral</option>
                                <option value="SUBCUTANEA">Subcutánea</option>
                                <option value="INTRAMUSCULAR">Intramuscular</option>
                                <option value="INTRAVENOSA">Intravenosa</option>
                                <option value="TOPICA">Tópica / Oftálmica</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Duración (Días)</label>
                            <input type="number" name="duracion_dias" min="1" placeholder="Ej. 7" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Instrucciones para el Tutor</label>
                        <textarea name="instrucciones" rows="2" placeholder="Administrar con alimentos, suspender si presenta vómito..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="tratModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg">
                            Guardar Tratamiento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: AGREGAR SERVICIO (CU32) --}}
    <div x-show="servModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="servModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Agregar Servicio Realizado (CU32)</h2>
                    <button type="button" @click="servModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('atenciones.servicios.store', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Servicio *</label>
                        <select name="id_servicio" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Selecciona el servicio...</option>
                            @foreach ($servicios as $srv)
                                <option value="{{ $srv->id_servicio }}">{{ $srv->nombre }} — Bs. {{ number_format($srv->precio_base, 2) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Cantidad *</label>
                            <input type="number" name="cantidad" value="1" min="1" step="1" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Descuento (Bs.)</label>
                            <input type="number" name="descuento" value="0" min="0" step="0.01" class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="servModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg">
                            Registrar Servicio
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: ASIGNAR PROFESIONAL PARTICIPANTE (CU33) --}}
    <div x-show="partModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="partModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Asignar Profesional Participante (CU33)</h2>
                    <button type="button" @click="partModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('atenciones.participantes.store', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Profesional *</label>
                        <select name="id_profesional" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="">Selecciona el profesional habilitado...</option>
                            @foreach ($profesionales as $p)
                                <option value="{{ $p->id_persona }}">{{ $p->persona?->nombre }} {{ $p->persona?->apellido1 }} ({{ $p->especialidad }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Rol en la Atención *</label>
                        <select name="rol_atencion" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="ASISTENTE">Asistente</option>
                            <option value="CIRUJANO">Cirujano</option>
                            <option value="ANESTESISTA">Anestesista</option>
                            <option value="GROOMER">Groomer / Estilista</option>
                            <option value="PRINCIPAL">Principal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Observaciones</label>
                        <input type="text" name="observaciones" placeholder="Funciones específicas en esta atención..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500" />
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="partModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg">
                            Asignar Profesional
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: NUEVA OBSERVACIÓN CLÍNICA / ADMINISTRATIVA (CU34) --}}
    <div x-show="obsModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="obsModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200" @click.stop>
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Registrar Observación (CU34)</h2>
                    <button type="button" @click="obsModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('atenciones.observaciones.store', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo de Observación *</label>
                        <select name="tipo" required class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            @if ($canClinical)
                                <option value="CLINICA">Clínica / Médica (Restringida a médicos)</option>
                            @endif
                            <option value="ADMINISTRATIVA">Administrativa (Cobro, tutor, recepción)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Detalle de la Observación *</label>
                        <textarea name="observacion" required rows="3" placeholder="Redacta la nota u observación..." class="w-full text-xs px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="obsModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg">
                            Guardar Observación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL: FINALIZAR ATENCIÓN --}}
    @if (! $isReadOnly && $atencion->estado === 'EN_CURSO')
        <div x-show="finalizarModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="finalizarModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900">Finalizar Atención #ATN-{{ str_pad($atencion->id_atencion, 4, '0', STR_PAD_LEFT) }}</h2>
                        <button type="button" @click="finalizarModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('atenciones.finalizar', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                        @csrf
                        <p class="text-xs text-slate-500">Al finalizar la atención se registrará la hora de cierre actual y los cargos generados pasarán automáticamente a disponibilidad en Caja.</p>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Observaciones Finales</label>
                            <textarea name="observaciones" rows="2" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ $atencion->observaciones }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Recomendaciones Finales al Tutor</label>
                            <textarea name="recomendaciones" rows="2" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500">{{ $atencion->recomendaciones }}</textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" @click="finalizarModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                                Cancelar
                            </button>
                            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg">
                                Confirmar y Finalizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL: CANCELAR ATENCIÓN --}}
        <div x-show="cancelarModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" @click="cancelarModal = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h2 class="text-base font-bold text-slate-900">Cancelar Atención</h2>
                        <button type="button" @click="cancelarModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('atenciones.cancelar', $atencion->id_atencion) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo de Cancelación *</label>
                            <textarea name="motivo" required rows="3" placeholder="Indique el motivo por el cual no se concluyó la atención..." class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                            <button type="button" @click="cancelarModal = false" class="px-3.5 py-2 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50">
                                Volver
                            </button>
                            <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg">
                                Cancelar Atención
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
