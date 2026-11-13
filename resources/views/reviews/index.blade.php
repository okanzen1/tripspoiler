@extends('layouts.app')

@section('title', __('reviews-page.meta_title'))
@section('meta_description', __('reviews-page.meta_description'))

{{--
    Yorumlar: açık zeminde başlık + "Yorum yaz", sağda puan kartı (ortalama, yıldız dağılımı; tıklanınca süzer),
    altında yıldız süzgeci ve sütunlu yorum kartları. Yazılar panelden (Sayfalar › Yorumlar › Hero alanı).
--}}
@php
    $hero = \App\Support\PageHero::texts('reviews', [
        'eyebrow' => __('reviews-page.eyebrow'),
        'title' => __('reviews-page.title'),
        'title_highlight' => __('reviews-page.title_highlight'),
        'description' => __('reviews-page.desc'),
    ]);
    // Sayfa içeriği (panel: Sayfalar › Yorumlar › Sayfa içeriği)
    $listText = \App\Models\SectionText::texts('reviews_list', app()->getLocale(), ['title' => __('reviews-page.recent')]);
    $shareText = \App\Models\SectionText::texts('reviews_share', app()->getLocale(), ['title' => __('reviews-page.share_title'), 'description' => __('reviews-page.share_text')]);
    $total = $general['totalReviews'];
    $avg = $general['averageRating'];
    $dist = $general['distribution'];
    $max = max(1, max($dist));
    $palette = ['bg-rose-100 text-rose-700', 'bg-sky-100 text-sky-700', 'bg-amber-100 text-amber-700', 'bg-emerald-100 text-emerald-700', 'bg-violet-100 text-violet-700', 'bg-orange-100 text-orange-700'];
    $initials = fn ($name) => mb_strtoupper(collect(preg_split('/\s+/', trim($name)))->filter()->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode(''));
    $star = '<path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>';
@endphp

