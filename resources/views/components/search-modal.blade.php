<div x-data="globalSearch()" x-init="init()" @keydown.escape.window="close()">

    {{-- SEARCH BAR --}}
    <div @click="openModal()"
        class="mt-10 mx-auto max-w-xl flex items-center gap-3
        bg-white border border-[#F3D6D1]
        px-6 py-4 rounded-full shadow-sm hover:shadow-md
        cursor-pointer transition">

       <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 text-slate-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="1.5"
            stroke-linecap="round"
            stroke-linejoin="round">

            <circle cx="11" cy="11" r="7"></circle>
            <path d="M20 20l-3.5-3.5"></path>

        </svg>

        <span class="text-slate-500">
            Search cities, countries or activities
        </span>

    </div>


    {{-- MODAL --}}
    <div x-show="open" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4">

        {{-- BACKDROP --}}
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="close()">
        </div>


        {{-- BOX --}}
        <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-xl p-6">


            {{-- INPUT --}}
            <div class="flex items-center gap-3 border border-slate-200 rounded-xl px-4 py-3">

                <svg xmlns="http://www.w3.org/2000/svg"
                    class="w-5 h-5 text-slate-400"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                    stroke-linecap="round"
                    stroke-linejoin="round">

                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="M20 20l-3.5-3.5"></path>

                </svg>

                <input x-ref="searchInput" x-model="query" @input.debounce.400ms="search" type="text"
                    placeholder="Search cities, activities or stories..." class="w-full outline-none text-lg">

            </div>


            {{-- RESULTS --}}
            <div class="mt-4 max-h-[300px] md:max-h-[420px] overflow-y-auto pr-1">

                <template x-if="loading">
                    <div class="text-sm text-slate-400">
                        Searching...
                    </div>
                </template>


                <template x-if="results.length">

                    <div class="divide-y">

                        <template x-for="item in results" :key="item.url">

                            <a :href="item.url"
                                class="flex items-center gap-3 py-3 px-3 rounded-lg hover:bg-[#FFF7F7] transition w-full">

                                <div
                                    class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full bg-[#FFF2F2] text-[#C62E2E]">

                                    {{-- CITY ICON --}}
                                        <template x-if="item.type === 'city'">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                stroke-linejoin="round">

                                                <path d="M3 21h18"/>
                                                <path d="M5 21V7l7-4 7 4v14"/>
                                                <path d="M9 9h.01"/>
                                                <path d="M15 9h.01"/>
                                                <path d="M9 13h.01"/>
                                                <path d="M15 13h.01"/>

                                                </svg>
                                        </template>
                                    {{-- CITY ICON --}}

                                    {{-- ACTIVITY ICON --}}
                                        <template x-if="item.type === 'activity'">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                stroke-linejoin="round">

                                                <path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v3
                                                a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H5
                                                a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4V9z"/>

                                                <path d="M12 7v10"/>

                                            </svg>
                                        </template>
                                    {{-- ACTIVITY ICON --}}

                                    {{-- BLOG ICON --}}
                                        <template x-if="item.type === 'blog'">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4"
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.6"
                                                stroke-linecap="round"
                                                stroke-linejoin="round">

                                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5V4.5A2.5 2.5 0 0 1 6.5 2z"/>
                                                <path d="M8 7h8"/>
                                                <path d="M8 11h8"/>

                                                </svg>
                                        </template>
                                    {{-- BLOG ICON --}}

                                </div>


                                <div class="flex flex-col items-start leading-tight w-full text-left">

                                    <span class="text-sm font-semibold text-slate-800" x-text="item.name">
                                    </span>

                                    <span class="text-xs text-slate-500" x-text="item.country">
                                    </span>

                                </div>

                            </a>

                        </template>

                    </div>

                </template>


                <template x-if="!loading && query.length >= 3 && results.length === 0">

                    <div class="text-sm text-slate-400 py-4">
                        No results found
                    </div>

                </template>


                <template x-if="query.length < 3">

                    <div class="text-xs text-slate-400 py-4">
                        Type at least 3 characters
                    </div>

                </template>

            </div>

        </div>

    </div>

</div>



@once
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

        <script>
            function globalSearch() {

                return {

                    open: false,
                    query: '',
                    results: [],
                    loading: false,

                    init() {},

                    openModal() {

                        this.open = true

                        this.$nextTick(() => {
                            this.$refs.searchInput.focus()
                        })

                    },

                    close() {

                        this.open = false
                        this.query = ''
                        this.results = []
                        this.loading = false

                    },

                    async search() {

                        if (this.query.length < 3) {

                            this.results = []
                            return

                        }

                        this.loading = true

                        try {

                            const res = await fetch(`/search?q=${encodeURIComponent(this.query)}`)
                            const data = await res.json()
                            this.results = data.search

                        } catch (e) {

                            this.results = []

                        }

                        this.loading = false

                    }

                }

            }
        </script>
    @endpush
@endonce
