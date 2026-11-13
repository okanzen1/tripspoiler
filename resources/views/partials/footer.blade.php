{{-- Footer: koyu; solda logo ve kısa tanıtım + sosyal medya, ortada/sağda bağlantı sütunları, altta telif --}}
@php
    $columns = [
        __('footer.explore') => [
            [__('footer.home'), route('home'), 'home'],
            [__('footer.activities'), route('activities.index'), 'activities.*'],
            [__('footer.cities'), route('cities.index'), 'cities.*'],
            [__('footer.blog'), route('blog.index'), 'blog.*'],
            ...(\App\Models\Campaign::liveCount() ? [[__('navbar.campaigns'), route('campaigns'), 'campaigns']] : []),
        ],
        __('footer.company') => [
            [__('footer.about'), route('about'), 'about'],
            [__('footer.reviews'), route('reviews.index'), 'reviews.*'],
            [__('footer.contact'), route('contact'), 'contact'],
            [__('footer.weather'), route('weather'), 'weather'],
            [__('footer.time'), route('time'), 'time'],
            [__('footer.rates'), route('rates'), 'rates'],
            [__('footer.privacy'), route('privacy'), 'privacy'],
        ],
    ];
    $socials = \App\Support\Socials::all();
@endphp

<footer class="relative overflow-hidden bg-slate-950 text-white/70">
    <div aria-hidden="true" class="pointer-events-none absolute -left-24 -top-24 size-[28rem] rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.22),transparent_65%)]"></div>

    <div class="relative mx-auto max-w-7xl px-4 pb-8 pt-14 md:pt-16">
        <div class="grid gap-10 md:grid-cols-12">

            {{-- MARKA --}}
            <div class="md:col-span-5">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/logo-mark.webp') }}" alt="" width="210" height="240" class="h-16 w-auto drop-shadow-[0_8px_20px_rgba(0,0,0,0.45)]" loading="lazy">
                    <span class="text-3xl font-extrabold tracking-tight text-white">Trip<span class="text-[#E0393E]">Spoiler</span></span>
                </a>
                <p class="mt-4 max-w-sm text-sm leading-relaxed">{{ __('footer.tagline') }}</p>

                <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.18em] text-white/45">{{ __('footer.follow') }}</p>
                <div class="mt-3 flex gap-2.5">
                    @foreach ($socials as [$name, $url, , $icon])
                        <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $name }}"
                            class="grid size-10 place-items-center rounded-full bg-white/5 text-white/80 ring-1 ring-white/10 transition hover:bg-[#C62E2E] hover:text-white hover:ring-[#C62E2E]">
                            <svg class="size-[18px]" fill="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- BAĞLANTILAR --}}
            <div class="grid grid-cols-2 gap-8 md:col-span-7 md:grid-cols-3">
                @foreach ($columns as $heading => $links)
                    <nav aria-label="{{ $heading }}" class="{{ $loop->first ? 'md:col-start-2' : '' }}">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-white/45">{{ $heading }}</p>
                        <ul class="mt-4 space-y-3 text-sm">
                            @foreach ($links as [$label, $url, $match])
                                <li>
                                    <a href="{{ $url }}" @class(['transition hover:text-white', 'font-semibold text-white' => request()->routeIs($match)])>{{ $label }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endforeach
            </div>
        </div>

        {{-- ALT SATIR --}}
        <div class="mt-12 flex flex-col gap-4 border-t border-white/10 pt-6 text-xs text-white/45 lg:flex-row lg:items-center lg:justify-between">
            <p>© {{ date('Y') }} TripSpoiler. {{ __('footer.rights') }}
                <span aria-hidden="true" class="px-1 text-white/20">·</span>
                <button type="button" onclick="window.dispatchEvent(new Event('cookie-settings'))" class="underline decoration-white/20 underline-offset-2 transition hover:text-white">{{ __('cookies.settings') }}</button>
            </p>
            {{-- diller: nokta ile ayrılmış, sarılabilir --}}
            <nav aria-label="Language" class="flex flex-wrap items-center gap-x-1 gap-y-2">
                @foreach (LaravelLocalization::getSupportedLocales() as $code => $properties)
                    @unless ($loop->first)<span aria-hidden="true" class="px-1 text-white/20">·</span>@endunless
                    <a href="{{ LaravelLocalization::getLocalizedURL($code, null, [], true) }}" hreflang="{{ $code }}"
                        @class(['rounded-md px-1.5 py-0.5 transition hover:text-white', 'bg-white/10 font-semibold text-white' => app()->getLocale() === $code])>{{ $properties['native'] }}</a>
                @endforeach
            </nav>
            </div>
            {{-- Ücretsiz veri kaynaklarının istediği kaynak gösterimi (DB-IP: CC BY 4.0, ExchangeRate-API) --}}
            <p class="mt-4 text-[11px] text-white/30">
                <a href="https://db-ip.com" target="_blank" rel="noopener nofollow" class="transition hover:text-white/60">IP Geolocation by DB-IP</a>
                <span aria-hidden="true" class="px-1 text-white/15">·</span>
                <a href="https://www.exchangerate-api.com" target="_blank" rel="noopener nofollow" class="transition hover:text-white/60">Rates By Exchange Rate API</a>
                <span aria-hidden="true" class="px-1 text-white/15">·</span>
                <a href="https://www.met.no/en" target="_blank" rel="noopener nofollow" class="transition hover:text-white/60">Weather data from MET Norway</a>
            </p>
        </div>
    </div>
</footer>
