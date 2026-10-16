@props([
    'tone' => 'neutral',
    'dot' => false,
])

@php
    $tones = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'warning' => 'bg-amber-50 text-amber-700 ring-amber-600/25',
        'danger' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
        'primary' => 'bg-primary-50 text-primary-700 ring-primary-600/20',
        'info' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-600/20',
        'neutral' => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-600/20',
        'indigo' => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20',
    ];

    $dotColors = [
        'success' => 'bg-emerald-500',
        'warning' => 'bg-amber-500',
        'danger' => 'bg-rose-500',
        'primary' => 'bg-primary-500',
        'info' => 'bg-sky-500',
        'blue' => 'bg-blue-500',
        'neutral' => 'bg-slate-400',
        'violet' => 'bg-violet-500',
        'indigo' => 'bg-indigo-500',
    ];

    $resolvedTone = $tones[$tone] ?? ($tones['neutral']);
    $resolvedDot = $dotColors[$tone] ?? ($dotColors['neutral']);
@endphp

<span
    {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset '.$resolvedTone]) }}
>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $resolvedDot }}"></span>
    @endif
    {{ $slot }}
</span>
