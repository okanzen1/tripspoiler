@props([
    'full' => false, // Activities gibi sayfalarda genişletmek için
    'hero' => false, // Anasayfa hero: solda ikon, sağda kırmızı buton
])

<div x-data="globalSearch()" x-init="init()" @keydown.escape.window="close()">

    {{-- SEARCH BAR (HERO) --}}
    @if ($hero)
    <button type="button" @click="openModal()"
        class="group w-full flex items-center gap-3 rounded-2xl bg-white p-2 pl-5
               border border-slate-200 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_32px_-12px_rgba(15,23,42,0.18)]
               hover:border-slate-300 transition cursor-pointer text-left">
        <svg class="w-5 h-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="7"></circle>
            <path d="M20 20l-3.5-3.5"></path>
        </svg>
        <span class="flex-1 truncate text-[15px] text-slate-500">{{ __('search-modal.placeholder_bar') }}</span>
        <span class="shrink-0 rounded-xl bg-[#C62E2E] px-5 py-3 text-sm font-semibold text-white
                     group-hover:bg-[#A82424] transition">
            {{ __('home-hero.search_button') }}
        </span>
    </button>
    @else
    {{-- SEARCH BAR --}}
    <div @click="openModal()"
        class="mt-10 relative
        bg-white border border-[#F3D6D1]
        px-6 py-4 rounded-full shadow-sm hover:shadow-md
        cursor-pointer transition
        {{ $full ? 'w-full' : 'mx-auto max-w-xl flex items-center gap-3' }}">

        {{-- NORMAL (full değilken): solda svg + solda text --}}
        @unless ($full)
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"></circle>
                <path d="M20 20l-3.5-3.5"></path>
            </svg>

            <span class="text-slate-500">
                {{ __('search-modal.placeholder_bar') }}
            </span>
        @endunless

        {{-- FULL (Activities): ortada svg + text birlikte --}}
        @if ($full)
            <div class="flex items-center justify-center gap-3 w-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-400" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="M20 20l-3.5-3.5"></path>
                </svg>

                <span class="text-slate-500">
                    {{ __('search-modal.placeholder_bar') }}
                </span>
            </div>
        @endif

    </div>
    @endif


    {{-- MODAL (body'ye taşınır; animasyonlu/üst üste binen kutuların içinde kalmasın) --}}
    <template x-teleport="body">
    <div x-show="open" x-cloak x-transition.opacity.duration.200ms
        class="fixed inset-0 z-[100] flex flex-col items-center px-3 py-[6vh] md:py-[10vh]"
        role="dialog" aria-modal="true" aria-label="{{ __('search-modal.placeholder_input') }}">

        {{-- BACKDROP --}}
        <div class="absolute inset-0 bg-slate-950/60 backdrop-blur-md" @click="close()"></div>

        {{-- Boşluk: boşken panel ortada; yazınca küçülür ve panel yumuşakça yukarı kayar (flex-grow geçişi) --}}
        <div aria-hidden="true" class="pointer-events-none shrink-0 transition-[flex-grow] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
            :style="{ flexGrow: expanded() ? 0 : 1 }"></div>

        {{-- PANEL --}}
        <div x-show="open"
            x-transition:enter="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
            x-transition:enter-start="opacity-0 -translate-y-3 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            class="relative flex max-h-[84vh] w-full max-w-2xl shrink-0 flex-col overflow-hidden rounded-3xl bg-white shadow-[0_40px_120px_-20px_rgba(2,6,23,0.6)] ring-1 ring-black/5">

            {{-- INPUT --}}
            <div class="flex items-center gap-3 border-b border-slate-100 px-5 md:px-6">
                <svg class="h-5 w-5 shrink-0 text-[#C62E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path>
                </svg>
                <input x-ref="searchInput" x-model="query" @input.debounce.250ms="search" type="search" autocomplete="off" spellcheck="false"
                    @keydown.arrow-down.prevent="move(1)" @keydown.arrow-up.prevent="move(-1)" @keydown.enter.prevent="go()"
                    placeholder="{{ __('search-modal.placeholder_input') }}" inputmode="search"
                    class="h-16 w-full bg-transparent text-base md:text-lg font-medium text-slate-900 outline-none placeholder:font-normal placeholder:text-slate-400 [&::-webkit-search-cancel-button]:hidden">
                <button type="button" x-show="query.length" @click="query = ''; results = []; $refs.searchInput.focus()"
                    class="shrink-0 rounded-full p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700" aria-label="{{ __('search-modal.clear') }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>
                <button type="button" @click="close()" class="hidden md:inline-flex shrink-0 items-center rounded-md border border-slate-200 px-1.5 py-0.5 text-[11px] font-semibold text-slate-500 hover:bg-slate-50">esc</button>
                <button type="button" @click="close()" class="md:hidden shrink-0 text-sm font-semibold text-slate-500">{{ __('search-modal.cancel') }}</button>
            </div>

            {{-- BODY --}}
            <div class="overflow-y-auto overscroll-contain px-3 py-3 md:px-4" x-ref="list">

                {{-- LOADING: iskelet satırlar --}}
                <template x-if="loading && !results.length">
                    <div class="space-y-1 p-1">
                        <template x-for="n in 4" :key="n">
                            <div class="flex items-center gap-3 rounded-2xl p-2.5">
                                <div class="h-12 w-12 shrink-0 animate-pulse rounded-xl bg-slate-100"></div>
                                <div class="flex-1 space-y-2">
                                    <div class="h-3 w-3/4 animate-pulse rounded bg-slate-100"></div>
                                    <div class="h-2.5 w-1/3 animate-pulse rounded bg-slate-100"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- RESULTS (gruplu) --}}
                <template x-if="results.length">
                    <div class="space-y-3" x-transition:enter="transition duration-500 ease-out delay-100"
                        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-show="true">
                        <template x-for="group in groups()" :key="group.type">
                            <div>
                                <p class="px-3 pb-1 pt-2 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400" x-text="labels[group.type]"></p>
                                <template x-for="item in group.items" :key="item.url">
                                    <a :href="item.url" @mouseenter="active = item.index" :data-index="item.index"
                                        :class="active === item.index ? 'bg-slate-50 ring-1 ring-slate-200' : ''"
                                        class="group flex items-center gap-3.5 rounded-2xl p-2.5 transition">
                                        <div class="relative h-12 w-12 shrink-0 overflow-hidden rounded-xl bg-[#C62E2E]/10 text-[#C62E2E]">
                                            <template x-if="item.image">
                                                <img :src="item.image" alt="" loading="lazy" class="h-full w-full object-cover">
                                            </template>
                                            <template x-if="!item.image">
                                                <span class="absolute inset-0 grid place-items-center" x-html="icons[item.type]"></span>
                                            </template>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[15px] font-semibold text-slate-900 [&_mark]:bg-transparent [&_mark]:text-[#C62E2E]" x-html="highlight(item.name)"></p>
                                            <p class="mt-0.5 truncate text-[13px] text-slate-500" x-text="item.subtitle"></p>
                                        </div>
                                        <svg :class="active === item.index ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-1'"
                                            class="h-4 w-4 shrink-0 text-slate-400 transition duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                    </a>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- NO RESULTS --}}
                <template x-if="!loading && query.trim().length >= 3 && results.length === 0">
                    <div class="px-3 py-8 text-center">
                        <div class="mx-auto grid h-12 w-12 place-items-center rounded-2xl bg-slate-100 text-slate-400">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="M20 20l-3.5-3.5M8.5 11h5" /></svg>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-900">{{ __('search-modal.no_results') }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ __('search-modal.no_results_hint') }}</p>
                    </div>
                </template>

                {{-- EMPTY: öneriler --}}
                <template x-if="query.trim().length < 3 && !loading">
                    <div class="space-y-4 p-1">
                        @if (count($suggestions['cities']))
                            <div>
                                <p class="px-2 pb-2 pt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ __('search-modal.group_cities') }}</p>
                                <div class="flex flex-wrap gap-2 px-2">
                                    @foreach ($suggestions['cities'] as $city)
                                        <a href="{{ $city['url'] }}" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3.5 py-1.5 text-sm font-medium text-slate-700 transition hover:border-[#C62E2E] hover:text-[#C62E2E]">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z" /><circle cx="12" cy="9.5" r="2.5" /></svg>
                                            {{ $city['name'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        @if (count($suggestions['activities']))
                            <div>
                                <p class="px-2 pb-1 pt-1 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ __('search-modal.popular') }}</p>
                                @foreach ($suggestions['activities'] as $activity)
                                    <a href="{{ $activity['url'] }}" class="group flex items-center gap-3.5 rounded-2xl p-2.5 transition hover:bg-slate-50">
                                        <img src="{{ $activity['image'] }}" alt="" loading="lazy" class="h-12 w-12 shrink-0 rounded-xl bg-slate-100 object-cover">
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-[15px] font-semibold text-slate-900">{{ $activity['name'] }}</p>
                                            <p class="mt-0.5 truncate text-[13px] text-slate-500">{{ $activity['subtitle'] }}</p>
                                        </div>
                                        <svg class="h-4 w-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <p class="px-2 pb-1 text-xs text-slate-400">{{ __('search-modal.min_chars') }}</p>
                    </div>
                </template>
            </div>

            {{-- FOOTER: klavye ipuçları --}}
            <div class="hidden md:flex items-center gap-4 border-t border-slate-100 bg-slate-50/70 px-6 py-2.5 text-[11px] text-slate-500">
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-slate-200 bg-white px-1 font-sans">↑</kbd><kbd class="rounded border border-slate-200 bg-white px-1 font-sans">↓</kbd> {{ __('search-modal.hint_navigate') }}</span>
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-slate-200 bg-white px-1 font-sans">↵</kbd> {{ __('search-modal.hint_open') }}</span>
                <span class="inline-flex items-center gap-1"><kbd class="rounded border border-slate-200 bg-white px-1 font-sans">esc</kbd> {{ __('search-modal.hint_close') }}</span>
            </div>
        </div>

        <div aria-hidden="true" class="pointer-events-none grow"></div>
    </div>
    </template>

</div>


@once
    @push('scripts')
        <script>
            function globalSearch() {

                return {

                    open: false,
                    query: '',
                    results: [],
                    loading: false,
                    ticket: 0,
                    active: 0,

                    labels: {
                        city: @json(__('search-modal.group_cities')),
                        activity: @json(__('search-modal.group_activities')),
                        blog: @json(__('search-modal.group_guides')),
                    },

                    icons: {
                        city: '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 10h.01M15 10h.01M9 14h.01M15 14h.01"/></svg>',
                        activity: '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/></svg>',
                        blog: '<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M8 7h6M8 11h4"/></svg>',
                    },

                    init() {},

                    openModal() {
                        this.open = true
                        document.body.style.overflow = 'hidden'
                        document.documentElement.classList.add('overlay-open')
                        this.$nextTick(() => setTimeout(() => this.$refs.searchInput.focus(), 120))
                    },

                    close() {
                        if (!this.open) return
                        this.open = false
                        this.query = ''
                        this.results = []
                        this.loading = false
                        this.active = 0
                        document.body.style.overflow = ''
                        document.documentElement.classList.remove('overlay-open')
                    },

                    // Sonuçlar tür sırasına göre gruplanır; klavye için her satıra sıra numarası verilir
                    groups() {
                        let index = 0
                        return ['city', 'activity', 'blog']
                            .map(type => ({ type, items: this.results.filter(r => r.type === type) }))
                            .filter(group => group.items.length)
                            .map(group => ({ ...group, items: group.items.map(item => ({ ...item, index: index++ })) }))
                    },

                    // 3 harften sonra sonuç alanı açılır; panel ortadan yukarı kayar
                    expanded() {
                        return this.query.trim().length >= 3
                    },

                    ordered() {
                        return this.groups().flatMap(group => group.items)
                    },

                    move(step) {
                        const count = this.results.length
                        if (!count) return
                        this.active = (this.active + step + count) % count
                        this.$nextTick(() => this.$refs.list.querySelector(`[data-index="${this.active}"]`)?.scrollIntoView({ block: 'nearest' }))
                    },

                    go() {
                        const item = this.ordered()[this.active]
                        if (item) window.location.href = item.url
                    },

                    // Aranan kelimeleri vurgular (metin önce kaçışlanır)
                    highlight(text) {
                        const safe = String(text).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c])
                        // Aramayla aynı mantık: "topkapi" → "Topkapı", "bogaz" → "Boğaz" da vurgulanır
                        const variants = { i: 'iıİI', ı: 'iıİI', g: 'gğĞ', ğ: 'gğĞ', s: 'sşŞ', ş: 'sşŞ', c: 'cçÇ', ç: 'cçÇ', o: 'oöÖ', ö: 'oöÖ', u: 'uüÜ', ü: 'uüÜ' }
                        const words = this.query.trim().toLocaleLowerCase('tr').split(/\s+/).filter(w => w.length >= 2)
                            .map(w => [...w].map(ch => variants[ch] ? `[${variants[ch]}]` : ch.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join(''))
                        return words.length ? safe.replace(new RegExp(`(${words.join('|')})`, 'giu'), '<mark>$1</mark>') : safe
                    },

                    async search() {

                        const query = this.query.trim()

                        if (query.length < 3) {
                            this.results = []
                            this.loading = false
                            return
                        }

                        // Hızlı yazınca eski yanıt yenisinin üstüne yazmasın
                        const ticket = ++this.ticket
                        this.loading = true

                        try {
                            const res = await fetch(`{{ route('search') }}?q=${encodeURIComponent(query)}`, {
                                headers: { 'Accept': 'application/json' }
                            })
                            const data = res.ok ? await res.json() : { search: [] }

                            if (ticket === this.ticket) {
                                this.results = data.search ?? []
                                this.active = 0
                            }
                        } catch (e) {
                            if (ticket === this.ticket) this.results = []
                        } finally {
                            if (ticket === this.ticket) this.loading = false
                        }
                    }
                }
            }
        </script>
    @endpush
@endonce
