<div id="mobileMenu" class="hidden md:hidden border-t bg-white">
    <nav class="flex flex-col px-4 py-3 text-sm">

        <a href="{{ route('home') }}"
            class="py-2 {{ request()->routeIs('home') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Home
        </a>

        <a href="{{ route('activities.index') }}"
            class="py-2 {{ request()->routeIs('activities.*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Activities
        </a>

        <a href="{{ route('cities.index') }}"
            class="py-2 {{ request()->routeIs('cities.*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Cities
        </a>

        <a href="{{ route('blog.index') }}"
            class="py-2 {{ request()->routeIs('blog.*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Blog
        </a>

        {{-- LANGUAGE --}}
        <div class="mt-4 pt-4 border-t border-slate-200">

            <p class="text-xs font-semibold text-slate-400 uppercase mb-2">
                Language
            </p>

            <div class="flex flex-wrap gap-2">

                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)

                    <a href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                       class="px-3 py-1.5 rounded-full text-xs border
                       {{ app()->getLocale() == $localeCode 
                            ? 'bg-[#C62E2E] text-white border-[#C62E2E]' 
                            : 'border-slate-200 text-slate-600 hover:border-[#C62E2E] hover:text-[#C62E2E]' }}">

                        {{ $properties['native'] }}

                    </a>

                @endforeach

            </div>

        </div>

    </nav>
</div>