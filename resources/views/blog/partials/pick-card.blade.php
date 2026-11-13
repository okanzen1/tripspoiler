{{-- Blog yazısı: "Editörün seçimi" kartı (kapakta masaüstünde, telefonda kapağın altında). Değişkenler show.blade.php'den. --}}
@if ($previewActivity)
{{-- öneri kartı: üst kısım bizim aktivite sayfasına; tek aktivitede altta "Detaylar" + "Bilet al" --}}
<div class="relative isolate overflow-hidden rounded-2xl bg-[#0F1420] p-4 text-white ring-1 ring-white/10">
    <div class="pointer-events-none absolute -right-10 -top-12 -z-10 size-40 rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.45),transparent_65%)]" aria-hidden="true"></div>
    <a href="{{ $singleActivity ? $activityUrl($previewActivity) : '#activitiesSection' }}" class="group block">
        <span class="flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-amber-300">
            <svg class="size-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6l-5.4 2.9 1.2-6-4.5-4.2 6.1-.7Z"/></svg>
            {{ $singleActivity ? __('blog.editors_pick') : __('blog.editors_picks') }}
        </span>
        <span class="mt-3 block overflow-hidden rounded-xl ring-1 ring-white/10">
            <span class="relative block aspect-[2.4/1] bg-white/5 lg:aspect-[16/9]">
                @if ($previewActivity->cover)
                    <img src="{{ $previewActivity->cover->url('sm') }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
                @endif
                @if ($activityCount > 1)
                    <span class="absolute bottom-2 right-2 rounded-full bg-[#0F1420]/85 px-2 py-0.5 text-[11px] font-bold">+{{ $activityCount - 1 }}</span>
                @endif
            </span>
        </span>
        <span class="mt-3 line-clamp-3 block text-[15px] font-bold leading-snug transition group-hover:text-[#FF8A8C]">
            {{ $singleActivity ? $previewActivity->name : $activityCount . ' ' . __('blog.experiences') }}
        </span>
    </a>
    @if ($singleActivity)
        <div class="mt-4 grid gap-2 max-lg:grid-cols-2">
            <a href="{{ $activityUrl($singleActivity) }}" class="flex h-[42px] items-center justify-center gap-1.5 whitespace-nowrap rounded-xl bg-[#C62E2E] px-3 text-[13px] font-bold text-white transition hover:bg-[#A82424]">
                {{ __('blog.see_details') }} {!! $arrowIcon !!}
            </a>
            @if ($singleActivity->affiliate_url)
                <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener" class="flex h-[40px] items-center justify-center gap-1.5 whitespace-nowrap rounded-xl bg-gradient-to-r from-amber-300 to-amber-500 px-3 text-[13px] font-extrabold text-slate-950 shadow-[0_10px_24px_-12px_rgba(245,158,11,0.9)] transition hover:from-amber-200 hover:to-amber-400">
                    {{ __('blog.buy_tickets') }} {!! $extIcon !!}
                </a>
            @endif
        </div>
    @else
        <a href="#activitiesSection" class="mt-4 flex min-h-[40px] items-center justify-center gap-1.5 rounded-xl bg-[#C62E2E] text-[13px] font-bold text-white transition hover:bg-[#A82424]">{{ __('blog.see_experiences') }} {!! $arrowIcon !!}</a>
    @endif
</div>
@endif
