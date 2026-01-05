<header class="bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">

        {{-- LOGO --}}
        <a href="{{ url('/') }}" class="flex items-center gap-2">
            <span class="font-semibold text-slate-800">
                TripSpoiler
            </span>
        </a>

        {{-- NAV --}}
        <nav class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-700">

            <a href="{{ url('/activities') }}"
                class="{{ request()->is('activities*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                Activities
            </a>

            <a href="{{ url('/museums') }}"
                class="{{ request()->is('museums*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                Museums
            </a>

            <a href="{{ url('/cities') }}"
                class="{{ request()->is('cities*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                Cities
            </a>

            <a href="{{ url('/blog') }}"
                class="{{ request()->is('blog*') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                Blog
            </a>

        </nav>


        {{-- MOBILE --}}
        <button class="md:hidden text-2xl" onclick="document.getElementById('mobileMenu').classList.toggle('hidden')">
            ☰
        </button>
    </div>

    @include('partials.mobile-menu')
</header>
