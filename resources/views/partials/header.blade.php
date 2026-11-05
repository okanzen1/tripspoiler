@php
    // Anasayfada header, tam ekran hero görselinin üstünde şeffaf başlar; kaydırınca beyaza döner
    $transparentHeader = request()->routeIs('home');
@endphp
<header x-data="{ top: {{ $transparentHeader ? 'true' : 'false' }}, menu: false }"
    x-effect="document.documentElement.classList.toggle('overflow-hidden', menu); document.documentElement.classList.toggle('overlay-open', menu)"
    @if ($transparentHeader) x-init="top = window.scrollY < 24" @scroll.window="top = window.scrollY < 24"
    :data-transparent="top" data-transparent="true" @endif
    class="group sticky top-0 z-50 lg:relative border-b border-slate-200/70 bg-white/90 backdrop-blur transition-colors duration-300
           data-[transparent=true]:border-white/10 data-[transparent=true]:bg-transparent data-[transparent=true]:backdrop-blur-none">
    <div class="max-w-7xl mx-auto px-4 h-[68px] md:h-20 flex items-center justify-between gap-6">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
            <img src="{{ asset('images/logo-mark.webp') }}" alt="" width="210" height="240" class="h-11 md:h-14 w-auto">
            <span class="text-xl md:text-2xl font-extrabold tracking-tight text-slate-900 group-data-[transparent=true]:text-white">
                Trip<span class="text-[#C62E2E]">Spoiler</span>
            </span>
        </a>

        {{-- NAV --}}
        <nav class="hidden lg:flex flex-1 items-center justify-center gap-8 text-[15px] font-semibold text-slate-700 group-data-[transparent=true]:text-white">

            <a href="{{ route('home') }}"
                class="{{ request()->routeIs('home') ? 'text-[#C62E2E] group-data-[transparent=true]:text-[#FF6B6E]' : 'hover:text-[#C62E2E] group-data-[transparent=true]:hover:text-[#FF6B6E]' }}">
                {{ __('navbar.home') }}
            </a>

            {{-- Aktiviteler: boşken çerçeveli hap, üstüne gelince kırmızı çerçeve, seçiliyken kırmızı dolu --}}
            <a href="{{ route('activities.index') }}"
                @class(['-mx-1 inline-flex h-10 items-center rounded-full px-4 ring-1 transition',
                    'bg-[#C62E2E] text-white ring-[#C62E2E] shadow-[0_8px_20px_-10px_rgba(198,46,46,0.9)] hover:bg-[#A82424] group-data-[transparent=true]:text-white' => request()->routeIs('activities.*'),
                    'ring-slate-200 hover:text-[#C62E2E] hover:ring-[#C62E2E]/50 group-data-[transparent=true]:ring-white/30 group-data-[transparent=true]:hover:bg-white/10 group-data-[transparent=true]:hover:text-white group-data-[transparent=true]:hover:ring-white/60' => !request()->routeIs('activities.*')])>
                {{ __('navbar.activities') }}
            </a>

            <a href="{{ route('cities.index') }}"
                class="{{ request()->routeIs('cities.*') ? 'text-[#C62E2E] group-data-[transparent=true]:text-[#FF6B6E]' : 'hover:text-[#C62E2E] group-data-[transparent=true]:hover:text-[#FF6B6E]' }}">
                {{ __('navbar.cities') }}
            </a>

            <a href="{{ route('blog.index') }}"
                class="{{ request()->routeIs('blog.*') ? 'text-[#C62E2E] group-data-[transparent=true]:text-[#FF6B6E]' : 'hover:text-[#C62E2E] group-data-[transparent=true]:hover:text-[#FF6B6E]' }}">
                {{ __('navbar.blog') }}
            </a>



        </nav>

        {{-- RIGHT --}}
        <div class="hidden lg:flex items-center gap-3 shrink-0">

            {{-- LANGUAGE SWITCHER --}}
            <div class="relative">
                <input id="langToggle" type="checkbox" class="peer hidden">

                <label for="langToggle"
                    class="cursor-pointer flex items-center gap-1.5 h-10 px-3.5 rounded-full border border-slate-200
                           text-sm font-semibold text-slate-700 hover:border-slate-300 select-none transition
                           group-data-[transparent=true]:border-white/30 group-data-[transparent=true]:text-white group-data-[transparent=true]:hover:border-white/60">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M3 12h18M12 3c2.5 2.6 3.8 5.6 3.8 9s-1.3 6.4-3.8 9c-2.5-2.6-3.8-5.6-3.8-9S9.5 5.6 12 3z" />
                    </svg>
                    {{ strtoupper(app()->getLocale()) }}

                    <svg class="w-3 h-3 mt-[1px]" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </label>

                <div
                    class="absolute right-0 mt-2 w-44 bg-white border border-slate-200 rounded-lg shadow-md
                            hidden peer-checked:block">

                    <div class="max-h-52 overflow-y-auto py-1">

                        @foreach (LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                            @php
                                $url = LaravelLocalization::getLocalizedURL($localeCode, null, [], true);
                            @endphp

                            <a href="{{ $url }}" hreflang="{{ $localeCode }}"
                                class="block px-4 py-2 text-sm hover:bg-slate-50
                                {{ app()->getLocale() == $localeCode ? 'text-[#C62E2E]' : '' }}">

                                {{ $properties['native'] }}

                            </a>
                        @endforeach

                    </div>

                </div>

            </div>

            {{-- İletişim düğmesi: koyu; iletişim sayfasındayken kırmızı --}}
            <a href="{{ route('contact') }}"
                @class(['group/contact inline-flex h-10 items-center gap-2 rounded-full pl-4 pr-5 text-sm font-semibold transition',
                    'bg-[#C62E2E] text-white hover:bg-[#A82424]' => request()->routeIs('contact'),
                    'bg-slate-900 text-white hover:bg-[#C62E2E] group-data-[transparent=true]:bg-white group-data-[transparent=true]:text-slate-900 group-data-[transparent=true]:hover:bg-[#C62E2E] group-data-[transparent=true]:hover:text-white' => !request()->routeIs('contact')])>
                <svg class="size-4 transition group-hover/contact:-rotate-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/><path d="M8.5 11.5h.01M12 11.5h.01M15.5 11.5h.01"/></svg>
                {{ __('footer.contact') }}
            </a>

        </div>

        {{-- MOBILE --}}
        <button type="button" class="lg:hidden -mr-2 p-2 text-slate-800 group-data-[transparent=true]:text-white" aria-label="Menu"
            @click="menu = true" :aria-expanded="menu">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>

    </div>

    @include('partials.mobile-menu')
</header>
