@extends('errors.layout')

@section('title', 'Error Interno del Servidor')
@section('code', '500')

@section('glow-class', 'bg-rose-500')
@section('badge-class', 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400')
@section('ring-class', 'ring-rose-50 dark:ring-rose-950/30')
@section('badge-pill-class', 'bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-300')

@section('icon')
    <i data-lucide="alert-triangle" class="size-10"></i>
@endsection

@section('headline', 'Error Inesperado')

@section('message')
    Ocurrió una eventualidad imprevista en el servidor. El incidente ha sido registrado en la bitácora clínica para ser corregido.
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
        onclick="window.location.reload()"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-5 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-200 shadow-xs hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors focus-ring"
    >
        <i data-lucide="refresh-cw" class="size-4"></i>
        <span>Reintentar</span>
    </button>
@endsection
