{{--
    Telefon menüsü: sağdan (☰ butonunun olduğu taraftan) açılan çekmece, arkası bulanık; zemini hero resmi. Header'daki x-data'nın "menu" değeriyle açılır.
    Header'da backdrop-blur olduğu için (içindeki fixed öğeleri hapseder) body'ye taşınır.
--}}
@php
    // AppServiceProvider'daki composer verir; header başka yerden render edilirse burada hesaplanır
    $menuBackground ??= (function () {
        $image = \App\Models\Setting::many(['hero_image_mobile', 'hero_image']);
        return $image['hero_image_mobile'] ? \App\Models\ActivityImage::mediaUrl($image['hero_image_mobile'])
            : ($image['hero_image'] ? \App\Models\ActivityImage::mediaUrl($image['hero_image'], 'sm') : asset('images/hero-banner.webp'));
    })();
    $links = [
        ['route' => 'home', 'match' => 'home', 'label' => __('navbar.home'),
         'icon' => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5 10v10h14V10"/><path d="M10 20v-5h4v5"/>'],
        ['route' => 'activities.index', 'match' => 'activities.*', 'label' => __('navbar.activities'),
         'icon' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/>'],
        ['route' => 'cities.index', 'match' => 'cities.*', 'label' => __('navbar.cities'),
         'icon' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>'],
        ['route' => 'blog.index', 'match' => 'blog.*', 'label' => __('navbar.blog'),
         'icon' => '<path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M8 7h6M8 11h4"/>'],
        ['route' => 'contact', 'match' => 'contact', 'label' => __('footer.contact'),
         'icon' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/>'],
    ];
@endphp

<template x-teleport="body">
    <div x-show="menu" x-cloak class="fixed inset-0 z-[90] lg:hidden" @keydown.escape.window="menu = false" role="dialog" aria-modal="true" aria-label="Menu">

        {{-- BULANIK ARKA PLAN --}}
        <div x-show="menu" x-transition.opacity.duration.300ms @click="menu = false"
            class="absolute inset-0 bg-slate-950/45 backdrop-blur-md"></div>

        {{-- ÇEKMECE --}}
        <aside x-show="menu"
            x-transition:enter="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
            x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition duration-200 ease-in"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 isolate w-[86%] max-w-sm overflow-hidden bg-slate-950 text-white shadow-[-20px_0_60px_-20px_rgba(2,6,23,0.6)]">

            {{-- ZEMİN: hero resmi + koyulaştırma --}}
            {{-- absolute + kaydırma içteki div'de: "fixed" kullanılırsa geçişteki transform bitince resim çekmeceye değil ekrana göre boyutlanıp sola doğru genişliyordu --}}
            <img src="{{ $menuBackground }}" alt="" aria-hidden="true" class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover object-[30%_center]">
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-b from-slate-950/80 via-slate-950/75 to-slate-950/95"></div>

            <div class="flex h-full flex-col overflow-y-auto overscroll-contain">

            {{-- ÜST --}}
            <div class="flex items-center justify-between px-5 pt-5 pb-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/logo-mark.webp') }}" alt="" width="210" height="240" class="h-10 w-auto">
                    <span class="text-xl font-extrabold tracking-tight text-white">Trip<span class="text-[#C62E2E]">Spoiler</span></span>
                </a>
                <button type="button" @click="menu = false" aria-label="{{ __('search-modal.hint_close') }}"
                    class="grid size-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/15 backdrop-blur transition hover:bg-white/20">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
            </div>

            {{-- BAĞLANTILAR --}}
            <nav class="px-3">
                @foreach ($links as $link)
                    @php($active = request()->routeIs($link['match']))
                    <a href="{{ route($link['route']) }}" @click="menu = false"
                        @class(['flex items-center gap-3.5 rounded-2xl px-3 py-3.5 text-[17px] font-semibold transition',
                                'bg-white/15 text-white ring-1 ring-white/15 backdrop-blur' => $active, 'text-white/90 hover:bg-white/10' => !$active])>
                        <span @class(['grid size-10 place-items-center rounded-xl', 'bg-[#C62E2E] text-white' => $active, 'bg-white/10 text-white/80' => !$active])>
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $link['icon'] !!}</svg>
                        </span>
                        <span class="flex-1">{{ $link['label'] }}</span>
                        <svg class="size-4 text-white/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6" /></svg>
                    </a>
                @endforeach
            </nav>

            <div class="px-5 pt-4">
                <a href="{{ route('activities.index') }}" @click="menu = false"
                    class="flex items-center justify-center rounded-2xl bg-[#C62E2E] px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#C62E2E]/20 transition hover:bg-[#A82424]">
                    {{ __('navbar.cta') }}
                </a>
            </div>

            {{-- DİL --}}
            <div class="mt-6 border-t border-white/10 px-5 pt-5">
                <p class="mb-3 text-[11px] font-bold uppercase tracking-[0.14em] text-white/55">{{ __('navbar.language') }}</p>
                <div class="grid grid-cols-3 gap-2">
                    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" hreflang="{{ $localeCode }}"
                            @class(['truncate rounded-xl px-2 py-2 text-center text-xs font-medium ring-1 transition',
                                    'bg-white text-slate-900 ring-white' => app()->getLocale() == $localeCode,
                                    'text-white/85 ring-white/20 bg-white/5 backdrop-blur hover:bg-white/15' => app()->getLocale() != $localeCode])>
                            {{ $properties['native'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- SOSYAL --}}
            <div class="mt-auto flex items-center gap-3 px-5 pt-8 pb-6">
                <a href="https://instagram.com/tripspoilerofficial" target="_blank" rel="noopener" aria-label="Instagram"
                    class="grid size-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/15 transition hover:bg-[#C62E2E]">
                    <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10z" /><path d="M12 7.5A4.5 4.5 0 1016.5 12 4.51 4.51 0 0012 7.5z" /><circle cx="17.5" cy="6.5" r="1" /></svg>
                </a>
                <a href="https://tiktok.com/@tripspoilerofficial" target="_blank" rel="noopener" aria-label="TikTok"
                    class="grid size-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/15 transition hover:bg-[#C62E2E]">
                    <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M21 8.3a6.6 6.6 0 01-3.8-1.2v7.1a6.4 6.4 0 11-6.4-6.4c.4 0 .8 0 1.2.1v3.3a3.1 3.1 0 10 2.2 3v-12h3.2a6.6 6.6 0 003.6 3.1z" /></svg>
                </a>
                <span class="ml-auto text-xs text-white/50">© {{ date('Y') }} TripSpoiler</span>
            </div>
            </div>
        </aside>
    </div>
</template>
