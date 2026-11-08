{{--
    Sayfa yolu (breadcrumb): görünen bağlantılar + Google için BreadcrumbList yapısal verisi.
    items: [['label' => 'Aktiviteler', 'url' => '…'], …]; sonuncusu bu sayfa (bağlantısız görünür, veride adresi var).
--}}
@props(['items' => [], 'dark' => false])

@php
    $items = array_values(array_filter($items, fn ($item) => filled($item['label'] ?? null)));
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => collect($items)->map(fn ($item, $i) => [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => strip_tags($item['label']),
            'item' => $item['url'] ?? url()->current(),
        ])->all(),
    ];
@endphp

<nav aria-label="{{ __('activity-show.breadcrumb') }}" {{ $attributes->merge(['class' => 'min-w-0']) }}>
    <ol class="flex min-w-0 items-center gap-1.5 overflow-x-auto whitespace-nowrap text-sm [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($items as $item)
            <li @class(['flex items-center gap-1.5', 'min-w-0' => $loop->last, 'shrink-0' => !$loop->last])>
                @if (!$loop->first)
                    <svg class="size-3.5 shrink-0 {{ $dark ? 'text-white/40' : 'text-slate-300' }} rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6"/></svg>
                @endif
                @if ($loop->last)
                    <span aria-current="page" class="truncate font-semibold {{ $dark ? 'text-white' : 'text-slate-900' }} max-w-[16rem] sm:max-w-md">{{ $item['label'] }}</span>
                @else
                    <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1.5 font-medium transition {{ $dark ? 'text-white/70 hover:text-white' : 'text-slate-500 hover:text-[#C62E2E]' }}">
                        @if ($loop->first)
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 11.5 12 5l8 6.5V19a1 1 0 0 1-1 1h-4.5v-5h-5v5H5a1 1 0 0 1-1-1z"/></svg>
                        @endif
                        {{ $item['label'] }}
                    </a>
                @endif
            </li>
        @endforeach
    </ol>
</nav>

@push('scripts')
    @jsonld($schema)
@endpush
