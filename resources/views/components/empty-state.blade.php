@props([
    'icon' => 'inbox',
    'title' => 'Sin resultados',
    'description' => null,
    'action' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-6 py-12 text-center']) }}>
    <div class="flex size-12 items-center justify-center rounded-full bg-white text-slate-400 shadow-sm ring-1 ring-slate-100">
        <i data-lucide="{{ $icon }}" class="size-6"></i>
    </div>
    <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $title }}</h3>
    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-slate-500">{{ $description }}</p>
    @endif
    @if ($action)
        <div class="mt-4">{{ $action }}</div>
    @endif
</div>