@section('content')
    @php
        $look = \App\Support\PageHero::visual('reviews');
        ['desktop' => $photo, 'mobile' => $photoMobile] = \App\Models\PageBanner::urls('reviews', null);
        $overlay = ['soft' => 'from-slate-950/80 via-slate-950/45 to-slate-950/10', 'medium' => 'from-slate-950 via-slate-950/75 to-slate-950/30', 'strong' => 'from-slate-950 via-slate-950/90 to-slate-950/60'][$look['overlay']];
    @endphp

    {{-- BANNER: ortalanmış; puan rozeti, başlık, düğmeler; altta yüzen cam yorum kartları --}}
    @php
        // banner kartları (panel: Hero alanı › Yorum kartları): boş = otomatik, "0" = gösterme, "1,5,3" = seçilenler
        $cardSetting = (string) \App\Models\Setting::many(['reviews_hero_cards'])['reviews_hero_cards'];
        $featuredReviews = match (true) {
            $cardSetting === '0' => collect(),
            $cardSetting !== '' => \App\Models\Review::approved()->whereIn('id', explode(',', $cardSetting))->get()->sortBy(fn ($r) => array_search((string) $r->id, explode(',', $cardSetting)))->values(),
            default => \App\Models\Review::approved()->where('rating', '>=', 4)->latest()->take(3)->get(),
        };
        $openReview = "window.dispatchEvent(new CustomEvent('open-review'))";
    @endphp
    <section data-dock-start data-dock-at="0.3" class="relative isolate overflow-hidden bg-slate-950 text-white">
        <div class="absolute inset-0 -z-10" aria-hidden="true">
            <picture>
                @if ($photoMobile)<source media="(max-width: 767px)" srcset="{{ $photoMobile }}">@endif
                <img src="{{ $photo }}" alt="" fetchpriority="high" class="h-full w-full object-cover" style="object-position: {{ $look['focus'] }}% center">
            </picture>
            <div @class(['absolute inset-0', 'bg-slate-950/45' => $look['overlay'] === 'soft', 'bg-slate-950/65' => $look['overlay'] === 'medium', 'bg-slate-950/80' => $look['overlay'] === 'strong'])></div>
            <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-slate-950 via-slate-950/60 to-transparent"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-20 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('footer.reviews'), 'url' => route('reviews.index')],
            ]" />

            <div class="mx-auto mt-12 max-w-3xl text-center md:mt-16">
                {{-- puan rozeti --}}
                <a href="#yorumlar" data-smooth class="inline-flex items-center gap-3 rounded-full bg-white/10 py-1.5 pl-1.5 pr-4 ring-1 ring-white/20 backdrop-blur-md transition hover:bg-white/15">
                    <span class="rounded-full bg-amber-400 px-2.5 py-1 text-sm font-extrabold text-slate-900">{{ number_format($avg, 1) }}</span>
                    <span class="flex gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg @class(['size-4', 'text-amber-400' => $i <= round($avg), 'text-white/25' => $i > round($avg)]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $star !!}</svg>
                        @endfor
                    </span>
                    <span class="text-sm font-semibold text-white/80">{{ __('reviews-page.based_on', ['count' => $total]) }}</span>
                </a>
                <h1 class="mt-6 text-[44px] font-extrabold leading-[1] tracking-[-0.04em] sm:text-6xl lg:text-[72px]">
                    {{ $hero['title'] }} <span class="text-[#FF6B6E]">{{ $hero['title_highlight'] }}</span>
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-base leading-relaxed text-white/75 md:text-lg">{{ $hero['description'] }}</p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <button type="button" onclick="{{ $openReview }}"
                        class="inline-flex min-h-[52px] items-center gap-2 rounded-full bg-[#C62E2E] px-6 py-3 text-sm font-bold text-white transition hover:bg-[#A82424]">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                        {{ __('reviews-page.write') }}
                    </button>
                    <a href="#yorumlar" data-smooth class="inline-flex min-h-[52px] items-center gap-2 rounded-full bg-white/10 px-6 py-3 text-sm font-bold ring-1 ring-white/25 backdrop-blur-md transition hover:bg-white hover:text-slate-900">
                        {{ __('reviews-page.recent') }}
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
                    </a>
                </div>
                <div class="mt-6 text-center">
                    <x-social-follow />
                </div>
            </div>

            {{-- yüzen cam yorum kartları --}}
            @if ($featuredReviews->isNotEmpty())
                {{-- telefonda parmakla kayan şerit (yanda sonraki kart görünür), masaüstünde üç sütun --}}
                <div x-data="{ i: 0 }" class="mt-12 md:mt-16">
                <div x-ref="strip" @scroll.throttle.80ms="i = Math.round($el.scrollLeft / ($el.firstElementChild.offsetWidth + 12))"
                    class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 [scrollbar-width:none] md:mx-0 md:grid md:grid-cols-3 md:gap-4 md:overflow-visible md:px-0 [&::-webkit-scrollbar]:hidden">
                    @foreach ($featuredReviews as $review)
                        <figure @class(['w-[82%] shrink-0 snap-center rounded-3xl bg-white/10 p-5 ring-1 ring-white/15 backdrop-blur-xl md:w-auto', 'md:translate-y-4' => $loop->index === 1])>
                            <div class="flex gap-0.5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg @class(['size-3.5', 'text-amber-400' => $i <= $review->rating, 'text-white/25' => $i > $review->rating]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $star !!}</svg>
                                @endfor
                            </div>
                            <blockquote class="mt-3 line-clamp-3 text-sm leading-relaxed text-white/85">“{{ $review->comment }}”</blockquote>
                            <figcaption class="mt-4 flex items-center gap-2.5">
                                <span class="grid size-8 place-items-center rounded-full text-xs font-bold {{ $palette[$review->id % count($palette)] }}">{{ $initials($review->name) }}</span>
                                <span class="text-sm font-semibold">{{ $review->display_name }}</span>
                            </figcaption>
                        </figure>
                    @endforeach
                </div>
                @if ($featuredReviews->count() > 1)
                    <div class="mt-4 flex justify-center gap-1.5 md:hidden">
                        @foreach ($featuredReviews as $review)
                            <span class="h-1.5 rounded-full transition-all" :class="i === {{ $loop->index }} ? 'w-6 bg-white' : 'w-1.5 bg-white/35'"></span>
                        @endforeach
                    </div>
                @endif
                </div>
            @endif
        </div>
    </section>

    {{-- Bölümler (panel: Sayfalar › Yorumlar › Bölümler): yorum listesi de sırada --}}
    <x-page-sections page="reviews">
        <x-slot:main>
    {{-- YORUMLAR: üstte özet + süzgeç çubuğu, altında tek tek geniş yorum kartları --}}
    <section id="yorumlar" class="scroll-mt-20 bg-slate-50 py-14 md:py-20">
        <div class="mx-auto max-w-5xl px-4">
            <div class="flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]"><span class="h-px w-6 bg-[#C62E2E]"></span>{{ __('reviews-page.eyebrow') }}</p>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 md:text-4xl">{{ $listText['title'] }}</h2>
                </div>
                <button type="button" onclick="{{ $openReview }}"
                    class="inline-flex min-h-[48px] shrink-0 items-center gap-2 self-start rounded-full bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-[#C62E2E] md:self-auto">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                    {{ __('reviews-page.write') }}
                </button>
            </div>

            {{-- özet + süzgeç --}}
            <div class="mt-8 flex flex-col gap-4 rounded-[24px] bg-white p-3 ring-1 ring-slate-200/80 shadow-[0_18px_44px_-30px_rgba(15,23,42,0.35)] lg:flex-row lg:items-center">
                <div class="flex items-center gap-3 px-3 lg:border-r lg:border-slate-100 lg:pr-6">
                    <span class="text-4xl font-extrabold tabular-nums tracking-tight text-slate-900">{{ number_format($avg, 1) }}</span>
                    <span>
                        <span class="flex gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg @class(['size-4', 'text-amber-400' => $i <= round($avg), 'text-slate-200' => $i > round($avg)]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $star !!}</svg>
                            @endfor
                        </span>
                        <span class="mt-0.5 block text-xs font-medium text-slate-500">{{ __('reviews-page.based_on', ['count' => $total]) }}</span>
                    </span>
                </div>
                {{-- puan süzgeci: taşmadan sarılır; sıralama seçimi korunur --}}
                @php $keepSort = $general['sort'] !== 'newest' ? ['sort' => $general['sort']] : []; @endphp
                <div class="flex flex-1 flex-wrap items-center gap-1.5 px-1 lg:px-0">
                    <a href="{{ route('reviews.index', $keepSort) }}#yorumlar" @class(['inline-flex items-center gap-2 rounded-2xl px-4 py-2.5 text-sm font-semibold transition', 'bg-[#C62E2E] text-white' => !$general['rating'], 'text-slate-600 hover:bg-slate-50' => $general['rating']])>
                        {{ __('reviews-page.all') }} <span class="text-xs opacity-70">{{ $total }}</span>
                    </a>
                    @foreach ($dist as $r => $n)
                        <a href="{{ $n ? route('reviews.index', ['rating' => $r] + $keepSort) . '#yorumlar' : '#' }}"
                            @class(['inline-flex items-center gap-1.5 rounded-2xl px-3 py-2.5 text-sm font-semibold transition', 'bg-[#C62E2E] text-white' => $general['rating'] === $r, 'text-slate-600 hover:bg-slate-50' => $general['rating'] !== $r && $n, 'pointer-events-none text-slate-300' => !$n])
                            @if (!$n) aria-disabled="true" @endif>
                            {{ $r }}<svg @class(['size-3.5', 'text-white' => $general['rating'] === $r, 'text-amber-400' => $general['rating'] !== $r && $n, 'text-slate-200' => !$n]) viewBox="0 0 24 24" fill="currentColor">{!! $star !!}</svg>
                            <span class="hidden h-1.5 w-10 overflow-hidden rounded-full 2xl:block {{ $general['rating'] === $r ? 'bg-white/30' : 'bg-slate-100' }}"><span class="block h-full rounded-full {{ $general['rating'] === $r ? 'bg-white' : 'bg-amber-400' }}" style="width: {{ $n / $max * 100 }}%"></span></span>
                            <span class="text-xs opacity-70">{{ $n }}</span>
                        </a>
                    @endforeach
                </div>

                {{-- sıralama --}}
                <form method="GET" action="{{ route('reviews.index') }}#yorumlar" class="px-1 lg:border-l lg:border-slate-100 lg:pl-4 lg:pr-1">
                    @if ($general['rating']) <input type="hidden" name="rating" value="{{ $general['rating'] }}"> @endif
                    <label class="relative flex items-center gap-2">
                        <svg class="pointer-events-none absolute left-3.5 size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 4v16M3 16l4 4 4-4M17 20V4M13 8l4-4 4 4"/></svg>
                        <span class="sr-only">{{ __('reviews-page.sort') }}</span>
                        <select name="sort" onchange="this.form.submit()"
                            class="h-11 w-full cursor-pointer appearance-none rounded-2xl bg-slate-50 pl-10 pr-10 text-sm font-semibold text-slate-700 outline-none ring-1 ring-slate-200 transition hover:ring-slate-300 focus:ring-2 focus:ring-[#C62E2E]/40 lg:w-auto">
                            @foreach (['newest', 'oldest', 'highest', 'lowest'] as $option)
                                <option value="{{ $option }}" @selected($general['sort'] === $option)>{{ __('reviews-page.sort_' . $option) }}</option>
                            @endforeach
                        </select>
                        <svg class="pointer-events-none absolute right-3.5 size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </label>
                </form>
            </div>

            @if ($reviews->isEmpty())
                <div class="mt-6 rounded-[28px] border-2 border-dashed border-slate-200 bg-white p-10 text-center">
                    <p class="text-xl font-extrabold text-slate-900">{{ __('reviews-page.empty_title') }}</p>
                    <p class="mt-2 text-slate-500">{{ __('reviews-page.empty_text') }}</p>
                </div>
            @else
                <ul class="mt-6 space-y-4">
                    @foreach ($reviews as $review)
                        <li class="group relative overflow-hidden rounded-[24px] bg-white p-6 ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-[0_24px_50px_-30px_rgba(15,23,42,0.45)] sm:p-7"
                            x-data="{ open: false, long: {{ mb_strlen($review->comment) > 320 ? 'true' : 'false' }} }">
                            <span class="absolute inset-y-6 left-0 w-1 rounded-r-full bg-gradient-to-b from-amber-300 to-[#C62E2E] opacity-0 transition group-hover:opacity-100" aria-hidden="true"></span>
                            <div class="grid gap-5 sm:grid-cols-[13rem_1fr] sm:gap-8">
                                {{-- yazar --}}
                                <div class="flex items-center gap-3 sm:flex-col sm:items-start">
                                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl text-base font-bold {{ $palette[$review->id % count($palette)] }}">{{ $initials($review->name) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-bold text-slate-900">{{ $review->display_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $review->created_at->locale(app()->getLocale())->isoFormat('D MMMM YYYY') }}</p>
                                    </div>
                                </div>
                                {{-- yorum --}}
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="flex gap-0.5">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <svg @class(['size-4', 'text-amber-400' => $i <= $review->rating, 'text-slate-200' => $i > $review->rating]) viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $star !!}</svg>
                                            @endfor
                                        </span>
                                        @if ($review->activity)
                                            <a href="{{ route('activities.show', $review->activity->slug) }}" class="inline-flex max-w-full items-center gap-1 truncate rounded-full bg-slate-100 px-2.5 py-0.5 text-[11px] font-semibold text-slate-600 transition hover:bg-[#C62E2E] hover:text-white">
                                                {!! \App\Support\FeatureIcons::svg('ticket', 'size-3') !!}<span class="truncate">{{ $review->activity->name }}</span>
                                            </a>
                                        @endif
                                    </div>
                                    <p class="mt-3 text-[16px] leading-relaxed text-slate-700" :class="long && !open && 'line-clamp-4'">{{ $review->comment }}</p>
                                    <button type="button" x-show="long" @click="open = !open" class="mt-2 text-sm font-semibold text-[#C62E2E]" x-text="open ? @js(__('reviews-page.read_less')) : @js(__('reviews-page.read_more'))"></button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if ($reviews->hasPages())
                    <div class="mt-10 flex justify-center">{{ $reviews->onEachSide(1)->fragment('yorumlar')->links('components.pagination') }}</div>
                @endif
            @endif

            {{-- yorum çağrısı --}}
            <div class="mt-12 flex flex-col items-start justify-between gap-5 rounded-[28px] bg-gradient-to-br from-[#C62E2E] to-[#8F1D1D] p-7 text-white sm:flex-row sm:items-center sm:p-9">
                <div>
                    <p class="text-xl font-extrabold sm:text-2xl">{{ $shareText['title'] }}</p>
                    <p class="mt-1 text-white/80">{{ $shareText['description'] }}</p>
                </div>
                <button type="button" onclick="{{ $openReview }}" class="inline-flex min-h-[52px] shrink-0 items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100">
                    {{ __('reviews-page.write') }}
                    <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </div>
        </div>
    </section>
        </x-slot:main>
    </x-page-sections>

    <x-review-modal :source="$source ?? null" :sourceId="$sourceId ?? null" />
@endsection

@push('scripts')
    <script>
        // "Son yorumlar" bağlantıları listeye yumuşak kayar
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[data-smooth]');
            if (!link) return;
            event.preventDefault();
            const target = document.getElementById('yorumlar');
            const header = document.querySelector('header');
            const offset = header && getComputedStyle(header).position === 'sticky' ? header.offsetHeight : 0;
            window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - offset, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        });
    </script>
    @jsonld($reviewSchema)
@endpush
