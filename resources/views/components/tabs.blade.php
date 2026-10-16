@props([
    'tabs' => [],
    'default' => null,
    'variant' => 'liquid-glass',
])

@php
    $defaultTab = $default ?? ($tabs[0]['key'] ?? $tabs[0] ?? '');
@endphp

<div x-data="{ activeTab: '{{ $defaultTab }}' }" {{ $attributes->merge(['class' => 'w-full']) }}>
    @if ($variant === 'liquid-glass')
        <!-- Barra de pestañas estilo píldora de cristal líquido Apple (iOS Segmented Control) -->
        <div class="inline-flex max-w-full p-1.5 rounded-2xl liquid-glass border border-slate-200/80 dark:border-slate-800 shadow-sm ios-smooth-scroll">
            <nav class="flex space-x-1.5" aria-label="Tabs">
                @foreach ($tabs as $tab)
                    @php
                        $key = is_array($tab) ? $tab['key'] : $tab;
                        $label = is_array($tab) ? $tab['label'] : $tab;
                        $badge = is_array($tab) ? ($tab['badge'] ?? null) : null;
                        $icon = is_array($tab) ? ($tab['icon'] ?? null) : null;
                    @endphp
                    <button
                        type="button"
                        @click="activeTab = '{{ $key }}'"
                        class="apple-press group inline-flex items-center gap-2 rounded-xl py-2 px-3.5 text-xs sm:text-sm font-medium transition-all duration-200 whitespace-nowrap"
                        :class="activeTab === '{{ $key }}' 
                            ? 'liquid-glass-active font-semibold shadow-sm' 
                            : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 hover:bg-slate-100/70 dark:hover:bg-slate-800/50'"
                    >
                        @if ($icon)
                            <i data-lucide="{{ $icon }}" class="size-4 shrink-0 transition-transform duration-200 group-hover:scale-110"></i>
                        @endif
                        <span>{{ $label }}</span>
                        @if ($badge !== null)
                            <span
                                class="rounded-full px-2 py-0.5 text-[11px] font-semibold transition-colors"
                                :class="activeTab === '{{ $key }}' ? 'bg-slate-900/10 dark:bg-white/20 text-slate-900 dark:text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300'"
                            >
                                {{ $badge }}
                            </span>
                        @endif
                    </button>
                @endforeach
            </nav>
        </div>
    @else
        <!-- Barra de pestañas tradicional subrayada -->
        <div class="border-b border-slate-200 dark:border-slate-800">
            <nav class="-mb-px flex space-x-6 overflow-x-auto ios-smooth-scroll" aria-label="Tabs">
                @foreach ($tabs as $tab)
                    @php
                        $key = is_array($tab) ? $tab['key'] : $tab;
                        $label = is_array($tab) ? $tab['label'] : $tab;
                        $badge = is_array($tab) ? ($tab['badge'] ?? null) : null;
                        $icon = is_array($tab) ? ($tab['icon'] ?? null) : null;
                    @endphp
                    <button
                        type="button"
                        @click="activeTab = '{{ $key }}'"
