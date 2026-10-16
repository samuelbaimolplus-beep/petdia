<x-layout title="Registro de Accesos y Sesiones — La Petdiatría">
    <div class="space-y-6">

        <!-- Encabezado con estética Apple Liquid Glass -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex size-12 shrink-0 items-center justify-center rounded-2xl bg-primary-600/10 text-primary-600 ring-1 ring-primary-500/20 shadow-xs dark:bg-primary-500/20 dark:text-primary-400">
                    <i data-lucide="history" class="size-6"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Auditoría de Seguridad</span>
                        <x-badge tone="primary">Ciclo 1</x-badge>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white sm:text-3xl">
                        Registro de Accesos al Sistema
                    </h1>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a
                    href="{{ route('usuarios.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white/70 px-4 py-2.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-white transition-all dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-200"
                >
                    <i data-lucide="users" class="size-4"></i>
                    <span>Administrar Usuarios</span>
                </a>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas KPI de Accesos -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="liquid-glass-card rounded-2xl p-4 border border-slate-200/70 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Conexiones</p>
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ number_format($totalAccesos) }}</h3>
                    </div>
                    <div class="size-10 rounded-xl bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i data-lucide="log-in" class="size-5"></i>
                    </div>
                </div>
            </div>

            <div class="liquid-glass-card rounded-2xl p-4 border border-slate-200/70 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Sesiones Activas</p>
                        <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ number_format($sesionesActivas) }}</h3>
                    </div>
                    <div class="size-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i data-lucide="activity" class="size-5"></i>
                    </div>
                </div>
            </div>

            <div class="liquid-glass-card rounded-2xl p-4 border border-slate-200/70 dark:border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Accesos Hoy</p>
                        <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ number_format($accesosHoy) }}</h3>
                    </div>
                    <div class="size-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i data-lucide="calendar" class="size-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barra de Búsqueda y Filtros -->
        <div class="liquid-glass-card rounded-2xl p-4 border border-slate-200/70 dark:border-slate-800">
            <form action="{{ route('usuarios.accesos') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Buscar usuario o IP</label>
                    <div class="relative">
                        <i data-lucide="search" class="absolute left-3 top-2.5 size-4 text-slate-400"></i>
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Usuario, rol o dirección IP..."
                            class="w-full rounded-xl border border-slate-200 bg-white/70 pl-9 pr-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Rol</label>
                    <select
                        name="rol"
                        class="w-full rounded-xl border border-slate-200 bg-white/70 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200"
                    >
                        <option value="">Todos los roles</option>
                        <option value="SUPER_ADMIN" {{ ($filters['rol'] ?? '') === 'SUPER_ADMIN' ? 'selected' : '' }}>Super-Administrador</option>
                        <option value="ADMIN" {{ ($filters['rol'] ?? '') === 'ADMIN' ? 'selected' : '' }}>Administrador</option>
                        <option value="RECEPCION" {{ ($filters['rol'] ?? '') === 'RECEPCION' ? 'selected' : '' }}>Recepcionista</option>
                        <option value="VETERINARIO" {{ ($filters['rol'] ?? '') === 'VETERINARIO' ? 'selected' : '' }}>Veterinario</option>
                        <option value="SPA" {{ ($filters['rol'] ?? '') === 'SPA' ? 'selected' : '' }}>SPA / Peluquería</option>
                        <option value="TRANSPORTE" {{ ($filters['rol'] ?? '') === 'TRANSPORTE' ? 'selected' : '' }}>Transporte / Servicio</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Fecha</label>
                    <input
                        type="date"
                        name="fecha"
                        value="{{ $filters['fecha'] ?? '' }}"
                        class="w-full rounded-xl border border-slate-200 bg-white/70 px-3 py-2 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-200"
                    />
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="flex-1 rounded-xl bg-primary-600 px-4 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors shadow-2xs"
                    >
                        Filtrar
                    </button>
                    <a
                        href="{{ route('usuarios.accesos') }}"
                        class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-500 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-400 dark:hover:bg-slate-800"
                        title="Limpiar filtros"
                    >
                        <i data-lucide="rotate-ccw" class="size-4"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabla de Registros de Accesos -->
        <div class="liquid-glass-card rounded-2xl border border-slate-200/70 overflow-hidden dark:border-slate-800">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/70 text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100 dark:bg-slate-900/70 dark:border-slate-800">
                        <tr>
                            <th class="px-5 py-3.5">Usuario</th>
                            <th class="px-4 py-3.5">Rol Institucional</th>
                            <th class="px-4 py-3.5">Fecha Inicio</th>
                            <th class="px-4 py-3.5">Hora Inicio</th>
                            <th class="px-4 py-3.5">Cierre de Sesión</th>
                            <th class="px-4 py-3.5">Estado Sesión</th>
                            <th class="px-5 py-3.5">IP / Dispositivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($accesos as $item)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-primary-700 font-bold text-xs dark:bg-primary-900/40 dark:text-primary-300">
                                            {{ strtoupper(substr($item->nombre_usuario, 0, 2)) }}
                                        </div>
                                        <div>
                                            <span class="font-bold text-slate-900 dark:text-white block">{{ $item->nombre_usuario }}</span>
                                            <span class="text-[11px] text-slate-400">
                                                {{ $item->usuario?->persona ? trim("{$item->usuario->persona->nombre} {$item->usuario->persona->apellido1}") : 'Usuario del sistema' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    @php
                                        $r = strtoupper((string)$item->rol);
                                    @endphp
                                    @if(str_contains($r, 'SUPER'))
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800">
                                            <i data-lucide="shield-check" class="size-3"></i>
                                            Super-Admin
                                        </span>
                                    @elseif(str_contains($r, 'ADMIN'))
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200/80 dark:bg-indigo-900/30 dark:text-indigo-300 dark:border-indigo-800">
                                            <i data-lucide="shield" class="size-3"></i>
                                            Administrador
                                        </span>
                                    @elseif(str_contains($r, 'VET'))
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/80 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800">
                                            <i data-lucide="stethoscope" class="size-3"></i>
                                            Veterinario
                                        </span>
                                    @elseif(str_contains($r, 'RECEP'))
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/80 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800">
                                            <i data-lucide="calendar" class="size-3"></i>
                                            Recepcionista
                                        </span>
                                    @elseif(str_contains($r, 'SPA') || str_contains($r, 'ESTET'))
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold bg-pink-50 text-pink-700 border border-pink-200/80 dark:bg-pink-900/30 dark:text-pink-300 dark:border-pink-800">
                                            <i data-lucide="sparkles" class="size-3"></i>
                                            SPA / Peluquería
                                        </span>
                                    @elseif(str_contains($r, 'TRANS'))
                                        <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold bg-purple-50 text-purple-700 border border-purple-200/80 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800">
                                            <i data-lucide="truck" class="size-3"></i>
                                            Transporte
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-lg px-2.5 py-1 text-[11px] font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ $item->rol ?: 'General' }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5 font-semibold text-slate-700 dark:text-slate-300">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="size-3.5 text-slate-400"></i>
                                        <span>{{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5 font-mono text-slate-700 dark:text-slate-300">
                                    <div class="flex items-center gap-1.5">
                                        <i data-lucide="clock" class="size-3.5 text-slate-400"></i>
                                        <span>{{ $item->hora_ingreso }}</span>
                                    </div>
                                </td>

                                <td class="px-4 py-3.5">
                                    @if ($item->fecha_hora_cierre)
                                        <div class="space-y-0.5">
                                            <span class="font-semibold text-slate-700 dark:text-slate-300 block">
                                                {{ \Carbon\Carbon::parse($item->fecha_hora_cierre)->format('d/m/Y') }}
                                            </span>
                                            <span class="font-mono text-[11px] text-slate-400 block">
                                                {{ \Carbon\Carbon::parse($item->fecha_hora_cierre)->format('H:i:s') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            Sesión en curso
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3.5">
                                    @if ($item->estado === 'ACTIVA')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                                            <i data-lucide="check-circle-2" class="size-3"></i>
                                            ACTIVA
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                            <i data-lucide="log-out" class="size-3"></i>
                                            CERRADA
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-3.5">
                                    <div class="space-y-0.5">
                                        <span class="font-mono text-[11px] text-slate-700 dark:text-slate-300 block">
                                            {{ $item->ip_address ?: '127.0.0.1' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 truncate max-w-xs block" title="{{ $item->user_agent }}">
                                            {{ Str::limit($item->user_agent ?: 'Navegador web', 40) }}
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="shield-alert" class="size-8 text-slate-300 mb-2"></i>
                                        <p class="font-medium text-sm">No se encontraron registros de accesos con los filtros actuales.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($accesos->hasPages())
                <div class="border-t border-slate-100 px-5 py-3.5 bg-slate-50/50 dark:bg-slate-900/50 dark:border-slate-800">
                    {{ $accesos->links() }}
                </div>
            @endif
        </div>

    </div>
</x-layout>
