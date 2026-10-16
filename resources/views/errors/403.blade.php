@extends('errors.layout')

@section('title', 'Acceso Denegado')
@section('code', '403')

@section('glow-class', 'bg-amber-500')
@section('badge-class', 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400')
@section('ring-class', 'ring-amber-50 dark:ring-amber-950/30')
@section('badge-pill-class', 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-300')

@section('icon')
    <i data-lucide="shield-alert" class="size-10"></i>
@endsection

@section('headline', 'Acceso Restringido')

@section('message')
    Tu rol actual no tiene autorización para ingresar a este módulo. Si consideras que deberías tener acceso, consulta con el Administrador de la clínica.
@endsection

@section('actions')
    <a
        href="{{ route('dashboard') }}"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors focus-ring"
    >
        <i data-lucide="layout-dashboard" class="size-4"></i>
        <span>Volver al Dashboard</span>
    </a>
    <button
        type="button"
        onclick="window.history.back()"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors focus-ring"
    >
        <i data-lucide="arrow-left" class="size-4"></i>
        <span>Regresar</span>
    </button>
@endsection
