@extends('layouts.app')

@section('title', $metaTitle)
@section('meta_description', $metaDescription)
@if ($banner)
    @section('og_image', $banner)
@endif

{{--
    Şehir sayfası: fotoğraflı banner (rehberin genel girişi, şehir seçimi, rakamlar), yapışkan rehber menüsü ve
    panelde sıralanan bölümler (rehber alanları + popüler, blog, yorumlar…: Sayfalar › Şehirler › Bölümler).
    Seçilen şehir aktiviteler sayfasıyla ortak (session "selected_city_id").
--}}
@php
    $flag = fn ($code) => $code ? implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($code)))) : '';
@endphp

@section('content')
    {{-- BANNER: açık zemin; solda şehir ve giriş, sağda fotoğraf kartı + rehberden küçük kapak kartları --}}
    {{-- şehir menüsü açıkken banner yapışkan rehber çubuğunun üstüne çıkar (yoksa menü çubuğun arkasında kalır) --}}
    {{-- fotoğrafın üstündeki iki kart ($floating: [sol alt, sağ üst]) panelden: Şehir rehberi › şehir › Banner'daki kartlar --}}
    <section data-dock-start data-dock-at="0.3" x-data="{ picker: false }" :class="picker ? 'z-40' : 'z-20'"
        class="relative isolate z-20 overflow-x-clip bg-slate-900 text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden" aria-hidden="true">
            <img src="{{ $banner }}" alt="" class="absolute inset-0 h-full w-full scale-125 object-cover opacity-35 blur-3xl">
            <div class="absolute inset-0 bg-gradient-to-br from-slate-900/95 via-slate-900/75 to-slate-800/60"></div>
            <div class="absolute inset-x-0 bottom-0 h-1/3 bg-gradient-to-t from-slate-900 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-20 md:pt-8">
            <div class="flex items-center justify-between gap-4">
                <x-breadcrumbs dark class="min-w-0 flex-1" :items="[
                    ['label' => __('activity-show.home'), 'url' => route('home')],
                    ['label' => __('cities-page.cities'), 'url' => route('cities.index')],
                    ['label' => $city->name, 'url' => url()->current()],
                ]" />

            </div>

            <div class="mt-10 grid items-center gap-12 lg:mt-12 lg:grid-cols-12 lg:gap-14">
                {{-- SOL: şehir ve giriş --}}
                <div class="min-w-0 lg:col-span-5">
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.22em] text-[#FF8A8C]">
                        <span class="h-px w-8 bg-[#FF6B6E]"></span>{{ $eyebrow }}
                    </p>
                    <h1 class="mt-4">
                        <span class="block text-[60px] font-extrabold leading-[0.9] tracking-[-0.05em] text-white sm:text-[84px] lg:text-[104px]">{{ $city->name }}</span>
                        @if ($city->country)
                            <span class="mt-4 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1.5 text-sm font-semibold text-white/85 ring-1 ring-white/15 backdrop-blur">
                                <span class="text-base leading-none" aria-hidden="true">{{ $flag($city->country->code) }}</span>{{ $city->country->name }}
                            </span>
                        @endif
                        <span class="mt-6 block text-xl font-bold leading-snug tracking-[-0.01em] text-white sm:text-2xl">{{ $title }}</span>
                    </h1>
                    <p class="mt-4 text-[15px] leading-relaxed text-white/65 line-clamp-5 md:text-base lg:line-clamp-none">{{ $description }}</p>

                    <div class="mt-8 w-full max-w-lg space-y-3">
                    {{-- ŞEHİR DEĞİŞTİR (birden fazla şehir varsa) --}}
                    @if ($cities->count() > 1)
                        <div x-data="{ open: false }" x-effect="picker = open" @click.outside="open = false" @keydown.escape.window="open = false" class="relative w-full">
                            <button type="button" @click="open = !open" :aria-expanded="open"
                                :class="open ? 'bg-white/[0.16] ring-white/40' : 'ring-white/15 hover:ring-white/30'"
                                class="flex w-full items-center gap-3 rounded-2xl bg-white/[0.08] px-4 py-3 text-left text-white ring-1 backdrop-blur-md transition">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 text-xl leading-none ring-1 ring-white/15" aria-hidden="true">{{ $flag($city->country?->code) ?: '📍' }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-white/50">{{ __('cities-page.choose_city') }}</span>
                                    <span class="block truncate text-sm font-bold">{{ $city->name }}</span>
                                </span>
                                <svg class="size-4 shrink-0 text-white/60 transition duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                            </button>
                            <div x-show="open" x-cloak
                                x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1"
                                class="absolute left-0 top-full z-50 mt-2 w-full min-w-[18rem] origin-top overflow-hidden rounded-3xl bg-white text-slate-900 shadow-[0_30px_70px_-20px_rgba(2,6,23,0.6)] ring-1 ring-slate-200">
                                <p class="border-b border-slate-100 px-5 py-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ __('cities-page.choose_city') }} · {{ __('cities-page.cities_count', ['count' => $cities->count()]) }}</p>
                                <div class="max-h-80 overflow-y-auto p-2">
                                    @foreach ($cities as $c)
                                        <a href="{{ route('cities.show', $c->slug) }}"
                                            @class(['group flex items-center gap-3.5 rounded-2xl p-2.5 transition', 'bg-slate-50 ring-1 ring-slate-200' => $c->is($city), 'hover:bg-slate-50' => !$c->is($city)])>
                                            <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-2xl leading-none">{{ $flag($c->country?->code) ?: '📍' }}</span>
                                            <span class="min-w-0 flex-1">
                                                <span class="block truncate text-[15px] font-bold">{{ $c->name }}</span>
                                                <span class="block truncate text-xs text-slate-500">{{ $c->country?->name }}@if ($c->activities_count) · {{ trans_choice('activities-page.activity_count', $c->activities_count, ['count' => $c->activities_count]) }}@endif</span>
                                            </span>
                                            @if ($c->is($city))
                                                <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#C62E2E] text-white">
                                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                                </span>
                                            @else
                                                <svg class="size-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        @if ($city->activities_count)
                            <a href="{{ route('activities.index', ['city' => $city->slug]) }}"
                                @class(['group inline-flex min-h-[52px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-[#C62E2E] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#A82424]', 'sm:col-span-2 lg:col-span-1 xl:col-span-2' => $guideNav->isEmpty()])>
                                {{ __('cities-page.see_activities', ['city' => $city->name]) }}
                                <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        @endif
                        @if ($guideNav->isNotEmpty())
                            <a href="#{{ $guideNav->first()->topic->key }}"
                                @class(['group inline-flex min-h-[52px] items-center justify-center gap-2 whitespace-nowrap rounded-full bg-white/10 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/20 backdrop-blur-md transition hover:bg-white hover:text-slate-900', 'sm:col-span-2 lg:col-span-1 xl:col-span-2' => !$city->activities_count])>
                                {{ __('cities-page.start_guide') }}
                                <svg class="size-4 transition group-hover:translate-y-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
                            </a>
                        @endif
                    </div>
                    </div>
                </div>

                {{-- SAĞ: fotoğraf kartı --}}
                <div class="relative lg:col-span-7">
                    <div class="relative aspect-[4/3] overflow-hidden rounded-[32px] bg-slate-800 shadow-[0_40px_90px_-40px_rgba(0,0,0,0.8)] ring-1 ring-white/10 lg:aspect-[5/4] lg:rounded-[40px]">
                        <picture>
                            @if ($bannerMobile)<source media="(max-width: 767px)" srcset="{{ $bannerMobile }}">@endif
                            <img src="{{ $banner }}" alt="{{ $city->name }}" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover" style="object-position: {{ $look['focus'] }}% center">
                        </picture>
                        <div @class(['absolute inset-x-0 bottom-0 bg-gradient-to-t to-transparent', 'h-1/3 from-slate-950/25' => $look['overlay'] === 'soft', 'h-1/2 from-slate-950/45' => $look['overlay'] === 'medium', 'h-2/3 from-slate-950/70' => $look['overlay'] === 'strong'])></div>
                    </div>

                    {{-- rehberden yüzen kartlar --}}
                    @foreach ($floating as $section)
                        @continue(!$section)
                        <a href="#{{ $section->topic->key }}"
                            @class(['group absolute flex w-56 items-center gap-3 rounded-2xl bg-white/95 p-2 pr-4 shadow-[0_24px_50px_-20px_rgba(15,23,42,0.45)] ring-1 ring-slate-200/70 backdrop-blur transition hover:-translate-y-1 sm:w-64',
                                '-bottom-6 left-4 sm:left-6 lg:-left-10' => $loop->first,
                                'right-4 top-6 hidden sm:flex lg:-right-6' => !$loop->first])>
                            @if ($section->image_path)
                                <img src="{{ $section->imageUrl('sm') }}" alt="" loading="lazy" class="size-14 shrink-0 rounded-xl object-cover">
                            @else
                                <span class="grid size-14 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] text-white">{!! \App\Support\FeatureIcons::svg($section->topic->icon, 'size-6') !!}</span>
                            @endif
                            <span class="min-w-0">
                                <span class="flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.12em] text-[#C62E2E]">
                                    {!! \App\Support\FeatureIcons::svg($section->topic->icon, 'size-3.5') !!}{{ $section->topic->name }}
                                </span>
                                <span class="mt-0.5 line-clamp-2 block text-sm font-bold leading-snug text-slate-900">{{ $section->title }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- REHBER MENÜSÜ: yapışkan, alt çizgili sekmeler; okunan alan seçili --}}
    @if ($guideNav->isNotEmpty())
        <nav aria-label="{{ __('cities-page.in_this_guide') }}" class="sticky top-[69px] z-30 border-b border-slate-200 bg-white/95 backdrop-blur-xl lg:top-0"
            x-data="{
                active: @js($guideNav->first()->topic->key), atStart: true, atEnd: false,
                init() {
                    const io = new IntersectionObserver((entries) => {
                        entries.forEach((e) => { if (e.isIntersecting) this.active = e.target.dataset.guideSection });
                    }, { rootMargin: '-35% 0px -60% 0px' });
                    document.querySelectorAll('[data-guide-section]').forEach((el) => io.observe(el));
                    this.edges();
                },
                edges() { const t = this.$refs.track; const x = Math.abs(t.scrollLeft); this.atStart = x < 4; this.atEnd = x + t.clientWidth >= t.scrollWidth - 4 },
                go(dir) { const t = this.$refs.track; const rtl = getComputedStyle(t).direction === 'rtl'; t.scrollBy({ left: t.clientWidth * 0.6 * dir * (rtl ? -1 : 1), behavior: 'smooth' }) },
            }"
            x-effect="const chip = $refs['chip-' + active], t = $refs.track; if (chip && t) t.scrollTo({ left: chip.offsetLeft - t.clientWidth / 2 + chip.offsetWidth / 2, behavior: 'smooth' })">
            <div class="relative mx-auto max-w-7xl md:px-4">
                <div x-ref="track" @scroll.throttle.80ms="edges()" class="flex overflow-x-auto px-2 [scrollbar-width:none] md:px-0 [&::-webkit-scrollbar]:hidden">
                    @foreach ($guideNav as $section)
                        <a href="#{{ $section->topic->key }}" x-ref="chip-{{ $section->topic->key }}"
                            :class="active === '{{ $section->topic->key }}' ? 'text-slate-900' : 'text-slate-500 hover:text-slate-900'"
                            class="group relative inline-flex shrink-0 items-center gap-2 whitespace-nowrap px-3.5 py-4 text-sm font-semibold transition md:px-4">
                            <span :class="active === '{{ $section->topic->key }}' ? 'text-[#C62E2E]' : 'text-slate-400 group-hover:text-slate-600'" class="transition">{!! \App\Support\FeatureIcons::svg($section->topic->icon, 'size-[18px]') !!}</span>
                            {{ $section->topic->name }}
                            <span :class="active === '{{ $section->topic->key }}' ? 'scale-x-100' : 'scale-x-0'" class="absolute inset-x-3 bottom-0 h-[3px] origin-center rounded-t-full bg-[#C62E2E] transition duration-300"></span>
                        </a>
                    @endforeach
                </div>
                {{-- kenar okları: kaydırılacak yer varsa --}}
                <button type="button" @click="go(-1)" x-show="!atStart" x-transition.opacity aria-label="‹"
                    class="absolute inset-y-0 left-0 hidden w-20 items-center justify-start bg-gradient-to-r from-white via-white/90 to-transparent pl-4 text-slate-700 md:flex rtl:left-auto rtl:right-0 rtl:rotate-180">
                    <span class="grid size-8 place-items-center rounded-full bg-white shadow-sm ring-1 ring-slate-200"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></span>
                </button>
                <button type="button" @click="go(1)" x-show="!atEnd" x-transition.opacity aria-label="›"
                    class="absolute inset-y-0 right-0 hidden w-20 items-center justify-end bg-gradient-to-l from-white via-white/90 to-transparent pr-4 text-slate-700 md:flex rtl:left-0 rtl:right-auto rtl:rotate-180">
                    <span class="grid size-8 place-items-center rounded-full bg-white shadow-sm ring-1 ring-slate-200"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg></span>
                </button>
            </div>
        </nav>
    @else
        {{-- Rehber henüz yazılmadıysa --}}
        <section class="bg-white py-14">
            <div class="mx-auto max-w-3xl px-4">
                <div class="rounded-[28px] border-2 border-dashed border-slate-200 p-8 text-center md:p-12">
                    <span class="mx-auto grid size-14 place-items-center rounded-2xl bg-slate-100 text-slate-500">{!! \App\Support\FeatureIcons::svg('book', 'size-7') !!}</span>
                    <p class="mt-5 text-xl font-extrabold text-slate-900">{{ __('cities-page.coming_soon_title', ['city' => $city->name]) }}</p>
                    <p class="mx-auto mt-2 max-w-md text-slate-500">{{ __('cities-page.coming_soon_text', ['city' => $city->name]) }}</p>
                    @if ($city->activities_count)
                        <a href="{{ route('activities.index', ['city' => $city->slug]) }}" class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-[#C62E2E] px-5 py-3 text-sm font-bold text-white transition hover:bg-[#A82424]">
                            {{ __('cities-page.see_activities', ['city' => $city->name]) }} →
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- Bölümler: rehber alanları + diğerleri, panelde verilen sırayla --}}
    <x-page-sections page="cities" :city-id="$city->id" :guide-sections="$guideSections" />
@endsection

@push('scripts')
    <script>
        // Rehber bağlantıları (#gezilecek-yerler…: "Rehbere başla", menü, yüzen kartlar) yumuşak kayar;
        // yapışkan header (telefonda) ve rehber menüsü kadar boşluk bırakılır
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href^="#"]');
            const target = link && document.getElementById(decodeURIComponent(link.getAttribute('href').slice(1)));
            if (!target || !target.matches('[data-guide-section]')) return;
            event.preventDefault();
            const header = document.querySelector('header');
            const nav = document.querySelector('nav[aria-label][class*="sticky"]');
            const offset = (header && getComputedStyle(header).position === 'sticky' ? header.offsetHeight : 0) + (nav?.offsetHeight ?? 0);
            const reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
            window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset + 1, behavior: reduce ? 'auto' : 'smooth' });
            history.replaceState(null, '', '#' + target.id);
        });
    </script>
    @jsonld($citySchema)
@endpush
