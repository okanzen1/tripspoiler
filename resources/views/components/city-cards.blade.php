@props(['cities', 'current' => null, 'url', 'loadingEvent' => null, 'see' => null])
{{-- Fotoğraflı şehir kartları: tıklayınca o şehre geçilir; seçili olan kırmızı çerçeveli + tik. Telefonda yana kayar, geniş ekranda iki sütun.
     Kullanım: aktiviteler ve blog sayfası (koyu arka plan üstünde). --}}
@php
    $flagOf = fn ($c) => $c->country?->code ? implode('', array_map(fn ($ch) => mb_chr(0x1F1E6 + ord($ch) - 65), str_split(strtoupper($c->country->code)))) : '';
    $cityPhoto = fn ($c) => \App\Models\PageBanner::urls('cities', $c->id)['desktop'];
@endphp
<div {{ $attributes }}>
    <p class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-white/80">
        <span class="relative flex size-2.5">
            <span class="absolute inline-flex size-full animate-ping rounded-full bg-[#FF6B6E] opacity-75 motion-reduce:animate-none"></span>
            <span class="relative inline-flex size-2.5 rounded-full bg-[#FF6B6E]"></span>
        </span>
        {{ $cities->count() > 1 ? __('activities-page.switch_city') : __('activities-page.city') }}
    </p>
    <div class="-mx-4 mt-3 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-1 [scrollbar-width:none] lg:mx-0 lg:grid lg:grid-cols-2 lg:overflow-visible lg:px-0 [&::-webkit-scrollbar]:hidden">
        @foreach ($cities as $c)
            @php $active = $current && $c->is($current); @endphp
            <a href="{{ $url($c) }}"
                @if (!$active && $loadingEvent) @click="$dispatch('{{ $loadingEvent }}')" @endif
                @if ($active) aria-current="page" @endif
                @class([
                    'group relative isolate flex h-32 w-44 shrink-0 snap-start flex-col justify-end overflow-hidden rounded-3xl p-3.5 text-white transition duration-300 sm:h-36 sm:w-52 lg:w-auto',
                    'ring-[3px] ring-[#FF6B6E] shadow-[0_18px_40px_-14px_rgba(255,107,110,0.75)]' => $active,
                    'ring-1 ring-white/25 hover:-translate-y-1 hover:ring-white/60 hover:shadow-[0_18px_40px_-16px_rgba(0,0,0,0.8)]' => !$active,
                ])>
                <img src="{{ $cityPhoto($c) }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-700 group-hover:scale-110 motion-reduce:transition-none">
                <span class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/90 via-slate-950/35 to-slate-950/10"></span>
                @if ($active)
                    <span class="absolute right-2.5 top-2.5 grid size-7 place-items-center rounded-full bg-[#FF6B6E] text-white shadow-lg rtl:left-2.5 rtl:right-auto">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                    </span>
                @else
                    <span class="absolute right-2.5 top-2.5 grid size-7 place-items-center rounded-full bg-white/90 text-slate-900 shadow-lg transition duration-300 group-hover:scale-110 group-hover:bg-white rtl:left-2.5 rtl:right-auto">
                        <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </span>
                @endif
                <span class="text-2xl leading-none drop-shadow" aria-hidden="true">{{ $flagOf($c) }}</span>
                <span class="mt-1 text-lg font-extrabold leading-tight tracking-tight">{{ $c->name }}</span>
                <span class="text-xs font-semibold text-white/75">{{ $active ? __('activities-page.city_selected') : ($see ?? __('activities-page.city_see')) }}</span>
            </a>
        @endforeach
    </div>
</div>
