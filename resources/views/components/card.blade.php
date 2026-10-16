@props([
    'padding' => true,
])

<div
    {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200/80 bg-white shadow-card '.($padding ? 'p-5' : '')]) }}
>
    {{ $slot }}
</div>
