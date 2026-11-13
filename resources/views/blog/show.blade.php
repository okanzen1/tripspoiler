@extends('layouts.app')

@section('title', $blog->getTranslation('meta_title', $locale) ?: $blog->title)
@section('meta_description', Str::limit(trim(strip_tags((string) ($blog->getTranslation('meta_description', $locale) ?: $blog->excerpt))), 160))
@section('og_type', 'article')
@if ($blog->coverUrl())
    @section('og_image', $blog->coverUrl())
@endif
@push('meta')
    <meta property="article:published_time" content="{{ ($blog->published_at ?? $blog->created_at)->toIso8601String() }}" />
    <meta property="article:modified_time" content="{{ $blog->updated_at?->toIso8601String() }}" />
@endpush

@push('styles')
    <style>
        @media (prefers-reduced-motion: no-preference) {
            html {
                scroll-behavior: smooth;
            }
        }

        #articleBody :is(h2, h3), [data-post-section] {
            scroll-margin-top: 96px;
        }

        #tocList a[aria-current='true'],
        #tocListMobile a[aria-current='true'] {
            border-color: #C62E2E;
            color: #C62E2E;
            font-weight: 600;
        }
    </style>
@endpush

@php
    $coverImage = $blog->coverUrl();
    $publishedAt = $blog->published_at ?? $blog->created_at;
    // Yazı ana başlıklarından bölümlere ayrılır (App\Support\ArticleSections); telefondaki bölüm kılavuzunda da görünür
    $parts = \App\Support\ArticleSections::split($blog->content);
    $navExtra = collect($parts['sections'])->map(fn ($part, $i) => ['post-' . ($i + 1), $part['title'],
        '<span class="grid size-[22px] place-items-center rounded-md bg-current/10 text-[11px] font-extrabold tabular-nums">' . ($i + 1) . '</span>'])->all();
    $activityCount = $blog->activities->count();
    $singleActivity = $activityCount === 1 ? $blog->activities->first() : null;
    // Öneri kartları: "Detayları gör" bizim aktivite sayfasına, "Bilet al" doğrudan partnere (iki düğme)
    $activityUrl = fn ($activity) => route('activities.show', ['slug' => $activity->slug]);
    $activityChips = function ($activity) {
        $rating = $activity->reviews()->reorder()->selectRaw('COUNT(*) as total, AVG(rating) as average')->first();
        return array_values(array_filter([
            $rating?->total ? ['★ ' . number_format((float) $rating->average, 1) . ' (' . $rating->total . ')', true] : null,
            [match ($activity->type) { 'pass' => __('most-popular-activities.category_pass'), 'package' => __('most-popular-activities.category_package'), default => __('most-popular-activities.category_product') }, false],
            $activity->duration ? [$activity->duration, false] : null,
            $activity->audio_guide ? [__('activity-show.audio'), false] : null,
        ]));
    };
    $extIcon = '<svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 4h6v6M20 4l-9 9M18 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4"/></svg>';
    $arrowIcon = '<svg class="size-4 shrink-0 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>';
    $blogIndexUrl = route('blog.index', $blog->city_id ? ['city_id' => $blog->city_id] : []);

    // BlogPosting: Google makale yapısal verisi. Yazar/yayıncı layout'taki Organization'a @id ile bağlanır.
    $pageUrl = \App\Support\Schema::canonical();
    $headline = trim(strip_tags((string) $blog->getTranslation('title', $locale)));
    $schemaDescription = trim(strip_tags((string) ($blog->getTranslation('meta_description', $locale) ?: $blog->excerpt)));
    $articleSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => $pageUrl . '#article',
        // Google başlığı 110 karakterde keser
        'headline' => Str::limit($headline, 110, '…'),
        'name' => $headline,
        'description' => $schemaDescription ?: null,
        'image' => $coverImage ? [$coverImage] : [\App\Support\Schema::logoUrl()],
        'author' => [
            '@type' => 'Organization',
            '@id' => \App\Support\Schema::organizationId(),
            'name' => 'TripSpoiler',
            'url' => \App\Support\Schema::home(),
        ],
        'publisher' => \App\Support\Schema::organizationRef(),
        'datePublished' => $publishedAt->toIso8601String(),
        'dateModified' => ($blog->updated_at && $blog->updated_at->gt($publishedAt) ? $blog->updated_at : $publishedAt)->toIso8601String(),
        'inLanguage' => $locale,
        'url' => $pageUrl,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $pageUrl, 'isPartOf' => ['@id' => \App\Support\Schema::websiteId()]],
        'keywords' => array_values(array_filter((array) $blog->tags)) ? implode(', ', array_filter((array) $blog->tags)) : null,
        'wordCount' => str_word_count(strip_tags((string) $blog->content)) ?: null,
        'contentLocation' => $blog->city ? ['@type' => 'City', 'name' => $blog->city->name] : null,
        'about' => $blog->city ? ['@type' => 'TouristDestination', 'name' => $blog->city->name] : null,
    ], fn ($value) => $value !== null);
