{{--
    Masaüstü yan menüsü: header sayfayla yukarı kaydıktan sonra (anasayfada hero bitince, diğer sayfalarda header
    geçilince) sağda belirir. Telefon/tablette yok; orada header sabit kalır.
--}}
@php
    // Küçük yuvarlaklar; en altta büyük kırmızı Aktiviteler
    $dock = [
        [__('navbar.home'), route('home'), 'home', '<path d="M3.5 10.2 12 3.5l8.5 6.7V19a1.5 1.5 0 0 1-1.5 1.5h-4v-6h-6v6H5A1.5 1.5 0 0 1 3.5 19z" fill="currentColor" fill-opacity=".14"/><path d="M3.5 10.2 12 3.5l8.5 6.7V19a1.5 1.5 0 0 1-1.5 1.5h-4v-6h-6v6H5A1.5 1.5 0 0 1 3.5 19z"/>'],
        [__('navbar.cities'), route('cities.index'), 'cities.*', '<path d="M9 4.5 3.5 6.6v13l5.5-2.1 6 2.1 5.5-2.1v-13L15 6.6z" fill="currentColor" fill-opacity=".14"/><path d="M9 4.5 3.5 6.6v13l5.5-2.1 6 2.1 5.5-2.1v-4.2M9 4.5v13M9 4.5l3.2 1.1M15 13.5v6.1"/><path d="M17.5 12.3s-3.2-2.7-3.2-5.3a3.2 3.2 0 0 1 6.4 0c0 2.6-3.2 5.3-3.2 5.3z" fill="currentColor" fill-opacity=".14"/><path d="M17.5 12.3s-3.2-2.7-3.2-5.3a3.2 3.2 0 0 1 6.4 0c0 2.6-3.2 5.3-3.2 5.3z"/><circle cx="17.5" cy="7" r="1"/>'],
        [__('navbar.blog'), route('blog.index'), 'blog.*', '<path d="M12 7c-1.6-1.7-4.3-2.5-8-2.5v13c3.7 0 6.4.8 8 2.5 1.6-1.7 4.3-2.5 8-2.5v-13c-3.7 0-6.4.8-8 2.5z" fill="currentColor" fill-opacity=".14"/><path d="M12 7c-1.6-1.7-4.3-2.5-8-2.5v13c3.7 0 6.4.8 8 2.5 1.6-1.7 4.3-2.5 8-2.5v-13c-3.7 0-6.4.8-8 2.5zM12 7v13M7 8.5h2M7 11.5h2M15 8.5h2M15 11.5h2"/>'],
    ];
    // Gezgin araçları: hava durumu, yerel saat, döviz kurları (biraz daha küçük yuvarlaklar)
    $tools = [
        [__('footer.weather'), route('weather'), 'weather', '<circle cx="9" cy="9" r="3.4" fill="currentColor" fill-opacity=".14"/><circle cx="9" cy="9" r="3.4"/><path d="M9 2.8v1.4M2.8 9h1.4M4.6 4.6l1 1M13.4 4.6l-1 1"/><path d="M9.5 20h8.6a3.4 3.4 0 0 0 .4-6.8 5 5 0 0 0-9.6-.6A3.7 3.7 0 0 0 9.5 20z" fill="currentColor" fill-opacity=".14"/><path d="M9.5 20h8.6a3.4 3.4 0 0 0 .4-6.8 5 5 0 0 0-9.6-.6A3.7 3.7 0 0 0 9.5 20z"/>'],
        [__('footer.time'), route('time'), 'time', '<circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".14"/><circle cx="12" cy="12" r="9"/><path d="M12 7v5.2l3.3 2"/>'],
        [__('footer.rates'), route('rates'), 'rates', '<circle cx="9" cy="9" r="5.5" fill="currentColor" fill-opacity=".14"/><circle cx="9" cy="9" r="5.5"/><circle cx="15" cy="15" r="5.5"/><path d="M7.6 7.7c.3-.5.8-.8 1.5-.8.9 0 1.5.5 1.5 1.1 0 1.4-3 .8-3 2.2 0 .6.6 1.1 1.5 1.1.7 0 1.2-.3 1.5-.8M9 5.8v.9M9 11.3v.9"/>'],
        [__('footer.contact'), route('contact'), 'contact', '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z" fill="currentColor" fill-opacity=".14"/><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/>'],
    ];
    $activitiesActive = request()->routeIs('activities.*');
    // Bulunduğun sayfanın bağlantısına tıklanınca sayfa yenilenmez, yumuşakça en üste çıkar
    $toTop = "\$event.preventDefault(); window.scrollTo({ top: 0, behavior: 'smooth' })";
@endphp

