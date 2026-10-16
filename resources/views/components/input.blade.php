@props([
    'invalid' => false,
])

<input
    {{ $attributes->merge([
        'class' => 'block w-full rounded-lg border-0 px-3.5 py-2 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus-ring bg-white '.($invalid ? 'ring-rose-500 focus:ring-rose-500' : 'ring-slate-200 focus:ring-primary-500'),
    ]) }}
/>
