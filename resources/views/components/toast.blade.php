<div class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4" x-cloak>
    <template x-for="t in $store.toast.items" :key="t.id">
        <div
            class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-xl border bg-white p-4 shadow-pop"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div
                class="flex size-8 shrink-0 items-center justify-center rounded-lg"
                :class="{
                    'bg-emerald-50 text-emerald-600': t.type === 'success',
                    'bg-rose-50 text-rose-600': t.type === 'error',
                    'bg-primary-50 text-primary-600': t.type === 'info',
                }"
            >
                <i data-lucide="check" x-show="t.type === 'success'" class="size-4"></i>
                <i data-lucide="circle-alert" x-show="t.type === 'error'" class="size-4"></i>
                <i data-lucide="info" x-show="t.type === 'info'" class="size-4"></i>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-slate-900" x-text="t.title"></p>
                <p class="mt-0.5 text-sm text-slate-500" x-text="t.message"></p>
            </div>
            <button type="button" class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" @click="$store.toast.remove(t.id)">
                <i data-lucide="x" class="size-3.5"></i>
            </button>
        </div>
    </template>
</div>