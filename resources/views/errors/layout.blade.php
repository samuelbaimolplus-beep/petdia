<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50 dark:bg-slate-950">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') — {{ \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Vite Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dark mode detection inline -->
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>
<body class="h-full bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 antialiased font-sans flex flex-col justify-between min-h-screen p-4 sm:p-6 lg:p-8">

    @php
        $clinicaNombre = \App\Models\Configuracion::get('clinica_nombre', 'La Petdiatría');
        $clinicaLogo = \App\Models\Configuracion::get('clinica_logo');
    @endphp

    <!-- Header sutil con identidad de marca -->
    <header class="w-full max-w-5xl mx-auto flex items-center justify-between py-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            @if($clinicaLogo && file_exists(public_path('storage/' . $clinicaLogo)))
                <img src="{{ asset('storage/' . $clinicaLogo) }}" alt="{{ $clinicaNombre }}" class="size-9 rounded-xl object-contain ring-1 ring-slate-200 dark:ring-slate-700 p-0.5 bg-white shrink-0">
            @else
                <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-600 text-white shadow-sm ring-1 ring-primary-500/30 group-hover:scale-105 transition-transform">
                    <i data-lucide="paw-print" class="size-5"></i>
                </div>
            @endif
            <div class="min-w-0">
                <span class="block text-sm font-bold tracking-tight text-slate-900 dark:text-slate-100 leading-tight">{{ $clinicaNombre }}</span>
                <span class="block text-[11px] font-medium text-slate-400 dark:text-slate-500 leading-none">Sistema de Gestión Clínica</span>
            </div>
        </a>

        <!-- Botón volver al inicio rápido -->
        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200 transition-colors">
            <i data-lucide="arrow-left" class="size-3.5"></i>
            <span>Ir al Dashboard</span>
        </a>
    </header>

    <!-- Contenedor central del error -->
    <main class="w-full max-w-lg mx-auto my-auto py-10">
        <div class="rounded-3xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-8 sm:p-10 shadow-pop text-center relative overflow-hidden">
            <!-- Glow decorativo superior -->
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 size-48 rounded-full blur-3xl opacity-20 pointer-events-none @yield('glow-class', 'bg-primary-500')"></div>

            <!-- Icono / Badge -->
            <div class="mx-auto flex size-20 items-center justify-center rounded-3xl @yield('badge-class', 'bg-primary-50 text-primary-600 dark:bg-primary-950/60 dark:text-primary-400') ring-8 @yield('ring-class', 'ring-primary-50 dark:ring-primary-950/30') mb-6 shadow-sm">
                @yield('icon')
            </div>

            <!-- Código de estado -->
            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase mb-3 @yield('badge-pill-class', 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300')">
                Código @yield('code')
            </span>

            <!-- Título y Descripción -->
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-3xl">
                @yield('headline')
            </h1>
            <p class="mt-3 text-sm text-slate-500 dark:text-slate-400 leading-relaxed max-w-sm mx-auto">
                @yield('message')
            </p>

            <!-- Acciones -->
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-3">
                @yield('actions')
            </div>
        </div>
    </main>

    <!-- Footer sutil -->
    <footer class="w-full max-w-5xl mx-auto py-4 text-center text-xs text-slate-400 dark:text-slate-600">
        <p>© {{ date('Y') }} {{ $clinicaNombre }} · Módulo Clínico y Hospitalario</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.renderIcons) window.renderIcons();
            else if (window.lucide) lucide.createIcons();
        });
    </script>
</body>
</html>
