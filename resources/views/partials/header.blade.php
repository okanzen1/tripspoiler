<header class="bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">

        {{-- LOGO --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="font-semibold text-slate-800">
                TripSpoiler
            </span>
        </a>

        {{-- NAV --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-700">

            <a href="{{ route('home') }}"
                class="{{ request()->routeIs('home') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                {{ __('navbar.home') }}
            </a>

            <a href="{{ route('activities.index') }}"
                class="{{ request()->routeIs('activities.*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                {{ __('navbar.activities') }}
            </a>

            <a href="{{ route('cities.index') }}"
                class="{{ request()->routeIs('cities.*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                {{ __('navbar.cities') }}
            </a>

            <a href="{{ route('blog.index') }}"
                class="{{ request()->routeIs('blog.*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                {{ __('navbar.blog') }}
            </a>

            {{-- LANGUAGE SWITCHER --}}
            <div class="relative">
                <input id="langToggle" type="checkbox" class="peer hidden">

                <label for="langToggle" class="cursor-pointer flex items-center gap-1 hover:text-[#C62E2E] select-none">
                    {{ LaravelLocalization::getCurrentLocaleNative() }}

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
                                $params = request()->route()?->parameters() ?? [];

                                // sayfada hangi model varsa onu bul
                                $model = $activity ?? ($city ?? ($blog ?? ($post ?? null)));

                                if (isset($params['slug']) && $model) {
                                    $slug = $model->getTranslation('slug', $localeCode);

                                    if (!$slug) {
                                        $slug = $model->getTranslation('slug', 'en'); // her zaman EN fallback
                                    }

                                    $params['slug'] = $slug;
                                }

                                $url = LaravelLocalization::getLocalizedURL(
                                    $localeCode,
                                    route(request()->route()->getName(), $params, false),
                                );
                            @endphp

                            <a href="{{ $url }}"
                                class="block px-4 py-2 text-sm hover:bg-slate-50
                                {{ app()->getLocale() == $localeCode ? 'text-[#C62E2E]' : '' }}">

                                {{ $properties['native'] }}

                            </a>
                        @endforeach

                    </div>

                </div>

            </div>

        </nav>

        {{-- MOBILE --}}
        <button class="md:hidden text-2xl" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
            ☰
        </button>

    </div>

    @include('partials.mobile-menu')
</header>
