@props([
    'headers' => [],
    'paginator' => null,
    'empty' => false,
    'emptyTitle' => 'No se encontraron registros',
    'emptyDescription' => 'No hay elementos para mostrar con los filtros actuales.',
])

<div class="overflow-hidden rounded-xl border border-slate-200/80 bg-white shadow-card">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200/80 text-left text-sm">
            @if (!empty($headers))
                <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="px-5 py-3.5 whitespace-nowrap {{ is_array($header) ? ($header['class'] ?? '') : '' }}">
                                {{ is_array($header) ? ($header['label'] ?? '') : $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @elseif (isset($head))
                <thead class="bg-slate-50/75 text-xs font-semibold uppercase tracking-wider text-slate-500">
                    {{ $head }}
                </thead>
            @endif

            <tbody class="divide-y divide-slate-100 bg-white text-slate-700">
                @if ($empty)
                    <tr>
                        <td colspan="{{ count($headers) ?: 10 }}" class="p-8">
                            <x-empty-state
                                icon="search-x"
                                :title="$emptyTitle"
                                :description="$emptyDescription"
                            />
                        </td>
                    </tr>
                @else
                    {{ $slot }}
                @endif
            </tbody>
        </table>
    </div>

    @if ($paginator && method_exists($paginator, 'hasPages') && $paginator->hasPages())
        <div class="border-t border-slate-200/80 px-5 py-3.5 bg-slate-50/40">
            {{ $paginator->links() }}
        </div>
    @elseif (isset($footer))
        <div class="border-t border-slate-200/80 px-5 py-3.5 bg-slate-50/40">
            {{ $footer }}
        </div>
    @endif
</div>