<nav aria-label="TripSpoiler" x-data="{
        show: false,
        update() {
            // Anasayfa: hero bitmeden az önce ([data-dock-start]); data-dock-at verilen banner: o oranda kaydırılınca (0.3 = üçte biri);
            // diğer sayfalar: sayfanın ilk bölümü (banner) üçte bir kaydırılınca — hiçbir banner'ın üstünde çıkmaz
            const hero = document.querySelector('[data-dock-start]');
            const banner = hero ?? document.querySelector('main section');
            if (!banner) { this.show = window.scrollY > 200; return; }
            const at = hero ? parseFloat(hero.dataset.dockAt) : 0.3;
            const top = banner.getBoundingClientRect().top + window.scrollY;
            this.show = window.scrollY > (at ? top + banner.offsetHeight * at : top + banner.offsetHeight - 120);
        },
    }" x-init="update()" @scroll.window.throttle.100ms="update()" @resize.window.throttle.200ms="update()"
    class="pointer-events-none fixed right-5 top-1/2 z-40 hidden -translate-y-1/2 lg:block rtl:left-5 rtl:right-auto">
    <div x-show="show" x-cloak
        x-transition:enter="transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition duration-300 ease-in" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-8"
        class="pointer-events-auto relative flex flex-col items-center gap-4">

        {{-- butonları birleştiren ince çizgi --}}
        <span aria-hidden="true" class="absolute bottom-8 left-1/2 top-4 -z-10 w-px -translate-x-1/2 bg-[#C62E2E]/35"></span>

        {{-- Kampanyalar: farklı renk (turuncu-pembe), nabız gibi atan halka; yayında kampanya yoksa yok --}}
        @if (\App\Models\Campaign::liveCount())
            @php $dealActive = request()->routeIs('campaigns'); @endphp
            <a href="{{ route('campaigns') }}" aria-label="{{ __('navbar.campaigns') }}" @if ($dealActive) aria-current="page" @click="{{ $toTop }}" @endif
                class="group relative grid size-12 place-items-center rounded-full bg-gradient-to-b from-amber-300 to-amber-400 text-slate-950 shadow-[0_12px_28px_-10px_rgba(245,158,11,0.9)] ring-2 ring-white transition duration-300 hover:-translate-x-1 hover:scale-105 rtl:hover:translate-x-1">
                <span class="absolute inset-0 -z-10 animate-ping rounded-full bg-amber-400/40 [animation-duration:2.4s]" aria-hidden="true"></span>
                <span class="campaign-shine absolute inset-0 overflow-hidden rounded-full" aria-hidden="true"></span>
                <svg class="campaign-wiggle size-[22px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v8a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-8M2.5 7.5h19v4.5h-19zM12 21V7.5M12 7.5H8a2.5 2.5 0 1 1 0-5c2.5 0 4 5 4 5zM12 7.5h4a2.5 2.5 0 1 0 0-5c-2.5 0-4 5-4 5z"/></svg>
                <span class="absolute -right-1 -top-1 grid size-5 place-items-center rounded-full bg-[#C62E2E] text-[10px] font-extrabold text-white shadow ring-2 ring-white">{{ \App\Models\Campaign::liveCount() }}</span>
                @if ($dealActive)
                    <span class="absolute -right-3.5 top-1/2 size-2 -translate-y-1/2 rounded-full bg-[#C62E2E] rtl:-left-3.5 rtl:right-auto"></span>
                @endif
                <span class="pointer-events-none absolute right-full mr-3 translate-x-1 whitespace-nowrap rounded-lg bg-slate-900/90 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition duration-200 group-hover:translate-x-0 group-hover:opacity-100 rtl:left-full rtl:right-auto rtl:ml-3 rtl:mr-0">{{ __('navbar.campaigns') }}</span>
            </a>
        @endif

        @foreach ($dock as [$label, $url, $match, $icon])
            @php $active = request()->routeIs($match); @endphp
            <a href="{{ $url }}" aria-label="{{ $label }}" @if ($active) aria-current="page" @click="{{ $toTop }}" @endif
                class="group relative grid size-12 place-items-center rounded-full bg-white text-slate-700 ring-1 ring-slate-200/80 shadow-[0_10px_26px_-12px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-x-1 hover:text-[#C62E2E] rtl:hover:translate-x-1">
                <svg class="size-[22px] transition-transform duration-300 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                @if ($active)
                    <span class="absolute -right-3.5 top-1/2 size-2 -translate-y-1/2 rounded-full bg-[#C62E2E] rtl:-left-3.5 rtl:right-auto"></span>
                @endif
                <span class="pointer-events-none absolute right-full mr-3 translate-x-1 whitespace-nowrap rounded-lg bg-slate-900/90 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition duration-200 group-hover:translate-x-0 group-hover:opacity-100 rtl:left-full rtl:right-auto rtl:ml-3 rtl:mr-0">{{ $label }}</span>
            </a>
        @endforeach

        {{-- gezgin araçları: biraz küçük --}}
        @foreach ($tools as [$label, $url, $match, $icon])
            @php $active = request()->routeIs($match); @endphp
            <a href="{{ $url }}" aria-label="{{ $label }}" @if ($active) aria-current="page" @click="{{ $toTop }}" @endif
                @class(['group relative grid size-10 place-items-center rounded-full bg-white ring-1 shadow-[0_10px_26px_-12px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-x-1 hover:text-[#C62E2E] rtl:hover:translate-x-1',
                    'text-[#C62E2E] ring-[#C62E2E]/40' => $active, 'text-slate-600 ring-slate-200/80' => !$active])>
                <svg class="size-[19px] transition-transform duration-300 group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
                @if ($active)
                    <span class="absolute -right-3.5 top-1/2 size-2 -translate-y-1/2 rounded-full bg-[#C62E2E] rtl:-left-3.5 rtl:right-auto"></span>
                @endif
                <span class="pointer-events-none absolute right-full mr-3 translate-x-1 whitespace-nowrap rounded-lg bg-slate-900/90 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition duration-200 group-hover:translate-x-0 group-hover:opacity-100 rtl:left-full rtl:right-auto rtl:ml-3 rtl:mr-0">{{ $label }}</span>
            </a>
        @endforeach

        {{-- Dil: yuvarlağa tıklayınca diller solda açılır (en üste çıkmadan dil değiştirmek için) --}}
        <div x-data="{ lang: false }" @click.outside="lang = false" @keydown.escape.window="lang = false" class="relative">
            <button type="button" @click="lang = !lang" :aria-expanded="lang" aria-label="{{ __('navbar.language') }}"
                :class="lang ? 'text-[#C62E2E] ring-[#C62E2E]/40' : 'text-slate-600 ring-slate-200/80'"
                class="group relative grid size-10 place-items-center rounded-full bg-white ring-1 shadow-[0_10px_26px_-12px_rgba(15,23,42,0.35)] transition duration-300 hover:-translate-x-1 hover:text-[#C62E2E] rtl:hover:translate-x-1">
                <span class="flex flex-col items-center leading-none">
                    <svg class="size-[15px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z" /></svg>
                    <span class="mt-0.5 text-[9px] font-extrabold uppercase tracking-wide">{{ app()->getLocale() }}</span>
                </span>
                <span x-show="!lang" class="pointer-events-none absolute right-full mr-3 translate-x-1 whitespace-nowrap rounded-lg bg-slate-900/90 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition duration-200 group-hover:translate-x-0 group-hover:opacity-100 rtl:left-full rtl:right-auto rtl:ml-3 rtl:mr-0">{{ __('navbar.language') }}</span>
            </button>
            <div x-show="lang" x-cloak
                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute right-full top-1/2 mr-4 w-72 -translate-y-1/2 rounded-2xl border border-slate-200 bg-white p-1.5 shadow-2xl rtl:left-full rtl:right-auto rtl:ml-4 rtl:mr-0">
                <div class="grid grid-cols-2 gap-0.5">
                    @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}" hreflang="{{ $localeCode }}" lang="{{ $localeCode }}"
                            @class(['flex items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm', 'bg-red-50 font-semibold text-[#C62E2E]' => app()->getLocale() == $localeCode, 'text-slate-700 hover:bg-slate-50' => app()->getLocale() != $localeCode])>
                            <span class="truncate">{{ \Illuminate\Support\Str::ucfirst($properties['native']) }}</span>
                            <span class="text-[10px] font-semibold uppercase text-slate-400">{{ $localeCode }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Aktiviteler: büyük, kırmızı, en altta --}}
        <a href="{{ route('activities.index') }}" aria-label="{{ __('navbar.activities') }}" @if ($activitiesActive) aria-current="page" @click="{{ $toTop }}" @endif
            class="group relative mt-1 grid size-16 place-items-center rounded-full bg-[#C62E2E] text-white shadow-[0_16px_36px_-10px_rgba(198,46,46,0.7)] transition duration-300 hover:scale-105 hover:bg-[#A82424]">
            <svg class="size-8 transition-transform duration-500 group-hover:rotate-[25deg]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9" fill="currentColor" fill-opacity=".16"/><circle cx="12" cy="12" r="9"/><path d="m15.6 8.4-2.1 5.1-5.1 2.1 2.1-5.1z" fill="currentColor" fill-opacity=".35"/><path d="m15.6 8.4-2.1 5.1-5.1 2.1 2.1-5.1z"/><path d="M12 3v1.6M12 19.4V21M3 12h1.6M19.4 12H21"/></svg>
            @if ($activitiesActive)
                <span class="absolute -right-3.5 top-1/2 size-2 -translate-y-1/2 rounded-full bg-[#C62E2E] rtl:-left-3.5 rtl:right-auto"></span>
            @endif
            <span class="pointer-events-none absolute right-full mr-3 translate-x-1 whitespace-nowrap rounded-lg bg-slate-900/90 px-2.5 py-1.5 text-xs font-semibold text-white opacity-0 shadow-lg transition duration-200 group-hover:translate-x-0 group-hover:opacity-100 rtl:left-full rtl:right-auto rtl:ml-3 rtl:mr-0">{{ __('navbar.activities') }}</span>
        </a>
    </div>
</nav>
