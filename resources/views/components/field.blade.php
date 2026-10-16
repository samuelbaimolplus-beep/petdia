@props([
    'label' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'wrapperClass' => null,
])

@php
    $hasError = filled($error);
@endphp

<div {{ $attributes->only(['class'])->merge(['class' => 'space-y-1.5 '.($wrapperClass ?? '')]) }}>
    @if ($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    {{ $slot }}

    @if ($hint && ! $hasError)
        <p class="text-xs text-slate-400">{{ $hint }}</p>
    @endif

    @if ($hasError)
        <p class="text-xs font-medium text-rose-600 flex items-center gap-1.5 mt-1">
            <svg class="size-3.5 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <span>{{ $error }}</span>
        </p>
    @endif
</div>