@endphp

@section('content')
    {{-- okuma ilerlemesi --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-[3px]" aria-hidden="true">
        <div id="readingProgress" class="h-full origin-left scale-x-0 bg-[#C62E2E] rtl:origin-right"></div>
    </div>

    {{-- KAPAK: başlık, özet, paylaş ve sosyal medya resmin üstünde (kutusuz); masaüstünde sağda editörün seçimi --}}
    @php
        $previewActivity = $singleActivity ?? $blog->activities->first();
    @endphp
    <section class="relative isolate overflow-hidden bg-[#0F1420] text-white">
        @if ($coverImage)
            <img src="{{ $coverImage }}" alt="{{ $blog->title }}" fetchpriority="high" class="absolute inset-0 -z-20 h-full w-full object-cover object-center">
        @else
            <div class="absolute inset-0 -z-20 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:72px_72px]" aria-hidden="true"></div>
        @endif
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-[#0F1420] via-[#0F1420]/70 to-[#0F1420]/30" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-[#0F1420]/80 via-[#0F1420]/30 to-transparent rtl:bg-gradient-to-l" aria-hidden="true"></div>

        <div class="mx-auto max-w-7xl px-4 pb-10 pt-6 md:pb-14 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('blog-page.blog'), 'url' => route('blog.index')],
                ...($blog->city ? [['label' => $blog->city->name, 'url' => route('blog.index', ['city_id' => $blog->city_id])]] : []),
                ['label' => $blog->title, 'url' => url()->current()],
            ]" />

            <div class="mt-24 grid items-end gap-10 sm:mt-32 lg:mt-40 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-bold uppercase tracking-[0.18em] text-white/70">
                        <span class="inline-flex items-center gap-2 text-[#FF8A8C]"><span class="h-px w-8 bg-[#FF8A8C]"></span>{{ $blog->city?->name ?? __('blog-page.blog') }}</span>
                        <span class="text-white/30">·</span>
                        <span>{{ $publishedAt->locale($locale)->isoFormat('D MMMM YYYY') }}</span>
                        <span class="text-white/30">·</span>
                        <span>{{ __('blog-page.min_read', ['count' => $blog->readingMinutes()]) }}</span>
                    </p>
                    <h1 class="mt-4 text-[34px] font-extrabold leading-[1.05] tracking-[-0.04em] text-balance drop-shadow-[0_2px_16px_rgba(0,0,0,0.35)] sm:text-5xl lg:text-[56px]">{{ $blog->title }}</h1>
                    @if ($blog->excerpt)
                        <p class="mt-5 max-w-2xl text-base leading-relaxed text-white/80 md:text-lg">{{ $blog->excerpt }}</p>
                    @endif

                            {{-- paylaş + bizi takip et --}}
                            <div class="mt-6 flex flex-wrap items-center gap-2">
                                <button type="button" data-copy-link aria-label="{{ __('blog.copy_link') }}" title="{{ __('blog.copy_link') }}"
                                    class="grid size-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/25 backdrop-blur-md transition hover:bg-[#C62E2E] hover:ring-[#C62E2E]">
                                    <svg class="size-[17px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 13.5a4 4 0 0 0 5.7 0l2.3-2.3a4 4 0 0 0-5.7-5.7l-1.3 1.3"/><path d="M13.5 10.5a4 4 0 0 0-5.7 0l-2.3 2.3a4 4 0 0 0 5.7 5.7l1.3-1.3"/></svg>
                                </button>
                                <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($blog->title) }}" target="_blank" rel="noopener noreferrer" aria-label="X" title="X"
                                    class="grid size-10 place-items-center rounded-full bg-white/10 text-white ring-1 ring-white/25 backdrop-blur-md transition hover:bg-black hover:ring-black">
                                    <svg class="size-[14px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.9 2H22l-6.8 7.8L23 22h-6.3l-4.9-6.4L6.2 22H3l7.3-8.3L3 2h6.4l4.4 5.9L18.9 2Zm-1.1 18h1.7L8.3 3.9H6.5L17.8 20Z"/></svg>
                                </a>
                                <a href="https://wa.me/?text={{ urlencode($blog->title . ' ' . url()->current()) }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" title="WhatsApp"
                                    class="grid size-10 place-items-center rounded-full bg-[#25D366] text-white ring-1 ring-white/25 transition hover:bg-[#1DA851]">
                                    <svg class="size-[17px]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15L2 22l5.2-1.4A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3.1.8.8-3-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.7-6.1c-.3-.1-1.6-.8-1.8-.9-.2-.1-.4-.1-.6.1-.2.3-.7.9-.8 1.1-.2.2-.3.2-.6.1-.3-.1-1.1-.4-2.1-1.3-.8-.7-1.3-1.5-1.4-1.8-.2-.3 0-.4.1-.5l.4-.5.3-.4c.1-.2 0-.4 0-.5 0-.1-.6-1.5-.8-2-.2-.5-.4-.4-.6-.4h-.5c-.2 0-.5.1-.7.3-.2.3-.9.9-.9 2.1s.9 2.4 1 2.6c.1.2 1.7 2.7 4.2 3.7 1.6.7 2.3.7 3.1.6.5-.1 1.6-.7 1.8-1.3.2-.6.2-1.2.2-1.3-.1-.1-.3-.2-.6-.3Z"/></svg>
                                </a>
                                <span class="mx-1.5 h-6 w-px bg-white/25" aria-hidden="true"></span>
                                @foreach (\App\Support\Socials::all() as [$name, $url, $handle, $icon, $tone])
                                    <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ $name }}" title="{{ __('footer.follow') }} · {{ $name }}"
                                        class="grid size-10 place-items-center rounded-full text-white ring-1 ring-white/25 transition hover:scale-110 {{ $tone }}">
                                        <svg class="size-[17px]" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">{!! $icon !!}</svg>
                                    </a>
                                @endforeach
                            </div>
                </div>
                @if ($previewActivity)
                    <div class="hidden lg:col-span-4 lg:block">
                        @include('blog.partials.pick-card')
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- telefonda editörün seçimi kapağın altında --}}
    @if ($previewActivity)
        <div class="bg-white px-4 pt-6 lg:hidden">
            @include('blog.partials.pick-card')
        </div>
    @endif

    {{-- Alt bölümler panelden (Sayfalar › Blog yazısı › Bölümler); "main" = makale + yazıdaki deneyimler --}}
    <x-page-sections page="post" :post="$blog" :nav-extra="$navExtra">
        <x-slot:main>
            {{-- MAKALE: her ana başlık (h2) ayrı bölüm — yapışkan sekmeler, solda başlık, sağda yazı ("Devamını oku") --}}
            @if ($parts['sections'])
                <nav aria-label="{{ __('blog.toc') }}" class="sticky top-[69px] z-30 border-b border-slate-200 bg-white/95 backdrop-blur-xl lg:top-0"
                    x-data="{
                        active: @js($parts['sections'][0]['id']), atStart: true, atEnd: false,
                        init() {
                            const io = new IntersectionObserver((entries) => {
                                entries.forEach((e) => { if (e.isIntersecting) this.active = e.target.id });
                            }, { rootMargin: '-35% 0px -60% 0px' });
                            document.querySelectorAll('[data-post-section]').forEach((el) => io.observe(el));
                            this.edges();
                        },
                        edges() { const t = this.$refs.track; const x = Math.abs(t.scrollLeft); this.atStart = x < 4; this.atEnd = x + t.clientWidth >= t.scrollWidth - 4 },
                        go(dir) { const t = this.$refs.track; const rtl = getComputedStyle(t).direction === 'rtl'; t.scrollBy({ left: t.clientWidth * 0.6 * dir * (rtl ? -1 : 1), behavior: 'smooth' }) },
                    }"
                    x-effect="const chip = $refs['chip-' + active], t = $refs.track; if (chip && t) t.scrollTo({ left: chip.offsetLeft - t.clientWidth / 2 + chip.offsetWidth / 2, behavior: 'smooth' })">
                    <div class="relative mx-auto max-w-7xl md:px-4">
                        <div x-ref="track" @scroll.throttle.80ms="edges()" class="flex overflow-x-auto px-2 [scrollbar-width:none] md:px-0 [&::-webkit-scrollbar]:hidden">
                            @foreach ($parts['sections'] as $part)
                                <a href="#{{ $part['id'] }}" x-ref="chip-{{ $part['id'] }}" data-post-link
                                    :class="active === @js($part['id']) ? 'text-slate-900' : 'text-slate-500 hover:text-slate-900'"
                                    class="group relative inline-flex shrink-0 items-center gap-2 whitespace-nowrap px-3.5 py-4 text-sm font-semibold transition md:px-4">
                                    <span :class="active === @js($part['id']) ? 'bg-[#C62E2E] text-white' : 'bg-slate-100 text-slate-500 group-hover:bg-slate-200'" class="grid size-6 place-items-center rounded-full text-[11px] font-extrabold tabular-nums transition">{{ $loop->iteration }}</span>
                                    <span class="max-w-[15rem] truncate">{{ $part['title'] }}</span>
                                    <span :class="active === @js($part['id']) ? 'scale-x-100' : 'scale-x-0'" class="absolute inset-x-3 bottom-0 h-[3px] origin-center rounded-t-full bg-[#C62E2E] transition duration-300"></span>
                                </a>
                            @endforeach
                        </div>
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
            @endif

            <div id="articleBody">
                @if ($parts['intro'] !== '')
                    <section class="bg-white pb-4 pt-12 md:pt-16">
                        <div class="mx-auto grid max-w-7xl px-4 lg:grid-cols-12 lg:gap-14">
                            <div class="rich-text quill-content text-[17px] lg:col-span-8 lg:col-start-5">{!! $parts['intro'] !!}</div>
                        </div>
                    </section>
                @endif

                @foreach ($parts['sections'] as $part)
                    @php $tone = $loop->odd ? 'bg-white' : 'bg-slate-50'; @endphp
                    <section id="{{ $part['id'] }}" data-post-section data-nav-section="post-{{ $loop->iteration }}" class="{{ $tone }} scroll-mt-36 py-12 md:py-16 lg:scroll-mt-24">
                        <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-12 lg:gap-14">
                            <header class="lg:col-span-4">
                                <div class="lg:sticky lg:top-28">
                                    <div class="flex items-center gap-3">
                                        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] text-lg font-extrabold tabular-nums text-white shadow-[0_12px_26px_-12px_rgba(198,46,46,0.8)]">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                        <p class="text-xs font-semibold text-slate-400">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }} / {{ str_pad($loop->count, 2, '0', STR_PAD_LEFT) }} · {{ __('blog-page.min_read', ['count' => $part['minutes']]) }}</p>
                                    </div>
                                    <h2 class="mt-5 text-[26px] font-extrabold leading-[1.15] tracking-[-0.025em] text-slate-900 sm:text-[32px]">{{ $part['title'] }}</h2>
                                </div>
                            </header>
                            <div class="min-w-0 lg:col-span-8" x-data="{ open: false, long: false }" x-init="$nextTick(() => { long = $refs.body.scrollHeight > 640 })">
                                <div x-ref="body" class="relative overflow-hidden" :class="long && !open ? 'max-h-[480px]' : ''">
                                    <div class="rich-text quill-content">{!! $part['html'] !!}</div>
                                    <div x-show="long && !open" x-cloak class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t {{ $loop->odd ? 'from-white' : 'from-slate-50' }} to-transparent"></div>
                                </div>
                                <button type="button" x-show="long" x-cloak
                                    @click="open = !open; if (!open) $el.closest('section').scrollIntoView({ behavior: 'smooth' })"
                                    class="mt-6 inline-flex items-center gap-2 rounded-full bg-[#C62E2E] px-5 py-2.5 text-sm font-bold text-white shadow-[0_10px_24px_-10px_rgba(198,46,46,0.8)] transition hover:bg-[#A82424]">
                                    <span x-text="open ? @js(__('cities-page.read_less')) : @js(__('cities-page.read_more'))"></span>
                                    <svg class="size-4 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- yazı sonu --}}
            <section class="bg-white py-10 md:py-12">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="flex flex-col gap-5 rounded-[24px] bg-slate-50 p-6 ring-1 ring-slate-200/70 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white ring-1 ring-slate-200/80">
                                <img src="{{ asset('images/logo-mark.webp') }}" alt="" width="210" height="240" class="h-8 w-auto" loading="lazy">
                            </span>
                            <div class="min-w-0">
                                <p class="text-[15px] font-extrabold tracking-tight text-slate-900">Trip<span class="text-[#C62E2E]">Spoiler</span></p>
                                <p class="mt-0.5 text-[13px] leading-snug text-slate-500">{{ __('footer.tagline') }}</p>
                            </div>
                        </div>
                        <a href="{{ $blogIndexUrl }}" class="inline-flex min-h-[44px] shrink-0 items-center justify-center gap-2 rounded-full bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#C62E2E]">
                            {{ __('blog-page.latest') }} {!! $arrowIcon !!}
                        </a>
                    </div>
                </div>
            </section>

            {{-- YAZIDAKİ DENEYİMLER --}}
            @if ($singleActivity)
                <section id="activitiesSection" class="scroll-mt-24 bg-white pb-12 md:pb-16">
                    <div class="mx-auto max-w-7xl px-4">
                        @php $chips = $activityChips($singleActivity); @endphp
                        <div class="group relative isolate grid overflow-hidden rounded-[28px] bg-[#0F1420] text-white shadow-[0_40px_80px_-40px_rgba(15,23,42,0.8)] lg:grid-cols-2">
                            <a href="{{ $activityUrl($singleActivity) }}" class="relative block min-h-[260px] overflow-hidden sm:min-h-[340px] lg:min-h-[420px]">
                                <img loading="lazy" decoding="async"
                                    src="{{ $singleActivity->cover?->url() ?? asset('images/all-activities-cover.webp') }}"
                                    alt="{{ $singleActivity->name }}"
                                    class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-[#0F1420]/80 via-[#0F1420]/10 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-transparent lg:to-[#0F1420]/60 rtl:lg:bg-gradient-to-l"></div>
                                <span class="absolute left-5 top-5 inline-flex items-center gap-1.5 rounded-full bg-amber-400 px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] text-slate-950 shadow-lg rtl:left-auto rtl:right-5">
                                    <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="m12 3 2.6 5.6 6.1.7-4.5 4.2 1.2 6L12 16.6l-5.4 2.9 1.2-6-4.5-4.2 6.1-.7Z"/></svg>
                                    {{ __('blog.editors_pick') }}
                                </span>
                            </a>

                            <div class="relative flex flex-col justify-center p-7 sm:p-10">
                                <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                                    <div class="absolute inset-0 opacity-[0.06] [background-image:radial-gradient(rgba(255,255,255,.9)_1px,transparent_1px)] [background-size:22px_22px]"></div>
                                    <div class="absolute -right-24 -top-24 size-80 rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.4),transparent_65%)]"></div>
                                    <div class="absolute -bottom-28 left-10 size-72 rounded-full bg-[radial-gradient(circle,rgba(251,191,36,0.18),transparent_65%)]"></div>
                                </div>
                                <p class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-white/50">
                                    <svg class="size-3.5 shrink-0 text-[#FF6B6E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                    {{ $singleActivity->city?->name }}@if ($singleActivity->city?->country?->name)<span class="text-white/25">·</span>{{ $singleActivity->city->country->name }}@endif
                                </p>
                                <h2 class="mt-3 text-2xl font-extrabold leading-tight tracking-[-0.02em] text-balance sm:text-3xl">
                                    <a href="{{ $activityUrl($singleActivity) }}" class="transition hover:text-[#FF8A8C]">{{ $singleActivity->name }}</a>
                                </h2>
                                @if ($chips)
                                    <ul class="mt-5 flex flex-wrap gap-2">
                                        @foreach ($chips as [$chip, $star])
                                            <li @class(['rounded-full px-3 py-1.5 text-xs font-bold ring-1', 'bg-amber-400/15 text-amber-300 ring-amber-300/25' => $star, 'bg-white/[0.07] text-white/80 ring-white/10' => !$star])>{{ $chip }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                                <div class="mt-7 flex flex-wrap items-center gap-3">
                                    <a href="{{ $activityUrl($singleActivity) }}"
                                        class="inline-flex min-h-[50px] items-center gap-2 rounded-full bg-[#C62E2E] px-7 py-3 text-[15px] font-bold text-white shadow-[0_14px_30px_-12px_rgba(198,46,46,0.9)] transition hover:bg-[#A82424]">
                                        {{ __('blog.see_details') }} {!! $arrowIcon !!}
                                    </a>
                                    @if ($singleActivity->affiliate_url)
                                        <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener"
                                            class="inline-flex min-h-[50px] items-center gap-2 rounded-full bg-gradient-to-r from-amber-300 to-amber-500 px-6 py-3 text-[15px] font-extrabold text-slate-950 shadow-[0_14px_30px_-12px_rgba(245,158,11,0.9)] transition hover:from-amber-200 hover:to-amber-400">
                                            {{ __('blog.buy_tickets') }} {!! $extIcon !!}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @elseif ($activityCount > 1)
                <section id="activitiesSection" class="scroll-mt-24 bg-white pb-12 md:pb-16">
                    <div class="mx-auto max-w-7xl px-4">
                        <div>
                            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">
                                <span class="h-px w-7 shrink-0 bg-[#C62E2E]"></span>
                                {{ __('blog.editors_picks') }}
                            </p>
                            <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.02em] text-slate-900 md:text-[32px]">
                                {{ $activityCount }} {{ __('blog.experiences') }}
                            </h2>
                        </div>

                        <div class="mt-6 grid gap-4 sm:mt-8 sm:grid-cols-2 sm:gap-5">
                            @foreach ($blog->activities as $activity)
                                <div class="group flex flex-row overflow-hidden rounded-[20px] bg-white ring-1 ring-slate-200/80 shadow-[0_22px_55px_-40px_rgba(15,23,42,0.45)] transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_30px_70px_-40px_rgba(15,23,42,0.5)] lg:rounded-[26px]">
                                    <a href="{{ $activityUrl($activity) }}" class="relative w-[32%] shrink-0 self-stretch overflow-hidden bg-slate-100 sm:w-[38%] lg:w-[42%]">
                                        <img loading="lazy" decoding="async"
                                            src="{{ $activity->cover?->url() ?? asset('images/all-activities-cover.webp') }}"
                                            alt="{{ $activity->name }}"
                                            class="absolute inset-0 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                    </a>
                                    <div class="flex min-w-0 flex-1 flex-col p-4 sm:p-5 lg:p-6">
                                        <p class="inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">
                                            <svg class="size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                                            {{ $activity->city?->name }}
                                        </p>
                                        <h3 class="mt-1.5 line-clamp-3 text-base font-extrabold leading-snug tracking-[-0.01em] text-slate-900 lg:mt-2 lg:text-lg">
                                            <a href="{{ $activityUrl($activity) }}" class="transition hover:text-[#C62E2E]">{{ $activity->name }}</a>
                                        </h3>
                                        @if ($chips = array_slice($activityChips($activity), 0, 3))
                                            <ul class="mt-2.5 flex flex-wrap gap-1.5">
                                                @foreach ($chips as [$chip, $star])
                                                    <li @class(['rounded-full px-2 py-0.5 text-[11px] font-bold', 'bg-amber-50 text-amber-700' => $star, 'bg-slate-100 text-slate-600' => !$star])>{{ $chip }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        <div class="mt-auto flex flex-wrap items-center gap-2 pt-4">
                                            <a href="{{ $activityUrl($activity) }}" class="inline-flex min-h-[38px] items-center gap-1.5 rounded-full bg-[#C62E2E] px-4 text-[13px] font-bold text-white transition hover:bg-[#A82424]">
                                                {{ __('blog.see_details') }} {!! $arrowIcon !!}
                                            </a>
                                            @if ($activity->affiliate_url)
                                                <a href="{{ $activity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener" class="inline-flex min-h-[38px] items-center gap-1.5 rounded-full bg-gradient-to-r from-amber-300 to-amber-500 px-3.5 text-[13px] font-extrabold text-slate-950 transition hover:from-amber-200 hover:to-amber-400">
                                                    {{ __('blog.buy_tickets') }} {!! $extIcon !!}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </x-slot:main>
    </x-page-sections>


    {{-- DİĞER REHBERLER --}}
    @if ($related->isNotEmpty())
        <section class="bg-slate-50 py-12 md:py-16">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('blog.blog') }}</p>
                        <h2 class="mt-2 text-2xl font-extrabold tracking-[-0.02em] text-slate-900 md:text-3xl">
                            {{ $blog->city ? __('blog.related_city', ['city' => $blog->city->name]) : __('blog.related_all') }}
                        </h2>
                    </div>
                    <a href="{{ $blogIndexUrl }}"
                        class="hidden shrink-0 items-center gap-2 text-sm font-bold text-slate-700 transition hover:text-[#C62E2E] sm:inline-flex">
                        {{ __('blog-page.latest') }}
                        <svg class="size-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <div class="mt-7 grid gap-x-4 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $post)
                        <a href="{{ route('blog.show', $post->slug) }}" class="group block">
                            <div class="relative aspect-[4/3] overflow-hidden rounded-[22px] bg-slate-200 ring-1 ring-slate-900/5">
                                @if ($post->cover_path)
                                    <img src="{{ $post->coverUrl('sm') }}" alt="{{ $post->title }}" loading="lazy" decoding="async"
                                        class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06]">
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/55 via-transparent to-transparent opacity-80 transition group-hover:opacity-100"></div>
                                @if ($firstTag = ((array) $post->tags)[0] ?? null)
                                    <span class="absolute left-3 top-3 max-w-[85%] truncate rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-bold text-slate-900 backdrop-blur rtl:left-auto rtl:right-3">{{ $firstTag }}</span>
                                @endif
                                <span class="absolute bottom-3 left-3 inline-flex items-center gap-1 text-[11px] font-semibold text-white/90 rtl:left-auto rtl:right-3">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                                    {{ __('blog-page.min_read', ['count' => $post->readingMinutes()]) }}
                                </span>
                            </div>
                            <h3 class="mt-3.5 line-clamp-2 text-[17px] font-extrabold leading-snug tracking-[-0.01em] text-slate-900 transition group-hover:text-[#C62E2E]">{{ $post->title }}</h3>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($activityCount > 0)
        {{-- MOBİL SABİT ÇUBUK --}}
        <div id="mobileStickyBar" data-bottom-bar class="fixed inset-x-0 bottom-0 z-50 translate-y-full transition-transform duration-500 lg:hidden">
            <div class="mx-3 mb-3 flex items-center justify-between gap-3 rounded-[20px] bg-white/95 p-3 shadow-[0_18px_50px_-18px_rgba(15,23,42,0.4)] ring-1 ring-slate-200 backdrop-blur">
                <div class="flex min-w-0 items-center gap-3">
                    @if ($singleActivity?->cover)
                        <img src="{{ $singleActivity->cover->url('sm') }}" alt="" class="size-11 shrink-0 rounded-xl object-cover">
                    @endif
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                            {{ $singleActivity ? __('blog.editors_pick') : __('blog.editors_picks') }}
                        </p>
                        <p class="truncate text-sm font-bold text-slate-900">
                            {{ $singleActivity ? $singleActivity->name : $activityCount . ' ' . __('blog.experiences') }}
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-1.5">
                    @if ($singleActivity?->affiliate_url)
                        <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener" aria-label="{{ __('blog.buy_tickets') }}" title="{{ __('blog.buy_tickets') }}"
                            class="grid size-11 place-items-center rounded-full bg-gradient-to-br from-amber-300 to-amber-500 text-slate-950 transition hover:from-amber-200 hover:to-amber-400">
                            <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h11A2.5 2.5 0 0 1 20 8.5v1a2.5 2.5 0 0 0 0 5v1a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 15.5v-1a2.5 2.5 0 0 0 0-5v-1Z"/><path d="M14 9.5v1.5M14 13v1.5"/></svg>
                        </a>
                    @endif
                    <a href="{{ $singleActivity ? $activityUrl($singleActivity) : '#activitiesSection' }}"
                        class="inline-flex min-h-[44px] shrink-0 items-center gap-1.5 rounded-full bg-[#C62E2E] px-4 py-2.5 text-xs font-bold text-white transition hover:bg-[#A82424]">
                        {{ $singleActivity ? __('blog.see_details') : __('blog.see_experiences') }}
                        <svg class="size-3.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- MASAÜSTÜ SABİT ÇUBUK --}}
        <div id="stickyCtaBar" class="fixed inset-x-0 bottom-0 z-50 hidden translate-y-full transition-transform duration-500 lg:block">
            <div class="border-t border-slate-200/80 bg-white/90 shadow-[0_-16px_40px_-30px_rgba(15,23,42,0.45)] backdrop-blur">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-3.5">
                    <div class="flex min-w-0 items-center gap-4">
                        @if ($singleActivity?->cover)
                            <img src="{{ $singleActivity->cover->url('sm') }}" alt="" class="size-12 shrink-0 rounded-xl object-cover" loading="lazy">
                        @endif
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-slate-400">
                                {{ $singleActivity ? __('blog.editors_pick') : __('blog.editors_picks') }}
                            </p>
                            <p class="truncate text-sm font-bold text-slate-900 md:text-[15px]">
                                {{ $singleActivity ? $singleActivity->name : $activityCount . ' ' . __('blog.experiences') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        @if ($singleActivity?->affiliate_url)
                            <a href="{{ $singleActivity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener"
                                class="inline-flex min-h-[44px] items-center gap-2 rounded-full bg-gradient-to-r from-amber-300 to-amber-500 px-5 py-2.5 text-sm font-extrabold text-slate-950 transition hover:from-amber-200 hover:to-amber-400">
                                {{ __('blog.buy_tickets') }} {!! $extIcon !!}
                            </a>
                        @endif
                        <a href="{{ $singleActivity ? $activityUrl($singleActivity) : '#activitiesSection' }}"
                            class="inline-flex min-h-[44px] items-center gap-2 rounded-full bg-[#C62E2E] px-6 py-2.5 text-sm font-bold text-white transition hover:bg-[#A82424]">
                            {{ $singleActivity ? __('blog.see_details') : __('blog.see_experiences') }} {!! $arrowIcon !!}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- kopyalandı bildirimi --}}
    <div id="copyToast" role="status" aria-live="polite"
        class="pointer-events-none fixed bottom-24 left-1/2 z-[60] -translate-x-1/2 translate-y-2 rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white opacity-0 shadow-lg transition duration-300">
        {{ __('blog.copied') }}
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const article = document.getElementById('articleBody');
            const progress = document.getElementById('readingProgress');
            const mobileBar = document.getElementById('mobileStickyBar');
            const desktopBar = document.getElementById('stickyCtaBar');
            const copyToast = document.getElementById('copyToast');
            const footer = document.querySelector('footer');
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            /* okuma ilerlemesi + sabit çubuklar (footer görününce çubuklar gizlenir) */
            const onScroll = () => {
                const max = document.documentElement.scrollHeight - window.innerHeight;
                if (progress) progress.style.transform = `scaleX(${max > 0 ? Math.min(1, window.scrollY / max) : 0})`;

                const atFooter = footer && footer.getBoundingClientRect().top < window.innerHeight;
                const show = window.scrollY > 600 && !atFooter;
                mobileBar?.classList.toggle('translate-y-full', !show);
                desktopBar?.classList.toggle('translate-y-full', !show);
            };
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();

            /* içindekiler: makaledeki h2/h3 başlıklarından üretilir */
            const lists = [document.getElementById('tocList'), document.getElementById('tocListMobile')].filter(Boolean);
            const tocCard = document.getElementById('tocCard');
            const tocMobile = document.getElementById('tocMobile');
            const headings = article ? [...article.querySelectorAll('h2, h3')].filter((h) => h.textContent.trim()) : [];

            if (headings.length >= 1 && lists.length) {
                tocCard?.classList.remove('hidden');
                tocMobile?.classList.remove('hidden');

                const entries = [];

                headings.forEach((heading, index) => {
                    if (!heading.id) {
                        const base = heading.textContent.toLocaleLowerCase()
                            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                            .replace(/[^\p{L}\p{N}]+/gu, '-').replace(/^-+|-+$/g, '').slice(0, 60) || `bolum-${index + 1}`;
                        let id = base;
                        let n = 2;
                        while (document.getElementById(id) && document.getElementById(id) !== heading) id = `${base}-${n++}`;
                        heading.id = id;
                    }

                    const anchors = [];

                    lists.forEach((list) => {
                        const li = document.createElement('li');
                        const a = document.createElement('a');
                        a.href = `#${heading.id}`;
                        a.textContent = heading.textContent.trim();
                        a.className = '-ms-px block border-s-2 border-transparent py-1.5 ps-3 text-[13px] font-medium leading-snug text-slate-500 transition hover:border-[#C62E2E] hover:text-[#C62E2E]'
                            + (heading.tagName === 'H3' ? ' ps-6 text-[12.5px]' : '');
                        a.addEventListener('click', (event) => {
                            event.preventDefault();
                            // Önce mobil içindekileri kapat: yüksekliği değişince hedefin konumu kaymasın
                            if (tocMobile?.open) tocMobile.open = false;
                            history.replaceState(null, '', `#${heading.id}`);
                            heading.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
                            setActive(heading);
                        });
                        li.appendChild(a);
                        list.appendChild(li);
                        anchors.push(a);
                    });

                    entries.push({ heading, anchors });
                });

                // Telefonda uzun liste kısaltılır: ilk 4 başlık + "Tümünü göster (n)"
                const mobileItems = [...(document.getElementById('tocListMobile')?.children ?? [])];
                const more = document.getElementById('tocMore');
                if (more && mobileItems.length > 5) {
                    let expanded = false;
                    const render = () => {
                        mobileItems.forEach((li, i) => li.hidden = !expanded && i >= 4);
                        more.textContent = expanded ? more.dataset.less : more.dataset.more.replace(':count', mobileItems.length);
                    };
                    more.hidden = false;
                    more.addEventListener('click', () => { expanded = !expanded; render(); });
                    render();
                }

                const setActive = (current) => {
                    entries.forEach(({ heading, anchors }) => {
                        anchors.forEach((a) => {
                            if (heading === current) a.setAttribute('aria-current', 'true');
                            else a.removeAttribute('aria-current');
                        });
                    });
                };

                const spy = () => {
                    let current = headings[0];
                    for (const heading of headings) {
                        if (heading.getBoundingClientRect().top <= 140) current = heading;
                    }
                    if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
                        current = headings[headings.length - 1];
                    }
                    setActive(current);
                };

                window.addEventListener('scroll', spy, { passive: true });
                spy();

                if (location.hash) {
                    let key = location.hash.slice(1);
                    try {
                        key = decodeURIComponent(key);
                    } catch {}
                    const target = headings.find((h) => h.id === key);
                    if (target) {
                        setActive(target);
                        target.scrollIntoView({ behavior: 'instant', block: 'start' });
                    }
                }
            }

            /* bağlantıyı kopyala */
            document.querySelectorAll('[data-copy-link]').forEach((button) => {
                button.addEventListener('click', async () => {
                    const url = window.location.href;
                    try {
                        await navigator.clipboard.writeText(url);
                    } catch {
                        const field = document.createElement('textarea');
                        field.value = url;
                        field.style.position = 'fixed';
                        field.style.opacity = '0';
                        document.body.appendChild(field);
                        field.select();
                        document.execCommand('copy');
                        field.remove();
                    }
                    if (copyToast) {
                        copyToast.classList.remove('opacity-0', 'translate-y-2');
                        clearTimeout(window.__copyToast);
                        window.__copyToast = setTimeout(() => copyToast.classList.add('opacity-0', 'translate-y-2'), 2200);
                    }
                    button.setAttribute('aria-label', @json(__('blog.copied')));
                });
            });

        });
    </script>

    @jsonld($articleSchema)
@endpush
