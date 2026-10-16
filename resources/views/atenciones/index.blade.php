@extends('layouts.app')

@section('title', 'Atenciones — La Petdiatría')

@section('content')
<div class="space-y-6">
    {{-- Header de Sección --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Atenciones Clínicas y Estéticas</h1>
            <p class="text-sm text-slate-500 mt-1">Control de pacientes en consulta médica, procedimientos y estética canina/felina.</p>
        </div>
        @if (! $isReadOnly)
            <div class="flex items-center gap-3">
                <a href="{{ route('atenciones.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-xs transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>Iniciar Atención</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Tarjetas KPI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Atenciones Hoy</span>
                <p class="text-3xl font-bold text-slate-900 mt-1">{{ $metrics['total_hoy'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Registradas en el día</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">En Curso Ahora</span>
                <p class="text-3xl font-bold text-amber-600 mt-1">{{ $metrics['en_curso'] }}</p>
                <p class="text-xs text-slate-500 mt-1">En consultorio o estética</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Finalizadas Hoy</span>
                <p class="text-3xl font-bold text-emerald-600 mt-1">{{ $metrics['finalizadas_hoy'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Listas para caja o cobradas</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Grooming / Spa</span>
                <p class="text-3xl font-bold text-violet-600 mt-1">{{ $metrics['esteticas_hoy'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Servicios estéticos hoy</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.757 5.757a3 3 0 014.243 0l1.414 1.414a3 3 0 010 4.243L14.12 14.12z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Filtros y Buscador --}}
    <div class="bg-white p-4 rounded-xl border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('atenciones.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
            <div class="sm:col-span-4">
                <label for="search" class="block text-xs font-semibold text-slate-600 mb-1">Buscar atención</label>
                <div class="relative">
                    <input type="text" name="search" id="search" value="{{ $filters['search'] }}" placeholder="ID, mascota, CI o propietario..." class="w-full text-sm pl-9 pr-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="tipo" class="block text-xs font-semibold text-slate-600 mb-1">Tipo</label>
                <select name="tipo" id="tipo" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos los tipos</option>
                    <option value="VETERINARIA" @selected($filters['tipo'] === 'VETERINARIA')>Veterinaria</option>
                    <option value="ESTETICA" @selected($filters['tipo'] === 'ESTETICA')>Estética / Spa</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="estado" class="block text-xs font-semibold text-slate-600 mb-1">Estado</label>
                <select name="estado" id="estado" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Todos</option>
                    <option value="EN_CURSO" @selected($filters['estado'] === 'EN_CURSO')>En Curso</option>
                    <option value="FINALIZADA" @selected($filters['estado'] === 'FINALIZADA')>Finalizada</option>
                    <option value="CANCELADA" @selected($filters['estado'] === 'CANCELADA')>Cancelada</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <label for="fecha" class="block text-xs font-semibold text-slate-600 mb-1">Fecha</label>
                <input type="date" name="fecha" id="fecha" value="{{ $filters['fecha'] }}" class="w-full text-sm px-3 py-2 border border-slate-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-sm font-semibold rounded-lg transition-colors">
                    Filtrar
                </button>
                @if ($filters['search'] || $filters['tipo'] || $filters['estado'] || $filters['fecha'])
                    <a href="{{ route('atenciones.index') }}" class="p-2 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors" title="Limpiar filtros">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabla de Atenciones --}}
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if ($atenciones->isEmpty())
            <div class="p-8">
                <x-empty-state
                    title="No hay atenciones registradas"
                    description="No se encontraron atenciones con los criterios de búsqueda o filtros seleccionados."
                >
                    @if (! $isReadOnly)
                        <x-slot:action>
                            <a href="{{ route('atenciones.create') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition-colors">
                                Iniciar Nueva Atención
                            </a>
                        </x-slot:action>
                    @endif
                </x-empty-state>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Folio / Fecha</th>
                            <th class="py-3 px-4">Paciente y Dueño</th>
                            <th class="py-3 px-4">Tipo</th>
                            <th class="py-3 px-4">Profesional</th>
                            <th class="py-3 px-4">Servicios</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($atenciones as $atn)
                            @php
                                $isVet = $atn->atencionVeterinaria !== null;
                                $prof = $atn->profesionales->first();
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('atenciones.show', $atn->id_atencion) }}" class="font-mono font-bold text-indigo-600 hover:underline">
                                        #ATN-{{ str_pad($atn->id_atencion, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                    <p class="text-xs text-slate-400">
                                        {{ \Carbon\Carbon::parse($atn->fecha_hora_inicio)->isoFormat('D MMM, HH:mm') }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <p class="font-semibold text-slate-900">{{ $atn->paciente->nombre }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $atn->paciente->propietario->persona->nombre }} {{ $atn->paciente->propietario->persona->apellido1 }}
                                    </p>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($isVet)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                            </svg>
                                            Médica
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs font-medium bg-violet-50 text-violet-700">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879a3 3 0 11-4.242-4.242L10.757 5.757a3 3 0 014.243 0l1.414 1.414a3 3 0 010 4.243L14.12 14.12z" />
                                            </svg>
                                            Estética
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if ($prof)
                                        <p class="font-medium text-slate-800">{{ $prof->persona->nombre }} {{ $prof->persona->apellido1 }}</p>
                                        <p class="text-slate-400">{{ $prof->especialidad }}</p>
                                    @else
                                        <span class="text-slate-400 italic">No asignado</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @forelse ($atn->atencionServicios->take(2) as $ats)
                                        <span class="inline-block px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-medium mb-0.5">
                                            {{ $ats->servicio->nombre }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400 italic">Sin servicios</span>
                                    @endforelse
                                    @if ($atn->atencionServicios->count() > 2)
                                        <span class="text-slate-400">+{{ $atn->atencionServicios->count() - 2 }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <x-state-badge :status="$atn->estado" />
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-900">
                                    Bs. {{ number_format($atn->total_final, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('atenciones.show', $atn->id_atencion) }}" class="p-1.5 text-slate-400 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition-colors" title="Ver detalle de atención">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($atenciones->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $atenciones->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
