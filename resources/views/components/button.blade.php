@props([
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'type' => 'button',
])

@php
    $variants = [
        'primary' => 'bg-primary-600 text-white shadow-sm hover:bg-primary-700 focus-visible:outline-primary-600',
        'secondary' => 'bg-white text-slate-700 ring-1 ring-inset ring-slate-200 hover:bg-slate-50',
        'danger' => 'bg-rose-600 text-white shadow-sm hover:bg-rose-700',
        'ghost' => 'text-slate-600 hover:bg-slate-100 hover:text-slate-900',
        'success' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700',
        'glass' => 'liquid-glass text-slate-800 dark:text-slate-100 hover:bg-white/80 dark:hover:bg-slate-800/80',
        'liquid-glass' => 'liquid-glass-active text-slate-900 dark:text-white',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs rounded-lg gap-1.5',
        'md' => 'px-3.5 py-2 text-sm rounded-lg gap-2',
        'lg' => 'px-4 py-2.5 text-sm rounded-xl gap-2',
    ];
@endphp

<button
    {{ $attributes->merge(['type' => $type, 'class' => 'inline-flex items-center justify-center font-semibold apple-press focus-ring disabled:opacity-50 disabled:pointer-events-none '.$variants[$variant].' '.$sizes[$size]]) }}
>
    @if ($icon)
        <i data-lucide="{{ $icon }}" class="size-4"></i>
    @endif
    @if (trim($slot))
        <span>{{ $slot }}</span>
    @endif
</button>
