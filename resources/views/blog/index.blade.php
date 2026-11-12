@extends('layouts.app')

@section('title', __('blog-page.meta_title', ['city' => $city?->name]))
@section('meta_description', __('blog-page.meta_description', ['city' => $city?->name]))

{{--
    Blog: koyu banner (başlık, şehir seçimi, o şehrin aktiviteleri / rehberi düğmeleri; öne çıkan yazı + yanında iki yazı),
    altında etiket + arama araç çubuğu ve tüm yazılar; panelde açılan bölümler (Sayfalar › Blog › Bölümler).
    Şehir seçimi aktiviteler ve şehirler sayfasıyla ortak.
--}}
@php
    $date = fn ($blog) => $blog->published_at?->locale(app()->getLocale())->isoFormat('D MMM YYYY');
    $norm = fn ($text) => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii((string) $text));
    // "İstanbul rehberleri": şehir adı kırmızı, çeviriye göre yeri değişir
    [$titleBefore, $titleAfter] = array_pad(explode(':city', __('blog-page.title_city')), 2, '');
    // Hero yazıları: panel (Sayfalar › Blog › Hero ayarları)
    $hero = \App\Support\PageHero::texts('blog', [
        'eyebrow' => __('blog-page.eyebrow'),
        'title' => trim(str_replace(':city', '', __('blog-page.title_city'))),
        'description' => __('blog-page.desc'),
    ], $city?->name);
@endphp

