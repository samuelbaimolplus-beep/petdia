@props(['items' => []])

@if(!empty($items))
    <nav aria-label="Breadcrumb" class="border-b border-slate-100 bg-slate-50/50 px-4 py-2 sm:px-6">
        <ol class="flex items-center gap-1.5 text-xs text-slate-500 flex-wrap">
            <li>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-1 hover:text-primary-600 transition-colors">
                    <i data-lucide="home" class="size-3.5"></i>
                    <span class="sr-only sm:not-sr-only">Dashboard</span>
                </a>
            </li>

            @foreach($items as $item)
                <li class="flex items-center gap-1.5">
                    <i data-lucide="chevron-right" class="size-3 text-slate-400 shrink-0"></i>
                    @if(!empty($item['url']) && !$loop->last)
                        <a href="{{ $item['url'] }}" class="hover:text-primary-600 font-medium transition-colors truncate max-w-40 sm:max-w-xs">
                            {{ $item['label'] }}
                        </a>
                    @else
                        <span class="font-semibold text-slate-800 truncate max-w-48 sm:max-w-sm" aria-current="page">
                            {{ $item['label'] }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif

