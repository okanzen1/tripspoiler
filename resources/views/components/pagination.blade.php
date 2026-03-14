@if ($paginator->hasPages())
    <nav class="flex items-center justify-center gap-2">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-2 text-slate-400">
                ‹
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}"
                class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100">
                ‹
            </a>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="px-3 py-2 text-slate-400">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-4 py-2 rounded-lg bg-[#c62e2e] text-white font-semibold">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}"
                            class="px-4 py-2 rounded-lg border border-slate-200 hover:bg-slate-100">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach


        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}"
                class="px-3 py-2 rounded-lg border border-slate-200 hover:bg-slate-100">
                ›
            </a>
        @else
            <span class="px-3 py-2 text-slate-400">
                ›
            </span>
        @endif

    </nav>
@endif
