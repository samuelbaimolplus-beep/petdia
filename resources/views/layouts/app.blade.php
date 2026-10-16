<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — ' . \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría') : \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría') . ' — Sistema Veterinario' }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts & CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Chart.js v4 -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dark mode detection script inline to avoid FOUC (Mejora 6.2) -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased font-sans transition-colors duration-150" x-data="{ mobileNavOpen: false, searchOpen: false, searchInput: '', searchResults: { clientes: [], mascotas: [], citas: [] }, searchLoading: false, darkMode: document.documentElement.classList.contains('dark') }" @keydown.slash.window="if ($event.target.tagName !== 'INPUT' && $event.target.tagName !== 'TEXTAREA') { $event.preventDefault(); searchOpen = true; $nextTick(() => $refs.globalSearchInput?.focus()); }" @keydown.cmd.k.window="$event.preventDefault(); searchOpen = true; $nextTick(() => $refs.globalSearchInput?.focus());" @keydown.ctrl.k.window="$event.preventDefault(); searchOpen = true; $nextTick(() => $refs.globalSearchInput?.focus());">

    <x-toast />
    <x-confirm-modal />

    @php
        /** @var \App\Models\Usuario|null $currentUser */
        $currentUser = Auth::user();
        $currentRole = $currentUser ? $currentUser->rol_principal : 'ADMIN';
        $currentRoute = request()->route() ? request()->route()->getName() : '';
        $clinicaNombre = \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría');
        $clinicaLogo = \App\Models\Configuracion::get('clinica_logo');

        $normalizeRole = fn ($str) => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', '-', ' '], ['a', 'e', 'i', 'o', 'u', '_', '_'], (string) $str));
        $realRoles = $currentUser ? $currentUser->roles->pluck('nombre')->map($normalizeRole)->all() : [];
        $isRealSuperAdmin = in_array('SUPER_ADMIN', $realRoles, true);
        $isRealAdmin = in_array('ADMIN', $realRoles, true) || in_array('ADMINISTRADOR', $realRoles, true);
        $canSimulateRoles = $isRealSuperAdmin || $isRealAdmin;
    @endphp

    <!-- Barra de progreso superior estilo Apple al cambiar de módulo -->
    <div id="apple-top-loader" class="apple-top-loader"></div>

    <!-- Píldora Dynamic Island con cristal líquido que indica la carga del módulo -->
    <div id="apple-dynamic-island-loader" class="apple-dynamic-island-loader">
        <div class="apple-spinner"></div>
        <span id="apple-island-text">Cargando módulo...</span>
    </div>

    <div class="min-h-full flex flex-col lg:flex-row">

        <!-- Mobile Drawer Overlay -->
        <div
            x-show="mobileNavOpen"
            x-cloak
            class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden"
            @click="mobileNavOpen = false"
            x-transition.opacity
        ></div>

        <!-- Barra lateral izquierda (Sidebar): translúcida estilo Apple con orbes de luz ambiental -->
        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[280px] sm:w-[260px] max-w-[85vw] sm:max-w-none flex-col border-r border-slate-200/70 dark:border-slate-800 bg-slate-50/95 dark:bg-slate-900/95 backdrop-blur-2xl transition-transform duration-300 ease-in-out lg:sticky lg:top-0 lg:h-screen lg:shrink-0 lg:translate-x-0 lg:z-auto overflow-hidden"
            :class="mobileNavOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            aria-label="Barra lateral"
        >
            <!-- Reflejos de luz ambiental neutra para dar refracción al cristal líquido puro -->
            <div class="pointer-events-none absolute -top-12 -left-12 size-48 rounded-full bg-slate-200/50 dark:bg-slate-700/20 blur-3xl"></div>
            <div class="pointer-events-none absolute top-1/2 -right-12 size-48 rounded-full bg-slate-200/40 dark:bg-slate-800/25 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-12 left-6 size-44 rounded-full bg-slate-100/50 dark:bg-slate-700/20 blur-3xl"></div>

            <!-- Logo de la clínica -->
            <div class="flex h-16 shrink-0 items-center justify-between border-b border-slate-200/60 dark:border-slate-800/80 px-5 backdrop-blur-md relative z-10">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                    @if($clinicaLogo && file_exists(public_path('storage/' . $clinicaLogo)))
                        <img src="{{ asset('storage/' . $clinicaLogo) }}" alt="{{ $clinicaNombre }}" class="size-9 rounded-xl object-contain ring-1 ring-slate-200 dark:ring-slate-700 p-0.5 bg-white shrink-0">
                    @else
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white shadow-sm ring-1 ring-primary-500/30">
                            <i data-lucide="paw-print" class="size-5"></i>
                        </div>
                    @endif
                    <div class="min-w-0 truncate">
                        <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-slate-100 leading-tight truncate">{{ $clinicaNombre }}</span>
                        <span class="block text-[11px] font-medium text-slate-400 dark:text-slate-500 leading-none">Veterinary PMS</span>
                    </div>
                </a>

                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-600 dark:hover:text-slate-300 lg:hidden"
                    @click="mobileNavOpen = false"
                >
                    <i data-lucide="x" class="size-5"></i>
                </button>
            </div>

            <!-- Navegación por secciones -->
            <div class="flex-1 overflow-y-auto px-3.5 py-4 space-y-6 relative z-10">

                <!-- SECCIÓN: RESUMEN -->
                <div>
                    <p class="px-2.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Resumen</p>
                    <nav class="mt-2 liquid-glass-card p-1.5 space-y-1">
                        <a
                            href="{{ route('dashboard') }}"
                            class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('dashboard*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                        >
                            <i data-lucide="layout-dashboard" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('dashboard*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                            <span>Dashboard</span>
                        </a>
                    </nav>
                </div>

                <!-- SECCIÓN: GESTIÓN BASE (Clientes & Mascotas) -->
                @if ($currentUser && $currentUser->canAccessModule('clientes'))
                    <div>
                        <p class="px-2.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Gestión Base</p>
                        <nav class="mt-2 liquid-glass-card p-1.5 space-y-1">
                            <a
                                href="{{ route('clientes.index') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ (request()->routeIs('clientes*') || request()->routeIs('mascotas*')) && !request()->routeIs('catalogo*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="users" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ (request()->routeIs('clientes*') || request()->routeIs('mascotas*')) && !request()->routeIs('catalogo*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Clientes y Mascotas</span>
                            </a>

                            @if ($currentUser->hasRole(['SUPER_ADMIN', 'SUPER-ADMIN', 'ADMIN', 'ADMINISTRADOR']))
                                <a
                                    href="{{ route('catalogo.especies-razas') }}"
                                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('catalogo*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                                >
                                    <i data-lucide="dna" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('catalogo*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                    <span>Especies y Razas</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

                <!-- SECCIÓN: AGENDA & CITAS (CU13, CU14, CU29, CU30) -->
                @if ($currentUser && $currentUser->canAccessModule('citas'))
                    <div>
                        <p class="px-2.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Agenda & Citas</p>
                        <nav class="mt-2 liquid-glass-card p-1.5 space-y-1">
                            <a
                                href="{{ route('citas.index') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('citas*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="calendar" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('citas*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Agenda y Citas</span>
                            </a>
                        </nav>
                    </div>
                @endif

                <!-- SECCIÓN: ÁREA MÉDICA -->
                @if ($currentUser && ($currentUser->canAccessModule('atenciones') || $currentUser->canAccessModule('cirugias') || $currentUser->canAccessModule('historial_clinico') || $currentUser->canAccessModule('profesionales')))
                    <div>
                        <p class="px-2.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Área Médica</p>
                        <nav class="mt-2 liquid-glass-card p-1.5 space-y-1">
                            @if ($currentUser->canAccessModule('atenciones'))
                                <a
                                    href="{{ route('atenciones.index') }}"
                                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('atenciones*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                                >
                                    <i data-lucide="stethoscope" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('atenciones*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                    <span>Atenciones Médicas</span>
                                </a>
                            @endif

                            @if ($currentUser->canAccessModule('cirugias'))
                                <a
                                    href="{{ route('cirugias.index') }}"
                                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('cirugias*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                                >
                                    <i data-lucide="scissors" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('cirugias*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                    <span>Quirófano & Cirugías</span>
                                </a>
                            @endif

                            @if ($currentUser->canAccessModule('historial_clinico'))
                                <a
                                    href="{{ route('historial.index') }}"
                                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('historial*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                                >
                                    <i data-lucide="clipboard-list" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('historial*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                    <span>Historial Clínico</span>
                                </a>
                            @endif

                            @if ($currentUser->canAccessModule('profesionales'))
                                <a
                                    href="{{ route('profesionales.index') }}"
                                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('profesionales*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                                >
                                    <i data-lucide="user-check" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('profesionales*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                    <span>Profesionales</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

                <!-- SECCIÓN: REPORTES & ANALÍTICA -->
                @if ($currentUser && $currentUser->canAccessModule('reportes'))
                    <div>
                        <p class="px-2.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Reportes & Analítica</p>
                        <nav class="mt-2 liquid-glass-card p-1.5 space-y-1">
                            <a
                                href="{{ route('reportes.index') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('reportes.index') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="bar-chart-2" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('reportes.index') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Panel Analítico</span>
                            </a>
                            <a
                                href="{{ route('reportes.vacunaciones') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('reportes.vacunaciones*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="shield-check" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('reportes.vacunaciones*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Vacunaciones</span>
                            </a>
                            <a
                                href="{{ route('reportes.pacientes-atendidos') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('reportes.pacientes-atendidos*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="activity" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('reportes.pacientes-atendidos*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Pacientes Atendidos</span>
                            </a>
                        </nav>
                    </div>
                @endif

                <!-- SECCIÓN: ADMINISTRACIÓN & SEGURIDAD -->
                @if ($currentUser && ($currentUser->canAccessModule('usuarios') || $currentUser->canAccessModule('accesos')))
                    <div>
                        <p class="px-2.5 text-[11px] font-bold tracking-wider uppercase text-slate-400 dark:text-slate-500">Administración & Seguridad</p>
                        <nav class="mt-2 liquid-glass-card p-1.5 space-y-1">
                            <a
                                href="{{ route('usuarios.index') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('usuarios.index') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="shield-alert" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('usuarios.index') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Usuarios y Roles</span>
                            </a>

                            <a
                                href="{{ route('usuarios.accesos') }}"
                                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('usuarios.accesos') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                            >
                                <i data-lucide="clock" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('usuarios.accesos') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                <span>Registro de Accesos</span>
                            </a>

                            @if ($currentUser->hasRole(['SUPER_ADMIN', 'SUPER-ADMIN']))
                                <a
                                    href="{{ route('usuarios.auditoria') }}"
                                    class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium apple-press module-btn-apple {{ request()->routeIs('usuarios.auditoria*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300' }}"
                                >
                                    <i data-lucide="file-search" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110 {{ request()->routeIs('usuarios.auditoria*') ? 'text-slate-900 dark:text-white' : 'text-slate-500 dark:text-slate-400 group-hover:text-slate-800 dark:group-hover:text-slate-200' }}"></i>
                                    <span>Bitácora Auditoría</span>
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

            </div>

            @if (Route::has('demo.switch-role') && $canSimulateRoles)
            <!-- Selector de Rol Demo en el pie de la barra lateral (Control Jerárquico) -->
            <div class="border-t border-slate-100 dark:border-slate-800 p-3 bg-slate-50/50 dark:bg-slate-900/50 relative z-10 backdrop-blur-md">
                <div class="rounded-xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-800/70 p-2.5 shadow-xs">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-400 dark:text-slate-400 font-medium">Rol activo:</span>
                        <x-badge tone="primary">{{ $currentRole }}</x-badge>
                    </div>
                    <form action="{{ route('demo.switch-role') }}" method="POST" class="mt-2">
                        @csrf
                        <select
                            name="role"
                            onchange="this.form.submit()"
                            class="block w-full rounded-lg border-0 bg-slate-50 dark:bg-slate-900 px-2 py-1.5 text-xs font-semibold text-slate-700 dark:text-slate-200 ring-1 ring-inset ring-slate-200 dark:ring-slate-700 focus-ring cursor-pointer"
                        >
                            @if ($isRealSuperAdmin)
                                <option value="SUPER_ADMIN" {{ $currentRole === 'SUPER_ADMIN' ? 'selected' : '' }}>Simular Super-Admin</option>
                            @endif
                            <option value="ADMIN" {{ $currentRole === 'ADMIN' ? 'selected' : '' }}>Simular Administrador</option>
                            <option value="RECEPCION" {{ $currentRole === 'RECEPCION' ? 'selected' : '' }}>Simular Recepción</option>
                            <option value="VETERINARIO" {{ $currentRole === 'VETERINARIO' ? 'selected' : '' }}>Simular Veterinario</option>
                            <option value="SPA" {{ ($currentRole === 'SPA' || $currentRole === 'ESTETICO') ? 'selected' : '' }}>Simular SPA / Estética</option>
                            <option value="TRANSPORTE" {{ $currentRole === 'TRANSPORTE' ? 'selected' : '' }}>Simular Transporte</option>
                        </select>
                    </form>
                </div>
            </div>
            @endif
        </aside>

        <!-- Área principal de trabajo -->
        <div class="flex flex-1 flex-col min-w-0 overflow-x-hidden">

            <!-- Barra superior (Header / Topbar) -->
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between border-b border-slate-200/80 dark:border-slate-800 bg-white/95 dark:bg-slate-900/95 px-3 sm:px-6 backdrop-blur-md">
                
                <!-- Izquierda: Botón Móvil -->
                <div class="flex items-center gap-2.5 lg:hidden min-w-0">
                    <button
                        type="button"
                        class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-700 dark:hover:text-slate-300 focus-ring shrink-0"
                        @click="mobileNavOpen = true"
                    >
                        <i data-lucide="menu" class="size-5"></i>
                    </button>
                    <span class="text-sm font-bold text-slate-900 dark:text-slate-100 truncate max-w-[130px] sm:max-w-none">{{ $clinicaNombre }}</span>
                </div>

                <!-- Centro: Barra de búsqueda centrada con atajos kbd -->
                <div class="hidden sm:flex flex-1 justify-center max-w-xl mx-auto px-4">
                    <button
                        type="button"
                        @click="searchOpen = true; $nextTick(() => $refs.globalSearchInput?.focus())"
                        class="group flex w-full max-w-md items-center justify-between rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/70 dark:bg-slate-800/60 px-3.5 py-1.5 text-sm text-slate-400 dark:text-slate-400 shadow-xs hover:border-slate-300 dark:hover:border-slate-600 hover:bg-white dark:hover:bg-slate-800 transition-all cursor-pointer"
                    >
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="search" class="size-4 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-colors"></i>
                            <span class="text-slate-400 font-normal">Buscar clientes o mascotas...</span>
                        </div>
                        <div class="flex items-center gap-1 text-[11px] font-semibold text-slate-400">
                            <kbd class="rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-1.5 py-0.5 shadow-2xs text-slate-500 dark:text-slate-400">/</kbd>
                            <kbd class="rounded border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-1.5 py-0.5 shadow-2xs text-slate-500 dark:text-slate-400">⌘K</kbd>
                        </div>
                    </button>
                </div>

                <!-- Derecha: Notificaciones, Avatar y Menú de Usuario -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    
                    <!-- Buscador móvil -->
                    <button
                        type="button"
                        @click="searchOpen = true; $nextTick(() => $refs.globalSearchInput?.focus())"
                        class="sm:hidden rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800"
                    >
                        <i data-lucide="search" class="size-5"></i>
                    </button>

                    <!-- Campana de notificaciones con badge dinámico y dropdown real -->
                    <div
                        class="relative"
                        x-data="{
                            open: false,
                            notificaciones: [],
                            total: 0,
                            loading: false,
                            async fetchNotificaciones() {
                                this.loading = true;
                                try {
                                    const res = await fetch('{{ route('notificaciones.index') }}');
                                    if (res.ok) {
                                        const data = await res.json();
                                        this.notificaciones = data.notificaciones || [];
                                        this.total = data.total || 0;
                                    }
                                } catch (e) {
                                    console.error('Error al cargar notificaciones:', e);
                                } finally {
                                    this.loading = false;
                                    this.$nextTick(() => { if (window.lucide) lucide.createIcons(); });
                                }
                            },
                            async clickNotificacion(item) {
                                try {
                                    await fetch(`/notificaciones/${item.id}/leer`, {
                                        method: 'PATCH',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });
                                    item.leida = true;
                                    if (this.total > 0) this.total--;
                                } catch (e) {
                                    console.error(e);
                                }
                                window.location.href = item.url;
                            },
                            async marcarTodas() {
                                try {
                                    const res = await fetch('{{ route('notificaciones.leer-todas') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                        }
                                    });
                                    if (res.ok) {
                                        this.notificaciones.forEach(n => n.leida = true);
                                        this.total = 0;
                                    }
                                } catch (e) {
                                    console.error(e);
                                }
                            }
                        }"
                        x-init="fetchNotificaciones()"
                    >
                        <button
                            type="button"
                            @click="open = !open; if(open) fetchNotificaciones()"
                            class="relative flex size-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition-colors focus-ring"
                            aria-label="Notificaciones"
                        >
                            <svg class="size-5 text-slate-600 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <template x-if="total > 0">
                                <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-rose-600 text-white text-[10px] font-bold shadow-xs">
                                    <span x-text="total > 9 ? '9+' : total"></span>
                                </span>
                            </template>
                        </button>

                        <!-- Menú desplegable de notificaciones -->
                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-cloak
                            class="absolute right-0 mt-2 w-[calc(100vw-2rem)] sm:w-88 max-w-sm rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-3 shadow-pop z-50"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                        >
                            <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800 px-1">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Alertas del Sistema</p>
                                <span
                                    class="rounded-full px-2 py-0.5 text-[11px] font-semibold transition-colors"
                                    :class="total > 0 ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'"
                                    x-text="total + ' pendientes'"
                                ></span>
                            </div>

                            <!-- Estado de carga con Skeleton (Mejora 6.3) -->
                            <div x-show="loading && notificaciones.length === 0" class="py-3 px-2">
                                <x-skeleton type="table-row" :lines="3" />
                            </div>

                            <!-- Sin notificaciones -->
                            <div x-show="!loading && notificaciones.length === 0" class="py-6 text-center text-xs text-slate-400 dark:text-slate-500">
                                <i data-lucide="check-circle-2" class="size-6 mx-auto mb-1 text-emerald-500"></i>
                                <p>Todo al día. Sin alertas pendientes.</p>
                            </div>

                            <!-- Lista de notificaciones -->
                            <div x-show="notificaciones.length > 0" class="mt-2 max-h-80 overflow-y-auto space-y-1 text-xs divide-y divide-slate-50 dark:divide-slate-800/40 pr-0.5">
                                <template x-for="item in notificaciones" :key="item.id">
                                    <div
                                        @click="clickNotificacion(item)"
                                        class="flex items-start gap-2.5 p-2 rounded-xl transition-all cursor-pointer group"
                                        :class="item.leida ? 'opacity-55 hover:opacity-85 hover:bg-slate-50 dark:hover:bg-slate-800/40' : 'bg-slate-50/60 dark:bg-slate-800/40 hover:bg-slate-100/80 dark:hover:bg-slate-800/70'"
                                    >
                                        <div
                                            class="flex size-7 shrink-0 items-center justify-center rounded-lg mt-0.5"
                                            :class="{
                                                'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300': item.color === 'amber',
                                                'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300': item.color === 'rose',
                                                'bg-primary-100 text-primary-700 dark:bg-primary-950/50 dark:text-primary-300': item.color === 'primary',
                                                'bg-violet-100 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300': item.color === 'violet'
                                            }"
                                        >
                                            <template x-if="item.icono === 'package'"><i data-lucide="package" class="size-3.5"></i></template>
                                            <template x-if="item.icono === 'alert-triangle'"><i data-lucide="alert-triangle" class="size-3.5"></i></template>
                                            <template x-if="item.icono === 'calendar'"><i data-lucide="calendar" class="size-3.5"></i></template>
                                            <template x-if="item.icono === 'bell-ring'"><i data-lucide="bell-ring" class="size-3.5"></i></template>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <p class="font-semibold text-slate-800 dark:text-slate-200 truncate" x-text="item.titulo"></p>
                                                <span x-show="!item.leida" class="size-1.5 rounded-full bg-rose-500 shrink-0"></span>
                                            </div>
                                            <p class="text-slate-500 dark:text-slate-400 text-[11px] leading-tight mt-0.5 line-clamp-2" x-text="item.detalle"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Pie con marcar todas como leídas -->
                            <div x-show="notificaciones.length > 0" class="mt-2 pt-2 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center px-1 text-[11px]">
                                <button
                                    type="button"
                                    x-show="total > 0"
                                    @click="marcarTodas()"
                                    class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium hover:underline"
                                >
                                    Marcar todas como leídas
                                </button>
                                <span x-show="total === 0" class="text-slate-400 dark:text-slate-500">Todas revisadas</span>
                                <button
                                    type="button"
                                    @click="fetchNotificaciones()"
                                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 flex items-center gap-1"
                                    title="Actualizar"
                                >
                                    <i data-lucide="refresh-cw" class="size-3"></i>
                                    <span>Actualizar</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Toggle Modo Oscuro (Mejora 6.2) -->
                    <button
                        type="button"
                        @click="darkMode = !darkMode; if (darkMode) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); }"
                        class="flex size-9 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition-colors focus-ring"
                        aria-label="Alternar modo oscuro y claro"
                        :title="darkMode ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro'"
                    >
                        <!-- Sol para cuando está en modo oscuro (cambiar a claro) -->
                        <svg x-show="darkMode" x-cloak class="size-4.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Luna para cuando está en modo claro (cambiar a oscuro) -->
                        <svg x-show="!darkMode" class="size-4.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Separador vertical sutil -->
                    <div class="h-6 w-px bg-slate-200 dark:bg-slate-800"></div>

                    <!-- Usuario logueado con avatar y menú desplegable -->
                    <div class="relative" x-data="{ userMenuOpen: false }">
                        <button
                            type="button"
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-3 rounded-xl p-1 text-left hover:bg-slate-50 dark:hover:bg-slate-800/70 transition-colors focus-ring"
                        >
                            <!-- Avatar circular -->
                            <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-primary-700 font-bold text-sm ring-2 ring-primary-500/20">
                                {{ strtoupper(substr($currentUser?->nombre_completo ?? 'U', 0, 1)) }}
                            </div>

                            <div class="hidden md:block min-w-0 pr-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 leading-tight truncate">
                                    {{ $currentUser?->nombre_completo ?? 'Usuario' }}
                                </p>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-none">
                                    {{ $currentRole }}
                                </p>
                            </div>

                            <i data-lucide="chevron-down" class="hidden md:block size-4 text-slate-400"></i>
                        </button>

                        <!-- Menú desplegable de cuenta -->
                        <div
                            x-show="userMenuOpen"
                            @click.outside="userMenuOpen = false"
                            x-cloak
                            class="absolute right-0 mt-2 w-64 max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-2 shadow-pop z-50"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                        >
                            <div class="p-3 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Sesión iniciada como</p>
                                <p class="mt-1 text-sm font-bold text-slate-900 dark:text-slate-100">{{ $currentUser?->nombre_completo }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">@<span>{{ $currentUser?->nombre_usuario }}</span> · <span class="font-medium text-primary-600 dark:text-primary-400">{{ $currentRole }}</span></p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('perfil.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/70 transition-colors">
                                    <i data-lucide="user" class="size-4 text-slate-400"></i>
                                    <span>Mi Cuenta & Seguridad</span>
                                </a>
                                @if($currentUser?->canAccessModule('configuracion') && Route::has('configuracion.index'))
                                    <a href="{{ route('configuracion.index') }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800/70 transition-colors">
                                        <i data-lucide="settings" class="size-4 text-slate-400"></i>
                                        <span>Configuración</span>
                                    </a>
                                @endif
                            </div>

                            <div class="pt-1 border-t border-slate-100 dark:border-slate-800">
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors"
                                    >
                                        <i data-lucide="log-out" class="size-4 text-rose-500"></i>
                                        <span>Cerrar Sesión</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Contenido dinámico principal -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 pb-28 lg:pb-8 apple-page-enter">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>

        </div>
    </div>

    <!-- Dock Flotante de Cristal Líquido estilo Apple (Móviles / Tablets pequeñas) -->
    <nav aria-label="Navegación Rápida Móvil" class="fixed bottom-3 inset-x-3 sm:inset-x-6 max-w-lg mx-auto ios-glass-dock rounded-2xl p-1.5 z-30 lg:hidden flex items-center justify-around shadow-2xl">
        <a
            href="{{ route('dashboard') }}"
            class="flex flex-1 flex-col items-center gap-1 py-1 px-1 rounded-xl apple-press transition-all duration-200 {{ request()->routeIs('dashboard') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}"
        >
            <i data-lucide="layout-dashboard" class="size-4.5"></i>
            <span class="text-[10px] tracking-tight">Inicio</span>
        </a>

        @if ($currentUser && $currentUser->canAccessModule('citas'))
            <a
                href="{{ route('citas.index') }}"
                class="flex flex-1 flex-col items-center gap-1 py-1 px-1 rounded-xl apple-press transition-all duration-200 {{ request()->routeIs('citas*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}"
            >
                <i data-lucide="calendar" class="size-4.5"></i>
                <span class="text-[10px] tracking-tight">Citas</span>
            </a>
        @endif

        @if ($currentUser && $currentUser->canAccessModule('clientes'))
            <a
                href="{{ route('clientes.index') }}"
                class="flex flex-1 flex-col items-center gap-1 py-1 px-1 rounded-xl apple-press transition-all duration-200 {{ (request()->routeIs('clientes*') || request()->routeIs('mascotas*')) && !request()->routeIs('catalogo*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}"
            >
                <i data-lucide="users" class="size-4.5"></i>
                <span class="text-[10px] tracking-tight">Pacientes</span>
            </a>
        @endif

        @if ($currentUser && $currentUser->canAccessModule('atenciones'))
            <a
                href="{{ route('atenciones.index') }}"
                class="flex flex-1 flex-col items-center gap-1 py-1 px-1 rounded-xl apple-press transition-all duration-200 {{ request()->routeIs('atenciones*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}"
            >
                <i data-lucide="stethoscope" class="size-4.5"></i>
                <span class="text-[10px] tracking-tight">Atención</span>
            </a>
        @elseif ($currentUser && $currentUser->canAccessModule('caja'))
            <a
                href="{{ route('caja.index') }}"
                class="flex flex-1 flex-col items-center gap-1 py-1 px-1 rounded-xl apple-press transition-all duration-200 {{ request()->routeIs('caja*') ? 'liquid-glass-active font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}"
            >
                <i data-lucide="receipt" class="size-4.5"></i>
                <span class="text-[10px] tracking-tight">Caja</span>
            </a>
        @endif

        <button
            type="button"
            @click="mobileNavOpen = true"
            class="flex flex-1 flex-col items-center gap-1 py-1 px-1 rounded-xl apple-press text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-all duration-200"
            aria-label="Abrir panel de módulos completo"
        >
            <i data-lucide="grid" class="size-4.5"></i>
            <span class="text-[10px] tracking-tight">Módulos</span>
        </button>
    </nav>

    <!-- Modal de Búsqueda Global (⌘K / /) -->
    <div
        x-show="searchOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="searchOpen = false"
    >
        <div class="flex min-h-full items-start justify-center p-4 pt-16 sm:pt-24">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="searchOpen = false"></div>

            <div
                class="relative w-full max-w-xl rounded-2xl bg-white dark:bg-slate-900 shadow-pop border border-slate-200/80 dark:border-slate-800 overflow-hidden"
                x-show="searchOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            >
                <div class="flex items-center border-b border-slate-200/80 dark:border-slate-800 px-4 py-3">
                    <i data-lucide="search" class="size-5 text-slate-400 mr-3"></i>
                    <input
                        x-ref="globalSearchInput"
                        type="text"
                        x-model="searchInput"
                        @input.debounce.300ms="
                            if (searchInput.length >= 2) {
                                searchLoading = true;
                                fetch('/buscar?q=' + encodeURIComponent(searchInput), {
                                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                })
                                .then(r => r.json())
                                .then(data => { searchResults = data; searchLoading = false; })
                                .catch(() => { searchLoading = false; });
                            } else {
                                searchResults = { clientes: [], mascotas: [], citas: [] };
                            }
                        "
                        placeholder="Buscar paciente por nombre, microchip o propietario..."
                        class="w-full border-0 p-0 text-sm text-slate-900 dark:text-slate-100 placeholder:text-slate-400 dark:placeholder:text-slate-500 bg-transparent focus:outline-none focus:ring-0"
                    />
                    <button type="button" @click="searchOpen = false; searchInput = ''; searchResults = { clientes: [], mascotas: [], citas: [] };" class="text-xs font-semibold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 border border-slate-200 dark:border-slate-700 rounded px-1.5 py-0.5">
                        ESC
                    </button>
                </div>

                <div class="p-4 max-h-96 overflow-y-auto">
                    {{-- Estado inicial --}}
                    <div x-show="!searchInput || searchInput.length < 2" class="text-center py-6 text-slate-400 dark:text-slate-500">
                        <i data-lucide="search" class="size-8 mx-auto mb-2 text-slate-300 dark:text-slate-600"></i>
                        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Búsqueda rápida en todo el sistema</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Escribe al menos 2 caracteres para buscar.</p>
                    </div>

                    {{-- Estado de carga con Skeleton (Mejora 6.3) --}}
                    <div x-show="searchLoading && searchInput.length >= 2" class="py-2 px-1">
                        <x-skeleton type="table-row" :lines="3" />
                    </div>

                    {{-- Resultados --}}
                    <div x-show="searchInput.length >= 2 && !searchLoading" class="space-y-4">

                        {{-- Sin resultados --}}
                        <div x-show="searchResults.clientes?.length === 0 && searchResults.mascotas?.length === 0 && searchResults.citas?.length === 0" class="text-center py-6">
                            <i data-lucide="search-x" class="size-8 mx-auto mb-2 text-slate-300 dark:text-slate-600"></i>
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-300">Sin resultados para "<span x-text="searchInput" class="font-bold"></span>"</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Intenta con otro término de búsqueda.</p>
                        </div>

                        {{-- Clientes --}}
                        <template x-if="searchResults.clientes?.length > 0">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-2 mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="users" class="size-3.5"></i>
                                    <span>Clientes / Propietarios</span>
                                </p>
                                <div class="space-y-1">
                                    <template x-for="c in searchResults.clientes" :key="c.id">
                                        <a :href="c.url" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" @click="searchOpen = false">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-9 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400">
                                                    <i data-lucide="user" class="size-4"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100" x-text="c.nombre"></p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">CI: <span x-text="c.documento || 'S/D'"></span></p>
                                                </div>
                                            </div>
                                            <i data-lucide="chevron-right" class="size-4 text-slate-300 dark:text-slate-600"></i>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Mascotas --}}
                        <template x-if="searchResults.mascotas?.length > 0">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-2 mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="paw-print" class="size-3.5"></i>
                                    <span>Mascotas / Pacientes</span>
                                </p>
                                <div class="space-y-1">
                                    <template x-for="m in searchResults.mascotas" :key="m.id">
                                        <a :href="m.url" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" @click="searchOpen = false">
                                            <div class="flex items-center gap-3">
                                                <template x-if="m.foto">
                                                    <img :src="m.foto" class="size-9 rounded-xl object-cover ring-1 ring-primary-500/20" />
                                                </template>
                                                <template x-if="!m.foto">
                                                    <div class="flex size-9 items-center justify-center rounded-xl bg-primary-50 dark:bg-primary-950/40 text-primary-600 dark:text-primary-400">
                                                        <i data-lucide="paw-print" class="size-4"></i>
                                                    </div>
                                                </template>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                        <span x-text="m.nombre"></span> · <span class="text-slate-500 dark:text-slate-400 font-normal" x-text="m.raza"></span>
                                                    </p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400">Propietario: <span x-text="m.propietario"></span></p>
                                                </div>
                                            </div>
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold"
                                                  :class="m.estado === 'ACTIVO' ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'"
                                                  x-text="m.estado">
                                            </span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Citas --}}
                        <template x-if="searchResults.citas?.length > 0">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500 px-2 mb-1.5 flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="size-3.5"></i>
                                    <span>Citas</span>
                                </p>
                                <div class="space-y-1">
                                    <template x-for="ci in searchResults.citas" :key="ci.id">
                                        <a :href="ci.url" class="flex items-center justify-between p-2.5 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors" @click="searchOpen = false">
                                            <div class="flex items-center gap-3">
                                                <div class="flex size-9 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400">
                                                    <i data-lucide="calendar" class="size-4"></i>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                        Cita #<span x-text="ci.id"></span> — <span x-text="ci.paciente"></span>
                                                    </p>
                                                    <p class="text-xs text-slate-500 dark:text-slate-400"><span x-text="ci.fecha"></span> · Dr. <span x-text="ci.profesional"></span></p>
                                                </div>
                                            </div>
                                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400" x-text="ci.estado"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/60 px-4 py-2.5 flex items-center justify-between text-xs text-slate-400 dark:text-slate-500">
                    <div class="flex items-center gap-3">
                        <span><kbd class="font-semibold text-slate-600 dark:text-slate-400">↑↓</kbd> navegar</span>
                        <span><kbd class="font-semibold text-slate-600 dark:text-slate-400">↵</kbd> seleccionar</span>
                    </div>
                    <span>{{ $clinicaNombre }} Search</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Renderizado seguro de iconos Lucide tras cualquier evento -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.renderIcons) window.renderIcons();

            // Interacción fluida de navegación estilo Apple al cambiar de módulo
            const moduleLinks = document.querySelectorAll('.module-btn-apple, nav a[href]');
            const topLoader = document.getElementById('apple-top-loader');
            const islandLoader = document.getElementById('apple-dynamic-island-loader');
            const islandText = document.getElementById('apple-island-text');

            moduleLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    const href = link.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:') || link.target === '_blank') return;
                    if (e.metaKey || e.ctrlKey || e.shiftKey) return;

                    // Iniciar barra de carga superior Apple
                    if (topLoader) {
                        topLoader.style.opacity = '1';
                        topLoader.style.width = '35%';
                        setTimeout(() => { topLoader.style.width = '78%'; }, 180);
                    }

                    // Mostrar Dynamic Island con el nombre del módulo seleccionado
                    if (islandLoader) {
                        const label = link.querySelector('span')?.textContent?.trim() || 'Módulo';
                        if (islandText) islandText.textContent = `Cargando ${label}...`;
                        islandLoader.classList.add('active');
                    }
                });
            });

            // Al completarse la carga de la vista, ocultar fluidamente los indicadores
            window.addEventListener('pageshow', () => {
                if (topLoader) {
                    topLoader.style.width = '100%';
                    setTimeout(() => {
                        topLoader.style.opacity = '0';
                        setTimeout(() => { topLoader.style.width = '0%'; }, 300);
                    }, 220);
                }
                if (islandLoader) {
                    setTimeout(() => {
                        islandLoader.classList.remove('active');
                    }, 280);
                }
            });
        });
        window.addEventListener('alpine:initialized', () => {
            if (window.renderIcons) window.renderIcons();
        });
    </script>
</body>
</html>

