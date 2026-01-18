<div id="mobileMenu" class="hidden md:hidden border-t bg-white">
    <nav class="flex flex-col px-4 py-3 text-sm">

        <a href="{{ url('/') }}"
            class="py-2 {{ request()->is('/') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Home
        </a>

        <a href="{{ url('/activities') }}"
            class="py-2 {{ request()->is('activities*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Activities
        </a>

        {{-- <a href="{{ url('/museums') }}"
            class="py-2 {{ request()->is('museums*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Museums
        </a> --}}

        <a href="{{ url('/cities') }}" class="py-2 {{ request()->is('cities*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Cities
        </a>

        <a href="{{ url('/blog') }}" class="py-2 {{ request()->is('blog*') ? 'text-[#C62E2E]' : 'text-slate-700' }}">
            Blog
        </a>

    </nav>
</div>
