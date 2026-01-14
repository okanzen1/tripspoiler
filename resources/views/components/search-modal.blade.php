<div
    data-search-modal
    class="fixed inset-0 z-50 hidden items-center justify-center
           bg-black/40 backdrop-blur-sm
           opacity-0 transition-opacity duration-200
           px-4 sm:px-6"
>
    <div
        data-search-panel
        class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl
               transform transition-all duration-200
               scale-95 translate-y-4 opacity-0 overflow-hidden"
    >

        {{-- HEADER --}}
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    Search
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Activities, Cities, museums, & blogs
                </p>
            </div>

            <button
                type="button"
                data-search-close
                class="text-slate-400 hover:text-slate-900
                       transition text-2xl leading-none"
            >
                &times;
            </button>
        </div>

        {{-- INPUT --}}
        <div class="px-6 py-5">
            <div class="relative">
                <input
                    type="text"
                    data-search-input
                    placeholder="Minimal 3 characters to start searching..."
                    class="w-full rounded-2xl border border-slate-300
                           px-4 py-3 pl-11 text-sm
                           focus:outline-none focus:ring-2 focus:ring-[#C62E2E]"
                >

                {{-- SEARCH ICON --}}
                <svg
                    class="absolute left-4 top-1/2 -translate-y-1/2
                           h-4 w-4 text-slate-400"
                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>

        {{-- RESULTS --}}
        <div
            data-search-results
            class="hidden border-t max-h-80 overflow-y-auto"
        ></div>

        {{-- STATUS / EMPTY --}}
        <div
            data-search-status
            class="px-6 py-4 text-sm text-slate-500"
        >
            Start typing to see results.
        </div>

    </div>
</div>