@section('content')
    {{-- BANNER: mimari düzen — ince ızgara çizgileri, solda tipografi, sağda kemer biçimli kapaklar (sütun dizisi) --}}
    @php
        // Kemerler: panelde seçilenler (Sayfalar › Blog › Hero ayarları: "orta,sol,sağ"), boş yerler sıralamaya göre dolar
        $withCover = $blogs->filter(fn ($blog) => $blog->cover_path)->values();
        $chosen = collect(array_pad(explode(',', (string) ($city ? \App\Models\Setting::many(["blog_hero_{$city->id}"])["blog_hero_{$city->id}"] : '')), 3, ''))
            ->map(fn ($id) => $id !== '' ? $withCover->firstWhere('id', (int) $id) : null);
        $rest = $withCover->reject(fn ($blog) => $chosen->contains(fn ($c) => $c?->is($blog)))->values();
        $j = 0;
        $arches = $chosen->map(fn ($blog) => $blog ?? $rest->get($j++))->filter()->values(); // [orta, sol, sağ]
        $featured = $arches->first() ?? $featured;
        $colonnade = $arches->count() === 3 ? collect([$arches[1], $arches[0], $arches[2]]) : $arches;
    @endphp
    <section x-data="{ picker: false }" :class="picker ? 'z-40' : 'z-20'" class="relative isolate z-20 overflow-x-clip bg-[#0F1420] text-white">
        {{-- mimari çizim ızgarası --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:96px_96px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_80%)]"></div>
            <div class="absolute bottom-0 right-[12%] h-[70%] w-[46rem] rounded-t-full bg-[#C62E2E]/10 blur-[100px]"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pb-0 pt-6 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('blog-page.blog'), 'url' => route('blog.index')],
                ...($city ? [['label' => $city->name, 'url' => route('blog.index', ['city_id' => $city->id])]] : []),
            ]" />

            <div class="mt-12 grid items-end gap-14 lg:mt-16 lg:grid-cols-12 lg:gap-10">
                {{-- SOL: tipografi --}}
                <div class="min-w-0 pb-0 lg:col-span-5 lg:pb-20">
                    <div class="flex items-center gap-4 text-[11px] font-bold uppercase tracking-[0.3em] text-white/45">
                        <span class="text-[#FF6B6E]">{{ $hero['eyebrow'] }}</span>
                        <span class="h-px flex-1 bg-white/15"></span>
                        @if ($blogs->isNotEmpty())<span>{{ str_pad($blogs->count(), 2, '0', STR_PAD_LEFT) }}</span>@endif
                    </div>
                    <h1 class="mt-8 text-[56px] font-extrabold leading-[0.9] tracking-[-0.05em] sm:text-7xl lg:text-[92px]">
                        {{ $city?->name ?? __('blog-page.blog') }}
                        <span class="mt-3 block text-2xl font-medium tracking-[-0.01em] text-white/55 sm:text-3xl">{{ $city ? $hero['title'] : '' }}</span>
                    </h1>
                    <p class="mt-8 max-w-md border-l border-[#FF6B6E]/60 pl-5 text-[15px] leading-relaxed text-white/65 rtl:border-l-0 rtl:border-r rtl:pl-0 rtl:pr-5">{{ $hero['description'] }}</p>

                    @if ($city && ($cities->count() > 1 || $hasActivities || $hasGuide))
                        {{-- şehir sayfasıyla aynı blok: seçici tam genişlik, altında eşit iki düğme --}}
                        <div class="mt-10 w-full max-w-lg space-y-3">
                            @if ($cities->count() > 1)
                                @include('partials.city-picker', [
                                    'cities' => $cities, 'current' => $city, 'label' => __('blog-page.choose_city'),
                                    'url' => fn ($c) => route('blog.index', ['city_id' => $c->id]),
                                    'meta' => fn ($c) => trim(($c->country?->name ?? '') . ' · ' . __('blog-page.posts_count', ['count' => $c->blogs_count])),
                                ])
                            @endif
                            @if ($hasActivities || $hasGuide)
                                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                    @if ($hasActivities)
                                        <a href="{{ route('activities.index', ['city' => $city->slug]) }}"
                                            @class(['group inline-flex min-h-[52px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-[#C62E2E] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#A82424]', 'sm:col-span-2 lg:col-span-1 xl:col-span-2' => !$hasGuide])>
                                            {{ __('blog-page.city_activities', ['city' => $city->name]) }}
                                            <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                        </a>
                                    @endif
                                    @if ($hasGuide)
                                        <a href="{{ route('cities.show', $city->slug) }}"
                                            @class(['group inline-flex min-h-[52px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-white/10 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/20 backdrop-blur-md transition hover:bg-white hover:text-slate-900', 'sm:col-span-2 lg:col-span-1 xl:col-span-2' => !$hasActivities])>
                                            {{ __('blog-page.city_guide') }}
                                            <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- SAĞ: kemerler --}}
                @if ($colonnade->isNotEmpty())
                    <div class="lg:col-span-7">
                        <div class="grid grid-cols-3 items-end gap-3 sm:gap-5">
                            @foreach ($colonnade as $blog)
                                @php
                                    $main = $blog->is($featured);
                                @endphp
                                <a href="{{ route('blog.show', $blog->slug) }}" class="group block">
                                    <div @class(['relative overflow-hidden rounded-t-full bg-slate-800 ring-1 ring-white/10 transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-2',
                                        'aspect-[3/5]' => $main, 'aspect-[3/4]' => !$main])>
                                        <img src="{{ $main ? $blog->coverUrl() : $blog->coverUrl('sm') }}" alt="{{ $blog->title }}" @if ($main) fetchpriority="high" @else loading="lazy" @endif
                                            class="absolute inset-0 h-full w-full object-cover transition duration-[1.2s] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                        <div class="absolute inset-0 bg-gradient-to-t from-[#0F1420]/70 via-transparent to-transparent"></div>
                                        @if ($main)
                                            <span class="absolute inset-x-0 bottom-4 mx-auto w-max rounded-full bg-[#C62E2E] px-3 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-white">{{ __('blog-page.featured') }}</span>
                                        @endif
                                    </div>
                                    {{-- kemer altı: ölçü çizgisi + numara + başlık --}}
                                    <div class="border-t border-white/15 pt-4">
                                        <p class="text-[11px] font-bold tracking-[0.2em] text-white/40">{{ str_pad($arches->search(fn ($b) => $b->is($blog)) + 1, 2, '0', STR_PAD_LEFT) }}</p>
                                        <p @class(['mt-1.5 line-clamp-3 font-bold leading-snug transition group-hover:text-[#FF8A8C]', 'text-[15px] sm:text-base' => $main, 'text-xs sm:text-sm text-white/80' => !$main])>{{ $blog->title }}</p>
                                        <p class="mt-1.5 hidden text-[11px] text-white/40 sm:block">{{ __('blog-page.min_read', ['count' => $blog->readingMinutes()]) }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="h-14 md:h-20"></div>
    </section>

    {{-- TÜM YAZILAR: araç çubuğu (etiketler + arama) ve kartlar --}}
    <section id="all-guides" class="scroll-mt-24 bg-slate-50 py-12 md:py-16" x-init="$nextTick(() => reveal())"
        @blog-tag.window="pick($event.detail, [...$el.querySelectorAll('button')].find(b => b.textContent.trim() === $event.detail)); $el.scrollIntoView({ behavior: 'smooth' })"
        x-data="{
            tab: '', tag: '', query: '', q: '', loading: false, timer: null, queue: [],
            // Filtre değişince kısa bir yükleniyor (iskelet kartlar), sonra kartlar sırayla belirir
            apply(fn, ms = 420) {
                this.loading = true;
                this.queue.push(fn);
                clearTimeout(this.timer);
                this.timer = setTimeout(() => { this.queue.splice(0).forEach(f => f()); this.loading = false; this.$nextTick(() => this.reveal()) }, ms);
            },
            pick(t, el) {
                // seçilen etiket şeritte görünür yere kayar (kesik kalmasın)
                el?.scrollIntoView({ inline: 'nearest', block: 'nearest', behavior: 'smooth' });
                if (t === this.tab) return; this.tab = t; this.apply(() => this.tag = t);
            },
            reveal() {
                [...this.$refs.grid.children].filter(el => this.match(el)).forEach((el, i) => {
                    el.classList.remove('card-in'); void el.offsetWidth;
                    el.style.setProperty('--d', Math.min(i, 11) * 45 + 'ms');
                    el.classList.add('card-in');
                });
            },
            norm(t) { return String(t).toLocaleLowerCase('tr').replace(/ı/g, 'i').normalize('NFD').replace(/[̀-ͯ]/g, '') },
            match(el) {
                const words = this.norm(this.q).split(/\s+/).filter(Boolean);
                return (!this.tag || el.dataset.tags.split('|').includes(this.tag)) && words.every(w => el.dataset.search.includes(w));
            },
            get shown() { return [...this.$refs.grid.children].filter(el => this.match(el)).length },
        }">
        <div class="mx-auto max-w-7xl px-4">
            @if ($blogs->isEmpty())
                <div class="mx-auto max-w-2xl rounded-[28px] border-2 border-dashed border-slate-200 bg-white p-10 text-center">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-500">{!! \App\Support\FeatureIcons::svg('book', 'size-7') !!}</span>
                    <p class="mt-5 text-xl font-extrabold text-slate-900">{{ __('blog-page.empty_title', ['city' => $city?->name]) }}</p>
                    <p class="mx-auto mt-2 max-w-md text-slate-500">{{ __('blog-page.empty_text', ['city' => $city?->name]) }}</p>
                </div>
            @else
                <div class="flex items-end justify-between gap-4">
                    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 md:text-3xl">{{ __('blog-page.latest') }}</h2>
                    <p class="flex h-5 items-center text-sm font-medium text-slate-500">
                        <span x-show="loading" x-cloak class="inline-flex gap-1" aria-hidden="true">
                            <span class="acts-dot size-1.5 rounded-full bg-[#C62E2E]"></span><span class="acts-dot size-1.5 rounded-full bg-[#C62E2E] [animation-delay:.15s]"></span><span class="acts-dot size-1.5 rounded-full bg-[#C62E2E] [animation-delay:.3s]"></span>
                        </span>
                        <span x-show="!loading" x-text="@js(__('blog-page.results')).replace(':count', shown)">{{ __('blog-page.results', ['count' => $blogs->count()]) }}</span>
                    </p>
                </div>

                {{-- araç çubuğu: aktiviteler sayfasındakiyle aynı dil --}}
                <div class="relative mt-6 flex flex-col gap-2 overflow-hidden rounded-[22px] bg-white p-2 shadow-[0_18px_44px_-28px_rgba(15,23,42,0.35)] ring-1 ring-slate-200/80 lg:flex-row lg:items-center">
                    @if ($tags->isNotEmpty())
                        <div class="-mx-2 flex min-w-0 gap-1 overflow-x-auto px-2 [scrollbar-width:none] lg:mx-0 lg:max-w-[62%] lg:px-0 [&::-webkit-scrollbar]:hidden">
                            <button type="button" @click="pick('', $el)"
                                :class="tab === '' ? 'bg-[#C62E2E] text-white shadow-[0_10px_24px_-12px_rgba(198,46,46,0.7)]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                class="inline-flex shrink-0 items-center gap-2 rounded-2xl px-4 py-3 text-sm font-semibold transition">
                                {!! \App\Support\FeatureIcons::svg('book', 'size-[18px]') !!}{{ __('blog-page.all') }}
                            </button>
                            @foreach ($tags as $tagName)
                                <button type="button" @click="pick(tab === @js($tagName) ? '' : @js($tagName), $el)"
                                    :class="tab === @js($tagName) ? 'bg-[#C62E2E] text-white shadow-[0_10px_24px_-12px_rgba(198,46,46,0.7)]' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="inline-flex shrink-0 items-center rounded-2xl px-4 py-3 text-sm font-semibold transition">{{ $tagName }}</button>
                            @endforeach
                        </div>
                        <span class="hidden h-8 w-px shrink-0 bg-slate-200 lg:block"></span>
                    @endif
                    <label class="relative flex min-w-0 flex-1 items-center gap-3 rounded-2xl px-3 py-1.5 transition focus-within:bg-slate-50">
                        <span class="grid size-9 shrink-0 place-items-center rounded-xl bg-slate-900 text-white">
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6"/><path d="m19.5 19.5-4.2-4.2"/></svg>
                        </span>
                        <input type="search" x-model="query" @input.debounce.250ms="apply(() => q = query, 300)" placeholder="{{ __('blog-page.search') }}"
                            class="min-w-0 flex-1 bg-transparent py-2 text-[15px] font-medium text-slate-900 outline-none placeholder:font-normal placeholder:text-slate-400 [&::-webkit-search-cancel-button]:hidden">
                        <button type="button" x-show="query" x-cloak @click="query = ''; apply(() => q = '', 300)" aria-label="✕" class="grid size-8 shrink-0 place-items-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </label>
                    {{-- yükleniyor çizgisi --}}
                    <span x-show="loading" x-cloak class="acts-progress pointer-events-none absolute inset-x-0 bottom-0 h-[3px]"></span>
                </div>

                {{-- kartlar --}}
                {{-- İSKELET KARTLAR (filtre değişirken) --}}
                <ul x-show="loading" x-cloak aria-hidden="true" class="mt-8 grid grid-cols-2 gap-x-3 gap-y-7 sm:gap-x-5 sm:gap-y-9 lg:grid-cols-3 xl:grid-cols-4">
                    @for ($i = 0; $i < min(8, max(4, $blogs->count())); $i++)
                        <li>
                            <div class="acts-skeleton aspect-[4/3] rounded-[22px]"></div>
                            <div class="acts-skeleton mt-3.5 h-3 w-20 rounded"></div>
                            <div class="acts-skeleton mt-2 h-4 w-11/12 rounded"></div>
                            <div class="acts-skeleton mt-1.5 h-4 w-2/3 rounded"></div>
                        </li>
                    @endfor
                </ul>

                <ul x-ref="grid" x-show="!loading" class="mt-8 grid grid-cols-2 gap-x-3 gap-y-7 sm:gap-x-5 sm:gap-y-9 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($blogs as $blog)
                        <li x-show="match($el)" data-tags="{{ implode('|', (array) $blog->tags) }}"
                            data-search="{{ $norm($blog->title . ' ' . $blog->excerpt . ' ' . implode(' ', (array) $blog->tags)) }}" x-init="$el.dataset.search = norm($el.dataset.search)">
                            <a href="{{ route('blog.show', $blog->slug) }}" class="group block">
                                <div class="relative aspect-[4/3] overflow-hidden rounded-2xl bg-slate-200 sm:rounded-[22px] ring-1 ring-slate-900/5">
                                    @if ($blog->cover_path)
                                        <img src="{{ $blog->coverUrl('sm') }}" alt="{{ $blog->title }}" loading="lazy" decoding="async"
                                            class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent opacity-80 transition group-hover:opacity-100"></div>
                                    @if ($first = ((array) $blog->tags)[0] ?? null)
                                        <span class="absolute left-2 top-2 max-w-[85%] truncate rounded-full bg-white/90 px-2 py-0.5 text-[10px] sm:left-3 sm:top-3 sm:px-2.5 sm:py-1 sm:text-[11px] font-bold text-slate-900 backdrop-blur rtl:left-auto rtl:right-3">{{ $first }}</span>
                                    @endif
                                    <span class="absolute bottom-2 left-2 inline-flex items-center gap-1 text-[10px] sm:bottom-3 sm:left-3 sm:text-[11px] font-semibold text-white/90 rtl:left-auto rtl:right-3">
                                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                                        {{ __('blog-page.min_read', ['count' => $blog->readingMinutes()]) }}
                                    </span>
                                    <span class="absolute bottom-3 right-3 grid size-9 translate-y-2 place-items-center rounded-full bg-white text-slate-900 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 rtl:left-3 rtl:right-auto">
                                        <svg class="size-4 -rotate-45 rtl:rotate-[225deg]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </span>
                                </div>
                                <p class="mt-3.5 text-xs font-medium text-slate-400">{{ $date($blog) }}</p>
                                <h3 class="mt-1 line-clamp-3 text-sm font-bold sm:line-clamp-2 sm:text-[15px] leading-snug text-slate-900 transition group-hover:text-[#C62E2E]">{{ $blog->title }}</h3>
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div x-show="!loading && shown === 0" x-cloak class="mx-auto mt-6 max-w-md rounded-3xl bg-white p-8 text-center ring-1 ring-slate-200">
                    <p class="font-bold text-slate-900">{{ __('blog-page.no_results') }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ __('blog-page.no_results_hint') }}</p>
                </div>
            @endif
        </div>
    </section>

    {{-- yapısal veri: blog yazıları listesi (seçili şehrin yazıları) --}}
    @jsonld(\App\Support\Schema::webPage('CollectionPage', __('blog-page.meta_title', ['city' => $city?->name]), __('blog-page.meta_description', ['city' => $city?->name]), [
        'about' => $city ? ['@type' => 'City', 'name' => $city->name, 'url' => route('cities.show', ['slug' => $city->slug])] : null,
        'mainEntity' => collect($blogs)->isNotEmpty() ? \App\Support\Schema::itemList(collect($blogs instanceof \Illuminate\Contracts\Pagination\Paginator ? $blogs->items() : $blogs)->take(50)->map(fn ($b) => [$b->title, route('blog.show', ['slug' => $b->slug])])) : null,
    ]))

    {{-- Alt bölümler: panelde Sayfalar › Blog › Bölümler --}}
    <x-page-sections page="blog" :city-id="$cityId" />
@endsection
