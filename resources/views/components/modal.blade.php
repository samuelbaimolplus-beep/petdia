@props([
    'title' => null,
    'maxWidth' => 'max-w-lg',
    'id' => null,
])

<div
    x-data="{ open: false }"
    x-init="window.{{ $id ?? 'modal' }} = $data"
    @keydown.escape.window="open = false"
    x-cloak
>
    {{ $trigger }}

    <div x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="open = false"></div>

            <div
                class="relative w-full {{ $maxWidth }} rounded-2xl bg-white shadow-pop"
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                @if ($title)
                    <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-4">
                        <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                        <button type="button" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="open = false">
                            <i data-lucide="x" class="size-4"></i>
                        </button>
                    </div>
                @endif

                <div class="px-6 py-5">{{ $slot }}</div>

                @isset($footer)
                    <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4">{{ $footer }}</div>
                @endisset
            </div>
        </div>
    </div>
</div>
