@props([
    'label' => null,
    'value' => null,
    'description' => null,
    'icon' => null,
    'tone' => 'primary',
])

@php
    $iconTones = [
        'primary' => 'bg-primary-50 text-primary-600 dark:bg-primary-950/60 dark:text-primary-300',
        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-300',
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-300',
        'danger' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-300',
        'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-950/60 dark:text-violet-300',
        'info' => 'bg-sky-50 text-sky-600 dark:bg-sky-950/60 dark:text-sky-300',
        'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-300',
        'neutral' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300',
    ];
    $resolvedTone = $iconTones[$tone] ?? $iconTones['primary'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 dark:border-slate-800 bg-white dark:bg-slate-900 p-5 shadow-card transition-all']) }}>
    <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">{{ $label }}</p>
            <p class="mt-1.5 text-2xl sm:text-[2rem] font-bold leading-none tracking-tight text-slate-900 dark:text-slate-100">{{ $value ?? $slot }}</p>
            @if ($description)
                <p class="mt-2 text-xs sm:text-sm text-slate-500 dark:text-slate-400 line-clamp-2">{{ $description }}</p>
            @endif
        </div>
        @if ($icon)
            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $resolvedTone }}">
                <i data-lucide="{{ $icon }}" class="size-5"></i>
            </div>
        @endif
    </div>
</div>
