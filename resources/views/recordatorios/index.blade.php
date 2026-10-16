@extends('layouts.app')

@section('title', 'Recordatorios y Alertas Preventivas — La Petdiatría')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-500 mb-1">
                <span>Recepción y Fidelización</span>
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-slate-800 font-medium">Recordatorios</span>
            </div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Recordatorios y Alertas Preventivas</h1>
            <p class="text-sm text-slate-500">Gestión de avisos automáticos y seguimiento de vacunas, desparasitaciones y controles clínicos</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('recordatorios.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold shadow-xs hover:shadow transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Programar Recordatorio
            </a>
        </div>
    </div>

    <!-- 4 Tarjetas KPI -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Pendientes Hoy -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Vencen Hoy</span>
                <p class="text-2xl font-bold text-rose-600 mt-1">{{ $pendientesHoy }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Notificaciones para enviar</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-rose-50 border border-rose-100 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>

        <!-- Próximos 7 días -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Próximos 7 Días</span>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $proximosSieteDias }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Alertas preventivas cercanas</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>

        <!-- Total Pendientes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total en Cola</span>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $totalPendientes }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Recordatorios activos</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
        </div>

        <!-- Enviados este mes -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Enviados del Mes</span>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $enviadosMes }}</p>
                <span class="text-xs text-slate-500 mt-0.5 block">Clientes contactados</span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
        <form method="GET" action="{{ route('recordatorios.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="w-5 h-5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por mascota, propietario, teléfono o contenido..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div class="w-full md:w-44">
                <select name="tipo" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Todos los Tipos</option>
                    <option value="VACUNA" {{ request('tipo') === 'VACUNA' ? 'selected' : '' }}>Vacuna</option>
                    <option value="DESPARASITACION" {{ request('tipo') === 'DESPARASITACION' ? 'selected' : '' }}>Desparasitación</option>
                    <option value="CONTROL" {{ request('tipo') === 'CONTROL' ? 'selected' : '' }}>Control / Revisión</option>
                    <option value="CITA" {{ request('tipo') === 'CITA' ? 'selected' : '' }}>Cita Médica</option>
                    <option value="OTRO" {{ request('tipo') === 'OTRO' ? 'selected' : '' }}>Otro</option>
                </select>
            </div>
            <div class="w-full md:w-40">
                <select name="estado" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
                    <option value="">Pendientes</option>
                    <option value="PENDIENTE" {{ request('estado') === 'PENDIENTE' ? 'selected' : '' }}>Solo Pendientes</option>
                    <option value="ENVIADO" {{ request('estado') === 'ENVIADO' ? 'selected' : '' }}>Enviados</option>
                    <option value="CANCELADO" {{ request('estado') === 'CANCELADO' ? 'selected' : '' }}>Cancelados</option>
                </select>
            </div>
            <div class="w-full md:w-40">
                <input type="date" name="fecha" value="{{ request('fecha') }}" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-600">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-sm font-medium transition-colors">
                    Filtrar
                </button>
                @if(request()->hasAny(['search', 'tipo', 'estado', 'fecha']))
                    <a href="{{ route('recordatorios.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-sm font-medium transition-colors">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Tabla de Recordatorios -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
        @if($recordatorios->isEmpty())
            <x-empty-state 
                title="No hay recordatorios pendientes" 
                description="Todos los avisos de vacunas, desparasitaciones y controles están al día."
                actionLabel="Programar Nuevo Recordatorio"
                actionUrl="{{ route('recordatorios.create') }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50/75 border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                        <tr>
                            <th class="py-3 px-4">Fecha Programada</th>
                            <th class="py-3 px-4">Paciente & Tutor</th>
                            <th class="py-3 px-4">Tipo & Procedimiento</th>
                            <th class="py-3 px-4">Veterinario / Personal</th>
                            <th class="py-3 px-4">Instrucciones / Aviso</th>
                            <th class="py-3 px-4 text-center">Estado</th>
                            <th class="py-3 px-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($recordatorios as $rec)
                            @php
                                $fechaProg = \Carbon\Carbon::parse($rec->fecha_hora_programada);
                                $esHoy = $fechaProg->isToday();
                                $vencido = $fechaProg->isPast() && in_array($rec->estado, ['PENDIENTE', 'PROGRAMADO']) && !$esHoy;
                                $telefono = $rec->paciente->propietario->persona->telefono ?? '';
                                $telLimpio = preg_replace('/[^0-9]/', '', $telefono);
                                $waUrl = !empty($telLimpio) ? "https://wa.me/{$telLimpio}?text=" . urlencode($rec->mensaje) : '#';
                                
                                $prof = $rec->profesional_responsable;
                                $nombreProf = $prof?->persona ? 'Dr./a ' . trim("{$prof->persona->nombre} {$prof->persona->apellido1}") : 'Equipo Clínico';
                                $emailTrabajador = $prof?->persona?->email ?: \App\Models\Configuracion::get('clinica_email', config('mail.from.address'));
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors {{ $esHoy ? 'bg-amber-50/30' : '' }}">
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5">
                                        <span class="font-bold text-slate-900 block font-mono text-xs">
                                            {{ $fechaProg->format('d/m/Y') }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-mono">
                                            {{ $fechaProg->format('H:i') }}
                                        </span>
                                        @if($esHoy && in_array($rec->estado, ['PENDIENTE', 'PROGRAMADO']))
                                            <span class="inline-block px-1.5 py-0.5 bg-rose-100 text-rose-700 text-[10px] font-bold rounded">
                                                HOY
                                            </span>
                                        @elseif($vencido)
                                            <span class="inline-block px-1.5 py-0.5 bg-rose-50 text-rose-600 text-[10px] font-medium rounded">
                                                VENCIDO
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-slate-900 block">{{ $rec->paciente->nombre ?? 'Mascota' }}</span>
                                    <span class="text-xs text-slate-500 block">
                                        Tutor: {{ $rec->paciente->propietario->persona->nombre ?? '' }} {{ $rec->paciente->propietario->persona->apellido1 ?? '' }}
                                    </span>
                                    @if($telefono)
                                        <span class="text-[11px] text-slate-400 block font-mono">📞 {{ $telefono }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($rec->tipo === 'VACUNA')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                                            Vacuna
                                        </span>
                                    @elseif($rec->tipo === 'DESPARASITACION')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            Desparasitación
                                        </span>
                                    @elseif($rec->tipo === 'CONTROL')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            Control
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $rec->tipo }}
                                        </span>
                                    @endif
                                    @if($rec->servicio)
                                        <span class="text-[11px] text-slate-400 block mt-0.5 truncate max-w-[150px]">{{ $rec->servicio->nombre }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-medium text-slate-800 block text-xs">{{ $nombreProf }}</span>
                                    <span class="text-[11px] text-primary-600 block truncate max-w-[180px]" title="Alerta al correo: {{ $emailTrabajador }}">
                                        ✉ {{ $emailTrabajador }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-slate-600 max-w-[280px]">
                                    <p class="line-clamp-2" title="{{ $rec->mensaje }}">{{ $rec->mensaje }}</p>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    @if($rec->estado === 'ENVIADO')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            Notificado
                                        </span>
                                    @elseif(in_array($rec->estado, ['PENDIENTE', 'PROGRAMADO']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                            Pendiente
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            {{ $rec->estado }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if(in_array($rec->estado, ['PENDIENTE', 'PROGRAMADO']))
                                            <form action="{{ route('recordatorios.marcar-enviado', $rec) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="p-1.5 text-primary-600 hover:text-primary-800 hover:bg-primary-50 rounded-lg transition-colors" title="Enviar alerta al correo de {{ $nombreProf }} ({{ $emailTrabajador }})">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                </button>
                                            </form>

                                            @if(!empty($telLimpio))
                                                <a 
                                                    href="{{ $waUrl }}" 
                                                    target="_blank" 
                                                    rel="noopener noreferrer"
                                                    class="p-1.5 text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 rounded-lg transition-colors"
                                                    title="Contactar al tutor por WhatsApp ({{ $telefono }})"
                                                >
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                </a>
                                            @endif

                                            <form action="{{ route('recordatorios.cancelar', $rec) }}" method="POST" onsubmit="return confirm('¿Descartar este recordatorio?')" class="inline">
                                                @csrf
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Descartar">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">Atendido</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($recordatorios->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $recordatorios->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
