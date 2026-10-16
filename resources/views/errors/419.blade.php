@extends('errors.layout')

@section('title', 'Sesión Expirada')
@section('code', '419')

@section('glow-class', 'bg-indigo-500')
@section('badge-class', 'bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400')
@section('ring-class', 'ring-indigo-50 dark:ring-indigo-950/30')
@section('badge-pill-class', 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/50 dark:text-indigo-300')

@section('icon')
    <i data-lucide="timer-reset" class="size-10"></i>
@endsection

@section('headline', 'Sesión Expirada')

@section('message')
    Tu sesión o el formulario ha expirado por un período prolongado de inactividad para proteger los datos médicos de los pacientes. Recarga para continuar.
@endsection

@section('actions')
    <button
        type="button"
        onclick="window.location.reload()"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors focus-ring"
    >
        <i data-lucide="refresh-cw" class="size-4"></i>
        <span>Recargar Página</span>
    </button>
    <a
        href="{{ route('login') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors focus-ring"
    >
        <i data-lucide="log-in" class="size-4"></i>
        <span>Iniciar Sesión</span>
    </a>
@endsection
