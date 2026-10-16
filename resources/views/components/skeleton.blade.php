@props([
    'lines' => 3,
    'type' => 'text',
    'height' => 'h-4',
])

<div {{ $attributes->merge(['class' => 'space-y-2.5 animate-pulse']) }} role="status" aria-label="Cargando contenido...">
    @if ($type === 'card')
        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 p-5 space-y-4">
            <div class="flex items-center justify-between">
                <div class="h-4 w-28 bg-slate-200 rounded-md"></div>
                <div class="size-9 bg-slate-200 rounded-xl"></div>
            </div>
            <div class="h-8 w-20 bg-slate-200 rounded-lg"></div>
            <div class="h-3 w-36 bg-slate-200 rounded-md"></div>
        </div>
    @elseif ($type === 'table-row')
        @for ($i = 0; $i < $lines; $i++)
            <div class="flex items-center gap-4 py-3 border-b border-slate-100">
                <div class="size-9 shrink-0 bg-slate-200 rounded-xl"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 bg-slate-200 rounded-md w-1/3"></div>
                    <div class="h-3 bg-slate-100 rounded-md w-1/2"></div>
                </div>
                <div class="h-6 w-16 bg-slate-100 rounded-full"></div>
            </div>
        @endfor
    @else
        @for ($i = 0; $i < $lines; $i++)
            @php
                $widthClass = match($i % 3) {
                    0 => 'w-full',
                    1 => 'w-4/5',
                    2 => 'w-3/5',
                };
            @endphp
            <div class="{{ $height }} {{ $widthClass }} bg-slate-200 dark:bg-slate-700 rounded-lg"></div>
        @endfor
    @endif
    <span class="sr-only">Cargando...</span>
</div>
