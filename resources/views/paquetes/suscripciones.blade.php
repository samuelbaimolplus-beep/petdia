@extends('layouts.app')

@section('title', 'Suscripciones a Planes — La Petdiatría')

@section('content')
<div class="space-y-6" x-data="{
    modalNuevaSuscripcion: false
}">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <a href="{{ route('paquetes.index') }}" class="hover:text-indigo-600 transition-colors">Paquetes</a>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Suscripciones</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Suscripciones de Pacientes</h1>
            <p class="text-sm text-slate-500">Monitoreo de membresías contratadas, vigencias y control de asistencias consumidas vs disponibles</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('paquetes.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Volver a Paquetes
            </a>
            <button type="button" @click="modalNuevaSuscripcion = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Asignar Suscripción
            </button>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('paquetes.suscripciones') }}" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por paciente, propietario o nombre del plan..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div class="w-full md:w-56">
                <select name="id_paquete" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todos los Paquetes</option>
                    @foreach($paquetesDisponibles as $pkg)
                        <option value="{{ $pkg->id_paquete }}" {{ request('id_paquete') == $pkg->id_paquete ? 'selected' : '' }}>
                            {{ $pkg->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-40">
                <select name="estado" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todos los Estados</option>
                    <option value="ACTIVO" {{ request('estado') === 'ACTIVO' ? 'selected' : '' }}>Activo</option>
                    <option value="CANCELADO" {{ request('estado') === 'CANCELADO' ? 'selected' : '' }}>Cancelado</option>
                    <option value="FINALIZADO" {{ request('estado') === 'FINALIZADO' ? 'selected' : '' }}>Finalizado</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-medium transition-colors">
                    Filtrar
                </button>
                @if(request('search') || request('id_paquete') || request('estado'))
                    <a href="{{ route('paquetes.suscripciones') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition-colors">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Suscripciones -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        @if($suscripciones->isEmpty())
            <x-empty-state 
                title="No se encontraron suscripciones" 
                description="Asigna membresías y paquetes de bienestar a tus pacientes para dar seguimiento a sus sesiones."
                actionLabel="Asignar Suscripción"
                actionUrl="#"
                @click.prevent="modalNuevaSuscripcion = true"
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Paciente & Propietario</th>
                            <th class="py-3 px-4">Plan / Paquete</th>
                            <th class="py-3 px-4">Vigencia</th>
                            <th class="py-3 px-4 w-64">Progreso de Asistencias</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($suscripciones as $sub)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 font-bold text-sm">
                                            {{ strtoupper(substr($sub->paciente->nombre ?? 'P', 0, 1)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 block">{{ $sub->paciente->nombre ?? 'Paciente' }}</span>
                                            <span class="text-xs text-slate-400">
                                                Propietario: {{ $sub->paciente->propietario->persona->nombre ?? '—' }} {{ $sub->paciente->propietario->persona->apellido1 ?? '' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-semibold text-slate-800 block">{{ $sub->paquete->nombre }}</span>
                                    <span class="text-xs text-indigo-600 font-mono font-medium">Bs. {{ number_format($sub->paquete->precio, 2) }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    <div class="space-y-0.5">
                                        <p class="text-slate-700">Inicio: <span class="font-medium">{{ \Carbon\Carbon::parse($sub->fecha_inicio)->format('d/m/Y') }}</span></p>
                                        <p class="text-slate-400">Fin: <span class="font-medium">{{ $sub->fecha_fin ? \Carbon\Carbon::parse($sub->fecha_fin)->format('d/m/Y') : 'Indefinido' }}</span></p>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-semibold text-slate-700">{{ $sub->asistencias_consumidas }} de {{ $sub->total_asistencias }} sesiones</span>
                                            <span class="font-mono text-slate-500 font-medium">{{ $sub->porcentaje_consumido }}%</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                            <div 
                                                class="h-full rounded-full transition-all duration-500 {{ $sub->porcentaje_consumido >= 100 ? 'bg-amber-500' : 'bg-indigo-600' }}"
                                                style="width: {{ $sub->porcentaje_consumido }}%"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($sub->estado === 'ACTIVO')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Activa
                                        </span>
                                    @elseif($sub->estado === 'FINALIZADO')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            Finalizada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $sub->estado }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if($sub->estado === 'ACTIVO')
                                        <form action="{{ route('paquetes.suscripciones.cancelar', $sub) }}" method="POST" onsubmit="return confirm('¿Deseas cancelar esta suscripción?')" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-rose-600 hover:text-rose-800 text-xs font-semibold hover:bg-rose-50 rounded-lg transition-colors">
                                                Cancelar
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($suscripciones->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $suscripciones->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- Modal Nueva Suscripción -->
    <div x-show="modalNuevaSuscripcion" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-cloak>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="modalNuevaSuscripcion = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-slate-100">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">Asignar Suscripción a Mascota</h3>
                    <button type="button" @click="modalNuevaSuscripcion = false" class="text-slate-400 hover:text-slate-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form action="{{ route('paquetes.suscripciones.store') }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Paciente / Mascota *</label>
                        <select name="id_paciente" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                            <option value="">Seleccionar Mascota...</option>
                            @foreach($pacientesDisponibles as $pac)
                                <option value="{{ $pac->id_paciente }}">
                                    {{ $pac->nombre }} — {{ $pac->raza->nombre ?? 'Mestizo' }} (Dueño: {{ $pac->propietario->persona->nombre ?? '' }} {{ $pac->propietario->persona->apellido1 ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Paquete / Plan Contratado *</label>
                        <select name="id_paquete" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                            <option value="">Seleccionar Paquete...</option>
                            @foreach($paquetesDisponibles as $pkg)
                                <option value="{{ $pkg->id_paquete }}">
                                    {{ $pkg->nombre }} (Bs. {{ number_format($pkg->precio, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Fecha de Inicio *</label>
                            <input type="date" name="fecha_inicio" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Fecha Fin (Vigencia)</label>
                            <input type="date" name="fecha_fin" value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                        </div>
                    </div>
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="modalNuevaSuscripcion = false" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-medium text-slate-600 hover:bg-slate-50">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs">
                            Activar Suscripción
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
