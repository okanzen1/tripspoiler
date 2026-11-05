@props(['color' => 'bg-white'])

@if (\App\Support\SiteSections::enabled('social'))
{{-- Sosyal medya: solda başlık, sağda Instagram ve TikTok kartları (görsel + hesap + takip butonu) --}}
@php
    // Başlık yazıları panelden (Site ayarları > Bölüm yazıları); yoksa dil dosyası
    $texts = \App\Models\SectionText::texts('social', app()->getLocale(), [
        'eyebrow' => __('social-presence-section.eyebrow'), 'title' => __('social-presence-section.title'),
        'title_highlight' => __('social-presence-section.title_highlight'), 'description' => __('social-presence-section.text'),
    ]);
@endphp
@php
    $accounts = [
        ['name' => 'Instagram', 'handle' => '@tripspoilerofficial', 'url' => 'https://instagram.com/tripspoilerofficial', 'img' => 'images/social-instagram.webp',
         'cta' => __('social-presence-section.instagram_cta'), 'badge' => 'bg-gradient-to-tr from-[#F58529] via-[#DD2A7B] to-[#8134AF]',
         'icon' => '<path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10z"/><path d="M12 7.5A4.5 4.5 0 1016.5 12 4.51 4.51 0 0012 7.5zm0 7.2A2.7 2.7 0 1114.7 12 2.7 2.7 0 0112 14.7z"/><circle cx="17.5" cy="6.5" r="1"/>'],
        ['name' => 'TikTok', 'handle' => '@tripspoilerofficial', 'url' => 'https://tiktok.com/@tripspoilerofficial', 'img' => 'images/social-tiktok.webp',
         'cta' => __('social-presence-section.tiktok_cta'), 'badge' => 'bg-slate-950',
         'icon' => '<path d="M21 8.3a6.6 6.6 0 01-3.8-1.2v7.1a6.4 6.4 0 11-6.4-6.4c.4 0 .8 0 1.2.1v3.3a3.1 3.1 0 102.2 3v-12h3.2a6.6 6.6 0 003.6 3.1z"/>'],
    ];
@endphp

<section class="{{ $color }} relative overflow-hidden py-16 md:py-24">
    <div class="mx-auto grid max-w-7xl items-start gap-10 px-4 lg:grid-cols-12 lg:gap-16">

        <div class="lg:col-span-5">
            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                <span class="h-px w-6 bg-[#C62E2E]"></span>{{ $texts['eyebrow'] }}
            </p>
            <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                {{ $texts['title'] }} <span class="text-[#C62E2E]">{{ $texts['title_highlight'] }}</span>
            </h2>
            <p class="mt-4 text-base leading-relaxed text-slate-600 md:text-lg">{{ $texts['description'] }}</p>
        </div>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:col-span-7">
            @foreach ($accounts as $account)
                <a href="{{ $account['url'] }}" target="_blank" rel="noopener noreferrer"
                    class="group overflow-hidden rounded-3xl bg-white ring-1 ring-slate-200/80 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                           shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)] hover:-translate-y-1.5 hover:shadow-[0_28px_60px_-28px_rgba(15,23,42,0.45)]">
                    <span class="relative block aspect-[4/3] overflow-hidden bg-slate-100 sm:aspect-[4/4]">
                        <img src="{{ asset($account['img']) }}" alt="TripSpoiler {{ $account['name'] }}" loading="lazy" decoding="async"
                            class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105">
                    </span>
                    <span class="flex items-center gap-3 p-4">
                        <span class="grid size-11 shrink-0 place-items-center rounded-2xl text-white {{ $account['badge'] }}">
                            <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">{!! $account['icon'] !!}</svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-bold text-slate-900">{{ $account['handle'] }}</span>
                            <span class="block truncate text-xs text-slate-500">{{ $account['cta'] }}</span>
                        </span>
                        <span class="shrink-0 rounded-full bg-slate-900 px-4 py-2 text-xs font-semibold text-white transition group-hover:bg-[#C62E2E]">
                            {{ __('social-presence-section.follow') }}
                        </span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
