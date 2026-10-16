<x-layout title="Bitácora de Auditoría — La Petdiatría">
    <div
        x-data="{
            inspectModalOpen: false,
            selectedLog: null,
            openInspect(log) {
                this.selectedLog = log;
                this.inspectModalOpen = true;
                this.$nextTick(() => { if (window.lucide) window.lucide.createIcons(); });
            }
        }"
        class="space-y-6"
    >
        <x-breadcrumb :items="[
            ['label' => 'Inicio', 'url' => route('dashboard')],
            ['label' => 'Usuarios y Seguridad', 'url' => route('usuarios.index')],
            ['label' => 'Bitácora de Auditoría', 'url' => null],
        ]" />

        <!-- Encabezado Principal -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <span>Seguridad & Compliance</span>
                    <span>·</span>
                    <span>Auditoría Inmutable</span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl flex items-center gap-2.5">
                        <i data-lucide="shield-check" class="size-7 text-primary-600"></i>
                        <span>Bitácora de Auditoría y Trazabilidad</span>
                    </h1>
                </div>
                <p class="text-xs text-slate-500 mt-1">
                    Registro cronológico de operaciones sensibles, anulaciones en caja, cambios de permisos y cancelaciones del personal.
                </p>
            </div>

            <a
                href="{{ route('usuarios.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 text-xs font-semibold shadow-2xs transition-colors"
            >
                <i data-lucide="users" class="size-4 text-slate-500"></i>
                <span>Gestión de Usuarios</span>
            </a>
        </div>

        <!-- 4 Tarjetas de KPIs de Auditoría -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Eventos</span>
                    <div class="p-2 rounded-xl bg-primary-50 text-primary-600">
                        <i data-lucide="database" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalLogs) }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Registros históricos</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-indigo-600 uppercase tracking-wider">Eventos Hoy</span>
                    <div class="p-2 rounded-xl bg-indigo-50 text-indigo-600">
                        <i data-lucide="clock" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-indigo-700">{{ $logsHoy }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Actividad en la fecha</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Anulaciones de Cobro</span>
                    <div class="p-2 rounded-xl bg-rose-50 text-rose-600">
                        <i data-lucide="alert-triangle" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-rose-700">{{ $totalAnulaciones }}</div>
                <p class="text-[11px] text-rose-500 mt-0.5">Acciones de caja críticas</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">Usuarios Auditados</span>
                    <div class="p-2 rounded-xl bg-emerald-50 text-emerald-600">
                        <i data-lucide="user-check" class="size-4"></i>
                    </div>
                </div>
                <div class="mt-2 text-2xl font-bold text-slate-900">{{ $usuariosAuditados }}</div>
                <p class="text-[11px] text-slate-400 mt-0.5">Cuentas con actividad</p>
            </div>
        </div>

        <!-- Filtros de Auditoría -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-2xs">
            <form method="GET" action="{{ route('usuarios.auditoria') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Módulo -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Módulo</label>
                    <select
                        name="modulo"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                    >
                        <option value="">Todos los módulos</option>
                        @foreach ($modulos as $mod)
                            <option value="{{ $mod }}" {{ ($filters['modulo'] ?? '') === $mod ? 'selected' : '' }}>
                                {{ $mod }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Acción -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Acción</label>
                    <select
                        name="accion"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                    >
                        <option value="">Todas las acciones</option>
                        @foreach ($acciones as $acc)
                            <option value="{{ $acc }}" {{ ($filters['accion'] ?? '') === $acc ? 'selected' : '' }}>
                                {{ $acc }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Resultado -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Resultado</label>
                    <select
                        name="resultado"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                    >
                        <option value="">Todos los resultados</option>
                        <option value="EXITO" {{ ($filters['resultado'] ?? '') === 'EXITO' ? 'selected' : '' }}>EXITO</option>
                        <option value="RECHAZADO" {{ ($filters['resultado'] ?? '') === 'RECHAZADO' ? 'selected' : '' }}>RECHAZADO</option>
                        <option value="FALLIDO" {{ ($filters['resultado'] ?? '') === 'FALLIDO' ? 'selected' : '' }}>FALLIDO</option>
                    </select>
                </div>

                <!-- Usuario -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Usuario</label>
                    <select
                        name="id_usuario"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                    >
                        <option value="">Todos los usuarios</option>
                        @foreach ($usuarios as $usr)
                            <option value="{{ $usr->id_usuario }}" {{ ($filters['id_usuario'] ?? '') == $usr->id_usuario ? 'selected' : '' }}>
                                {{ $usr->nombre_usuario }} ({{ $usr->persona?->nombre ?? 'S/D' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Fecha -->
                <div>
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Fecha</label>
                    <input
                        type="date"
                        name="fecha"
                        value="{{ $filters['fecha'] ?? '' }}"
                        class="w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                    />
                </div>

                <!-- Búsqueda y Botón -->
                <div class="flex items-end gap-2">
                    <div class="relative flex-1">
                        <input
                            type="text"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Buscar en log..."
                            class="w-full rounded-xl border border-slate-200 pl-3 pr-2 py-1.5 text-xs text-slate-800 focus:border-primary-500 focus:outline-hidden"
                        />
                    </div>
                    <button
                        type="submit"
                        class="px-3 py-1.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs transition-colors shrink-0 shadow-2xs"
                    >
                        Filtrar
                    </button>
                    @if (array_filter($filters))
                        <a
                            href="{{ route('usuarios.auditoria') }}"
                            class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 text-xs transition-colors"
                            title="Limpiar filtros"
                        >
                            <i data-lucide="rotate-ccw" class="size-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla de Eventos de Auditoría -->
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/75 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="py-3 px-4">Fecha y Hora</th>
                            <th class="py-3 px-4">Usuario</th>
                            <th class="py-3 px-4">Acción</th>
                            <th class="py-3 px-4">Resultado</th>
                            <th class="py-3 px-4">Módulo</th>
                            <th class="py-3 px-4">Descripción / Entidad</th>
                            <th class="py-3 px-4">Origen (IP)</th>
                            <th class="py-3 px-4 text-right">Detalles</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-normal">
                        @forelse ($logs as $log)
                            @php
                                $accion = $log->accion;
                                $badgeClass = 'bg-slate-100 text-slate-700 ring-slate-500/20';
                                if (str_contains($accion, 'ANULAR') || str_contains($accion, 'ELIMINAR')) {
                                    $badgeClass = 'bg-rose-50 text-rose-700 ring-rose-600/20';
                                } elseif (str_contains($accion, 'CANCELAR')) {
                                    $badgeClass = 'bg-amber-50 text-amber-700 ring-amber-600/20';
                                } elseif (str_contains($accion, 'ROL') || str_contains($accion, 'USUARIO')) {
                                    $badgeClass = 'bg-purple-50 text-purple-700 ring-purple-600/20';
                                } elseif (str_contains($accion, 'CIERRE') || str_contains($accion, 'COBRO')) {
                                    $badgeClass = 'bg-blue-50 text-blue-700 ring-blue-600/20';
                                } elseif (str_contains($accion, 'CREAR')) {
                                    $badgeClass = 'bg-emerald-50 text-emerald-700 ring-emerald-600/20';
                                }

                                $res = $log->resultado ?? 'EXITO';
                                $resTone = match($res) {
                                    'EXITO' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                    'RECHAZADO' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
                                    'FALLIDO' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                    default => 'bg-slate-50 text-slate-700 ring-slate-500/20',
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <!-- Fecha y Hora -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-semibold text-slate-800">
                                        {{ $log->created_at->format('d/m/Y H:i:s') }}
                                    </span>
                                    <div class="text-[10px] text-slate-400">
                                        {{ $log->created_at->diffForHumans() }}
                                    </div>
                                </td>

                                <!-- Usuario -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    @if ($log->usuario)
                                        <div class="flex items-center gap-2">
                                            <div class="size-6 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-[10px]">
                                                {{ strtoupper(substr($log->usuario->nombre_usuario, 0, 1)) }}
                                            </div>
                                            <div>
                                                <span class="font-bold text-slate-800">{{ $log->usuario->nombre_usuario }}</span>
                                                <div class="text-[10px] text-slate-400">
                                                    {{ $log->usuario->persona?->nombre ?? 'Sistema' }}
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Sistema Automático</span>
                                    @endif
                                </td>

                                <!-- Acción -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 {{ $badgeClass }}">
                                        {{ $log->accion }}
                                    </span>
                                </td>

                                <!-- Resultado -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold ring-1 {{ $resTone }}">
                                        {{ $res }}
                                    </span>
                                </td>

                                <!-- Módulo -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 font-semibold text-slate-700 text-[11px]">
                                        <span class="size-1.5 rounded-full bg-slate-400"></span>
                                        {{ $log->modulo }}
                                    </span>
                                </td>

                                <!-- Descripción / Entidad -->
                                <td class="py-3 px-4 max-w-xs">
                                    @if ($log->descripcion)
                                        <div class="font-medium text-slate-800 truncate" title="{{ $log->descripcion }}">
                                            {{ $log->descripcion }}
                                        </div>
                                    @endif
                                    @if ($log->entidad_tipo)
                                        <span class="text-[11px] text-slate-500">
                                            {{ class_basename($log->entidad_tipo) }}
                                            @if ($log->entidad_id)
                                                <span class="font-mono text-slate-400">#{{ $log->entidad_id }}</span>
                                            @endif
                                        </span>
                                    @elseif (!$log->descripcion)
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <!-- IP -->
                                <td class="py-3 px-4 whitespace-nowrap">
                                    <span class="font-mono text-[11px] text-slate-600">{{ $log->ip_address ?: '127.0.0.1' }}</span>
                                </td>

                                <!-- Botón Inspección -->
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <button
                                        type="button"
                                        @click="openInspect(@js($log))"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 hover:text-primary-600 text-xs font-semibold shadow-2xs transition-colors"
                                    >
                                        <i data-lucide="eye" class="size-3.5"></i>
                                        <span>Inspeccionar</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="size-12 rounded-2xl bg-slate-50 text-slate-400 flex items-center justify-center mx-auto mb-2">
                                        <i data-lucide="shield" class="size-6"></i>
                                    </div>
                                    <p class="font-semibold text-slate-600 text-xs">No se encontraron eventos en este criterio de búsqueda.</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">La bitácora registra automáticamente las operaciones del sistema.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($logs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL: INSPECCIONAR PAYLOAD JSON DE AUDITORÍA -->
        <div
            x-show="inspectModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            style="display: none;"
        >
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity" @click="inspectModalOpen = false"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div
                    class="relative w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 transition-all sm:p-7"
                    @click.stop
                >
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i data-lucide="terminal" class="size-5 text-primary-600"></i>
                                <span>Detalles del Evento de Auditoría</span>
                            </h3>
                            <p class="text-xs text-slate-500 mt-0.5" x-text="'Evento #' + (selectedLog ? selectedLog.id : '')"></p>
                        </div>
                        <button
                            type="button"
                            @click="inspectModalOpen = false"
                            class="rounded-xl p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors"
                        >
                            <i data-lucide="x" class="size-5"></i>
                        </button>
                    </div>

                    <template x-if="selectedLog">
                        <div class="space-y-4 mt-5 text-xs">
                            <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Acción & Módulo</span>
                                    <span class="font-bold text-slate-800" x-text="selectedLog.accion + ' (' + selectedLog.modulo + ')'"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">IP de Origen</span>
                                    <span class="font-mono text-slate-800" x-text="selectedLog.ip_address"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Entidad Afectada</span>
                                    <span class="text-slate-800" x-text="(selectedLog.entidad_tipo ? selectedLog.entidad_tipo : 'N/A') + ' #' + (selectedLog.entidad_id || '')"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Fecha y Hora</span>
                                    <span class="text-slate-800" x-text="new Date(selectedLog.created_at).toLocaleString()"></span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Resultado</span>
                                    <span class="font-bold text-slate-800" x-text="selectedLog.resultado || 'EXITO'"></span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block">Descripción del Evento</span>
                                    <span class="text-slate-800 font-medium" x-text="selectedLog.descripcion || 'Sin descripción adicional'"></span>
                                </div>
                            </div>

                            <div>
                                <span class="text-[10px] uppercase font-bold text-slate-400 block mb-1">Carga de Datos (Detalles JSON):</span>
                                <pre
                                    class="w-full max-h-60 overflow-y-auto rounded-2xl bg-slate-900 text-emerald-400 p-4 font-mono text-[11px] leading-relaxed shadow-inner"
                                    x-text="JSON.stringify(selectedLog.detalles, null, 2)"
                                ></pre>
                            </div>

                            <template x-if="selectedLog.user_agent">
                                <div>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Navegador / User Agent:</span>
                                    <p class="text-[11px] text-slate-500 font-mono truncate" x-text="selectedLog.user_agent"></p>
                                </div>
                            </template>

                            <div class="flex items-center justify-end pt-4 border-t border-slate-100">
                                <button
                                    type="button"
                                    @click="inspectModalOpen = false"
                                    class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors"
                                >
                                    Cerrar
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>
</x-layout>
