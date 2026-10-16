@extends('errors.layout')

@section('title', 'Página no encontrada')
@section('code', '404')

@section('glow-class', 'bg-sky-500')
@section('badge-class', 'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-400')
@section('ring-class', 'ring-sky-50 dark:ring-sky-950/30')
@section('badge-pill-class', 'bg-sky-100 text-sky-800 dark:bg-sky-950/50 dark:text-sky-300')

@section('icon')
    <i data-lucide="search-x" class="size-10"></i>
@endsection

@section('headline', 'Página o Registro no encontrado')

@section('message')
    El paciente, cita, historial clínico o enlace que estás buscando no existe, ha sido trasladado o no se encuentra disponible.
@endsection

@section('actions')
    <a
        href="{{ route('dashboard') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors focus-ring"
    >
        <i data-lucide="layout-dashboard" class="size-4"></i>
        <span>Volver al Dashboard</span>
    </a>
    @if (Route::has('citas.index'))
    <a
        href="{{ route('citas.index') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors focus-ring"
    >
        <i data-lucide="calendar" class="size-4"></i>
        <span>Ver Citas</span>
    </a>
    @else
    <a
        href="{{ route('clientes.index') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors focus-ring"
    >
        <i data-lucide="users" class="size-4"></i>
        <span>Ver Clientes y Mascotas</span>
    </a>
    @endif
@endsection
