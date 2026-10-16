@props([
    'invalid' => false,
])

<select
    {{ $attributes->merge([
        'class' => 'block w-full appearance-none rounded-lg border-0 bg-white px-3.5 py-2 pr-9 text-sm text-slate-900 shadow-sm ring-1 ring-inset ring-slate-200 focus-ring bg-[url("data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2020%2020%22%3E%3Cpath%20stroke%3D%22%2364748b%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%221.5%22%20d%3D%22m6%208%204%204%204-4%22%2F%3E%3C%2Fsvg%3E")] bg-[length:1.25rem] bg-[right_0.5rem_center] bg-no-repeat '.($invalid ? 'ring-rose-300 focus:ring-rose-400' : 'ring-slate-200 focus:ring-primary-500'),
    ]) }}
>
    {{ $slot }}
</select>