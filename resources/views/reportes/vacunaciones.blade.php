@extends('layouts.app')

@section('title', 'Reporte de Vacunaciones — La Petdiatría')

@section('content')
<div class="space-y-6">
    <!-- Header de navegación y acciones -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('reportes.index') }}" class="hover:text-indigo-600 transition-colors">Reportes</a>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Vacunaciones</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Reporte de Vacunaciones e Inmunizaciones</h1>
            <p class="text-sm text-slate-500">Historial de vacunas aplicadas, seguimiento de refuerzos y control de lotes</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('reportes.exportar', array_merge(['tipo' => 'vacunaciones'], request()->query())) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Exportar CSV
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Imprimir
            </button>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Dosis Aplicadas</span>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalVacunas }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">En el período consultado</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Próximos Refuerzos</span>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $proximasDosisMes }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">En los próximos 30 días</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rango de Consulta</span>
                <p class="text-sm font-bold text-slate-800 mt-1">{{ $desde->format('d/m/Y') }} — {{ $hasta->format('d/m/Y') }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Filtro activo</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('reportes.vacunaciones') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Desde</label>
                <input type="date" name="desde" value="{{ $desde->toDateString() }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ $hasta->toDateString() }}" class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Mascota</label>
                <input type="text" name="paciente" value="{{ $filters['paciente'] ?? '' }}" placeholder="Nombre o ID..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tutor / Cliente</label>
                <input type="text" name="cliente" value="{{ $filters['cliente'] ?? '' }}" placeholder="Nombre o CI..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Vacuna</label>
                <input type="text" name="vacuna" value="{{ $filters['vacuna'] ?? '' }}" placeholder="Antirrábica, Séxtuple..." class="w-full text-xs px-3 py-2 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-colors shadow-xs">
                    Filtrar
                </button>
                <a href="{{ route('reportes.vacunaciones') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors" title="Limpiar filtros">
                    Limpiar
                </a>
            </div>
        </form>
    </div>

    <!-- Tabla de Registros Reales -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                        <th class="px-5 py-3.5">Fecha Aplicación</th>
                        <th class="px-5 py-3.5">Paciente</th>
                        <th class="px-5 py-3.5">Tutor / Contacto</th>
                        <th class="px-5 py-3.5">Vacuna / Biológico</th>
                        <th class="px-5 py-3.5">Vía / Sitio</th>
                        <th class="px-5 py-3.5">Próxima Dosis</th>
                        <th class="px-5 py-3.5">Profesional</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                    @forelse($vacunaciones as $vac)
                        @php
                            $proc = $vac->procedimiento;
                            $at = $proc?->atencionVeterinaria?->atencion;
                            $pac = $at?->paciente;
                            $prop = $pac?->propietario?->persona;
                            $prof = $at?->participaciones?->first()?->profesional?->persona;
                            $vacunaNombre = $proc?->descripcion ?: ($vac->administracionMedicamento?->usoProductoAtencion?->producto?->nombre ?? 'Vacuna');
                        @endphp
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <span class="font-medium text-slate-900">{{ $proc && $proc->fecha_hora ? \Carbon\Carbon::parse($proc->fecha_hora)->format('d/m/Y H:i') : 'N/A' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($pac)
                                    <div class="font-semibold text-slate-900">{{ $pac->nombre }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $pac->raza?->especie?->nombre ?? '' }} &bull; {{ $pac->raza?->nombre ?? '' }}</div>
                                @else
                                    <span class="text-slate-400 italic">No asociado</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                @if($prop)
                                    <div class="font-medium text-slate-800">{{ $prop->nombre }} {{ $prop->apellido1 }}</div>
                                    <div class="text-[11px] text-slate-400">CI: {{ $prop->documento ?? 'N/A' }} &bull; Cel: {{ $prop->telefono ?? 'N/A' }}</div>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $vacunaNombre }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">
                                {{ $vac->sitio_aplicacion ?: 'Subcutánea' }}
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($vac->proxima_dosis)
                                    @php
                                        $dias = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($vac->proxima_dosis), false);
                                    @endphp
                                    <span class="inline-flex items-center gap-1 font-semibold {{ $dias < 0 ? 'text-rose-600' : ($dias <= 7 ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ \Carbon\Carbon::parse($vac->proxima_dosis)->format('d/m/Y') }}
                                        @if($dias < 0)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-rose-50 text-rose-700">Vencida</span>
                                        @elseif($dias <= 7)
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 text-amber-700">Próxima</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">No programada</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                @if($prof)
                                    <span class="font-medium text-slate-800">Dr(a). {{ $prof->nombre }} {{ $prof->apellido1 }}</span>
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                <div class="max-w-xs mx-auto">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    <p class="font-semibold text-slate-600">No se encontraron vacunaciones</p>
                                    <p class="text-xs text-slate-400 mt-1">Intenta ajustando el rango de fechas o los criterios de búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vacunaciones->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $vacunaciones->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
