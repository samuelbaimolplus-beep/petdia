@props([
    'id' => 'confirmModal',
    'title' => '¿Confirmar acción?',
    'description' => 'Esta acción no se puede deshacer.',
    'confirmText' => 'Confirmar',
    'cancelText' => 'Cancelar',
    'confirmVariant' => 'danger',
])

<div
    x-data="{
        open: false,
        actionUrl: '',
        method: 'POST',
        itemName: '',
        itemDetails: '',
        modalTitle: '{{ $title }}',
        modalDescription: '{{ $description }}',
        btnText: '{{ $confirmText }}',
        variant: '{{ $confirmVariant }}',
        openModal(detail) {
            this.actionUrl = detail.url || '';
            this.itemName = detail.name || '';
            this.itemDetails = detail.details || '';
            this.method = detail.method || 'POST';
            this.modalTitle = detail.title || '{{ $title }}';
            this.modalDescription = detail.description || '{{ $description }}';
            this.btnText = detail.confirmText || '{{ $confirmText }}';
            this.variant = detail.variant || '{{ $confirmVariant }}';
            this.open = true;
        },
        closeModal() {
            this.open = false;
        }
    }"
    @open-confirm-modal.window="openModal($event.detail)"
    @keydown.escape.window="closeModal()"
    x-cloak
>
    <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" @click="closeModal()"></div>

            <div
                class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-pop"
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-11 shrink-0 items-center justify-center rounded-xl ring-1"
                        :class="variant === 'danger' ? 'bg-rose-50 text-rose-600 ring-rose-100' : (variant === 'warning' ? 'bg-amber-50 text-amber-600 ring-amber-100' : 'bg-primary-50 text-primary-600 ring-primary-100')"
                    >
                        <i data-lucide="triangle-alert" class="size-6"></i>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-slate-900" x-text="modalTitle"></h3>
                        <p class="mt-1 text-sm text-slate-500" x-text="modalDescription"></p>

                        <div x-show="itemName" class="mt-3 rounded-lg bg-slate-50 p-3 border border-slate-100">
                            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Elemento afectado</p>
                            <p class="text-sm font-semibold text-slate-800" x-text="itemName"></p>
                            <p class="text-xs text-slate-500 mt-0.5" x-show="itemDetails" x-text="itemDetails"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <x-button variant="secondary" @click="closeModal()">
                        {{ $cancelText }}
                    </x-button>

                    <form :action="actionUrl" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="_method" :value="method">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center font-medium transition-colors focus-ring px-4 py-2 text-sm rounded-lg text-white shadow-sm"
                            :class="variant === 'danger' ? 'bg-rose-600 hover:bg-rose-700' : (variant === 'warning' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-primary-600 hover:bg-primary-700')"
                            x-text="btnText"
                        ></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

