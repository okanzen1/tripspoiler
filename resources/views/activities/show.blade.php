@extends('layouts.app')

@section('title', $metaTitle)
@section('meta_description', Str::limit(strip_tags($metaDescription), 155))
@if ($activity->images->first()?->url())
    @section('og_image', $activity->images->first()->url())
@endif

{{--
    Aktivite detayı: sayfa yolu, galeri (masaüstünde mozaik, telefonda kaydırmalı), bilgi kartları, açıklama ve
    rezervasyon kutusu (partner sitesine gider; fiyat yok, affiliate). Alttaki bölümler panelde
    Sayfalar › Aktivite detayı › Bölümler (yorumlar, sorular, aynı şehirdeki benzerler…).
--}}
@push('styles')
    {{-- galeri CSS'i yalnızca fotoğraf açılınca gerekir: sayfa çizimini bekletmesin --}}
    <link rel="preload" as="style" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.36/dist/fancybox/fancybox.css" onload="this.onload=null;this.rel='stylesheet'" />
    {{-- telefonda alttaki sabit rezervasyon çubuğu sayfanın sonunu örtmesin --}}
    <style>@media (max-width: 1023px) { body { padding-bottom: 5.5rem; } }</style>
@endpush

@php
    $locale = app()->getLocale();
    $name = $activity->name;
    $city = $activity->city;
    $images = $activity->images;
    $imageUrls = $images->map(fn ($image) => $image->url())->filter()->values()->all();
    $flag = fn ($code) => $code ? implode('', array_map(fn ($c) => mb_chr(0x1F1E6 + ord($c) - 65), str_split(strtoupper($code)))) : '';
    $typeLabel = match ($activity->type) {
        'pass' => __('most-popular-activities.category_pass'),
        'package' => __('most-popular-activities.category_package'),
        default => __('most-popular-activities.category_product'),
    };
    $partnerLogo = $activity->partner?->logo_path ? $activity->partner->logoUrl() : null;
    $plainDescription = trim(strip_tags((string) $activity->description));

    // Bilgi kartları: sadece bilinenler
    $facts = array_values(array_filter([
        ['label' => __('activity-show.type'), 'value' => $typeLabel, 'icon' => '<rect x="3" y="6" width="18" height="12" rx="2.5"/><path d="M3 10h18M7 14.5h4"/>'],
        $activity->duration ? ['label' => __('activity-show.duration'), 'value' => $activity->duration, 'icon' => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>'] : null,
        $activity->audio_guide ? ['label' => __('activity-show.audio'), 'value' => __('activity-show.audio_yes'), 'icon' => '<path d="M4 14.5v-2a8 8 0 0 1 16 0v2"/><rect x="3.5" y="13.5" width="4.5" height="6.5" rx="2"/><rect x="16" y="13.5" width="4.5" height="6.5" rx="2"/>'] : null,
        ['label' => __('activity-show.location'), 'value' => $city->name, 'icon' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>'],
    ]));

    // Masaüstü mozaik: ilk resim büyük, yanında en fazla 4 küçük
    $mosaic = $images->take(5);
    $spans = match ($mosaic->count()) {
        1 => ['col-span-4 row-span-2'],
        2 => ['col-span-2 row-span-2', 'col-span-2 row-span-2'],
        3 => ['col-span-2 row-span-2', 'col-span-2', 'col-span-2'],
        4 => ['col-span-2 row-span-2', 'col-span-2', '', ''],
        default => ['col-span-2 row-span-2', '', '', '', ''],
    };

    // TouristAttraction: aktivite (fiyat bilgisi partnerde olduğu için Offer/Product verilmez; uydurma fiyat
    // Google'da "yanıltıcı yapısal veri" sayılır). Şehir ve ülke containedInPlace ile bağlanır.
    $pageUrl = \App\Support\Schema::canonical();
    $activitySchema = [
        '@context' => 'https://schema.org',
        '@type' => 'TouristAttraction',
        '@id' => $pageUrl . '#attraction',
        'name' => $name,
        'description' => Str::limit($plainDescription, 300) ?: null,
        'image' => $imageUrls ?: [asset('images/all-activities-cover.webp')],
        'url' => $pageUrl,
        'isAccessibleForFree' => false,
        'containedInPlace' => array_filter([
            '@type' => 'City',
            'name' => $city->name,
            'url' => route('cities.show', $city->slug),
            'containedInPlace' => $city->country ? ['@type' => 'Country', 'name' => $city->country->name] : null,
        ]),
        'address' => array_filter(['@type' => 'PostalAddress', 'addressLocality' => $city->name, 'addressCountry' => $city->country?->code ?: $city->country?->name]),
        'geo' => $city->latitude && $city->longitude
            ? ['@type' => 'GeoCoordinates', 'latitude' => (float) $city->latitude, 'longitude' => (float) $city->longitude]
            : null,
        'subjectOf' => ['@type' => 'WebPage', '@id' => $pageUrl, 'isPartOf' => ['@id' => \App\Support\Schema::websiteId()], 'publisher' => \App\Support\Schema::organizationRef()],
    ];
    // Onaylı yorumlardan puan özeti (yıldız ve yorum sayısı)
    $ratingStats = $activity->reviews()->reorder()->selectRaw('COUNT(*) as total, AVG(rating) as average')->first();
    if ($ratingStats && $ratingStats->total > 0) {
        $activitySchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => round((float) $ratingStats->average, 1),
            'reviewCount' => (int) $ratingStats->total,
            'bestRating' => 5,
            'worstRating' => 1,
        ];
    }
    $activitySchema = array_filter($activitySchema, fn ($value) => $value !== null);
@endphp

@section('content')
    {{-- ÜST: sayfa yolu, etiketler, başlık --}}
    <section class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
        <div class="mx-auto max-w-7xl px-4 pb-8 pt-6 md:pb-10 md:pt-8">
            <x-breadcrumbs :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('activities-page.eyebrow'), 'url' => route('activities.index')],
                ['label' => $city->name, 'url' => route('activities.index', ['city' => $city->slug])],
                ['label' => $name, 'url' => url()->current()],
            ]" />

            <div class="mt-6 flex flex-wrap items-center gap-2">
                <span @class(['rounded-lg px-2.5 py-1 text-xs font-bold uppercase tracking-wide text-white', 'bg-[#C62E2E]' => $activity->type === 'pass', 'bg-slate-900' => $activity->type !== 'pass'])>{{ $typeLabel }}</span>
                @if ($activity->audio_guide)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 14.5v-2a8 8 0 0 1 16 0v2"/><rect x="3.5" y="13.5" width="4.5" height="6.5" rx="2"/><rect x="16" y="13.5" width="4.5" height="6.5" rx="2"/></svg>
                        {{ __('activity-show.audio') }}
                    </span>
                @endif
                @if ($activity->duration)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>
                        {{ $activity->duration }}
                    </span>
                @endif
            </div>

            <h1 class="mt-4 text-3xl font-extrabold leading-[1.1] text-balance tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[46px]">{{ $name }}</h1>

            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600">
                <a href="{{ route('cities.show', $city->slug) }}" class="inline-flex items-center gap-2 font-semibold text-slate-800 transition hover:text-[#C62E2E]">
                    <span class="text-base leading-none" aria-hidden="true">{{ $flag($city->country?->code) }}</span>
                    {{ $city->name }}@if ($city->country)<span class="font-normal text-slate-500">· {{ $city->country->name }}</span>@endif
                </a>
                @if ($images->count() > 1)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="size-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="10" r="1.6"/><path d="m21 16-5-5-9 8"/></svg>
                        {{ __('activity-show.photos', ['count' => $images->count()]) }}
                    </span>
                @endif
            </div>
        </div>
    </section>

    {{-- Alt bölümler panelden; "main" = galeri + içerik + rezervasyon --}}
    <x-page-sections page="activity" :activity="$activity">
        <x-slot:main>
            <section class="bg-white pb-14 pt-6 md:pb-20 md:pt-8">
                <div class="mx-auto max-w-7xl px-4">

                    {{-- GALERİ --}}
                    @if ($images->isNotEmpty())
                        {{-- telefon: kaydırmalı, sayaçlı --}}
                        <div class="-mx-4 md:hidden" x-data="{ i: 1, n: {{ $images->count() }} }">
                            <div class="relative">
                                <div x-ref="track" @scroll.throttle.80ms="i = Math.round($el.scrollLeft / $el.clientWidth) + 1"
                                    class="flex snap-x snap-mandatory overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                                    @foreach ($images as $image)
                                        <a href="{{ $image->url() }}" data-fancybox="activity-gallery" class="w-full shrink-0 snap-center">
                                            <img src="{{ $image->url() }}" alt="{{ $name }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async"
                                                @if ($image->width && $image->height) width="{{ $image->width }}" height="{{ $image->height }}" @endif
                                                class="aspect-[4/3] w-full object-cover">
                                        </a>
                                    @endforeach
                                </div>
                                @if ($images->count() > 1)
                                    <span class="pointer-events-none absolute bottom-3 right-4 rounded-full bg-slate-950/70 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur" x-text="i + ' / ' + n">1 / {{ $images->count() }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- masaüstü: mozaik --}}
                        <div class="relative hidden h-[460px] grid-cols-4 grid-rows-2 gap-2 overflow-hidden rounded-[28px] md:grid lg:h-[500px]">
                            @foreach ($mosaic as $image)
                                <a href="{{ $image->url() }}" data-fancybox="activity-gallery-desktop" class="group relative overflow-hidden bg-slate-100 {{ $spans[$loop->index] ?? '' }}">
                                    <img src="{{ $loop->first ? $image->url() : $image->url('sm') }}" alt="{{ $name }}" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async"
                                        class="h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.04]">
                                    <span class="absolute inset-0 bg-slate-950/0 transition group-hover:bg-slate-950/10"></span>
                                </a>
                            @endforeach
                            {{-- mozaiğe sığmayanlar da büyük görünümde gezilsin --}}
                            @foreach ($images->slice(5) as $image)
                                <a href="{{ $image->url() }}" data-fancybox="activity-gallery-desktop" class="hidden" aria-hidden="true" tabindex="-1"></a>
                            @endforeach
                            @if ($images->count() > 1)
                                <button type="button" data-open-gallery
                                    class="absolute bottom-4 right-4 inline-flex items-center gap-2 rounded-xl bg-white/95 px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-lg ring-1 ring-slate-200 backdrop-blur transition hover:bg-white rtl:left-4 rtl:right-auto">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/></svg>
                                    {{ __('activity-show.all_photos') }} · {{ $images->count() }}
                                </button>
                            @endif
                        </div>
                    @endif

                    <div class="mt-8 grid gap-10 md:mt-12 lg:grid-cols-12 lg:gap-12">

                        {{-- SOL: bilgi kartları + açıklama --}}
                        <div class="min-w-0 lg:col-span-8">
                            <dl @class(['grid gap-3', 'grid-cols-2 sm:grid-cols-4' => count($facts) === 4, 'grid-cols-3' => count($facts) === 3, 'grid-cols-2' => count($facts) <= 2])>
                                @foreach ($facts as $fact)
                                    <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/70">
                                        <svg class="size-5 text-[#C62E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $fact['icon'] !!}</svg>
                                        <dt class="mt-3 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ $fact['label'] }}</dt>
                                        <dd class="mt-0.5 truncate text-sm font-bold text-slate-900">{{ $fact['value'] }}</dd>
                                    </div>
                                @endforeach
                            </dl>

                            @if ($plainDescription !== '')
                                <div class="mt-10" x-data="{ open: false, long: false }" x-init="$nextTick(() => long = $refs.body.scrollHeight > 560)">
                                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ __('activity-show.about') }}</h2>
                                    <div class="relative mt-5">
                                        <div x-ref="body" class="rich-text overflow-hidden transition-[max-height] duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                            :style="long && !open ? 'max-height: 520px' : (long ? 'max-height: ' + $refs.body.scrollHeight + 'px' : '')">
                                            {!! \App\Support\BulletParagraphs::toList((string) $activity->description) !!}
                                        </div>
                                        <div x-show="long && !open" class="pointer-events-none absolute inset-x-0 bottom-0 h-32 bg-gradient-to-t from-white to-transparent"></div>
                                    </div>
                                    <button type="button" x-show="long" x-cloak @click="open = !open; if (!open) $el.closest('[x-data]').scrollIntoView({ behavior: 'smooth' })"
                                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                                        <span x-text="open ? @js(__('activity-show.read_less')) : @js(__('activity-show.read_more'))"></span>
                                        <svg class="size-4 transition" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                    </button>
                                </div>
                            @endif
                        </div>

                        {{-- SAĞ: rezervasyon kutusu (masaüstünde yapışkan) --}}
                        <aside class="lg:col-span-4">
                            <div class="space-y-4 lg:sticky lg:top-6">
                                <div class="overflow-hidden rounded-3xl bg-white shadow-[0_24px_60px_-30px_rgba(15,23,42,0.35)] ring-1 ring-slate-200">
                                    <div class="p-6">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-[#C62E2E]">{{ __('activity-show.book_eyebrow') }}</p>
                                        <p class="mt-2 line-clamp-2 text-lg font-extrabold leading-snug text-slate-900">{{ $name }}</p>

                                        @if ($activity->partner)
                                            <div class="mt-4 flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3 ring-1 ring-slate-200/70">
                                                <span class="text-xs font-medium text-slate-500">{{ __('activity-show.book_via') }}</span>
                                                @if ($partnerLogo)
                                                    <img src="{{ $partnerLogo }}" alt="{{ $activity->partner->name }}" class="ml-auto h-6 w-auto max-w-[9rem] object-contain rtl:ml-0 rtl:mr-auto">
                                                @else
                                                    <span class="ml-auto text-sm font-bold text-slate-900 rtl:ml-0 rtl:mr-auto">{{ $activity->partner->name }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        <a href="{{ $activity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener"
                                            class="group mt-5 flex items-center justify-center gap-2 rounded-2xl bg-[#C62E2E] px-6 py-4 text-base font-bold text-white shadow-[0_14px_30px_-12px_rgba(198,46,46,0.8)] transition hover:bg-[#A82424] active:scale-[0.99]">
                                            {{ __('activity-show.cta') }}
                                            <svg class="size-5 transition group-hover:-translate-y-0.5 group-hover:translate-x-0.5 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M9 7h8v8"/></svg>
                                        </a>
                                        <p class="mt-3 text-center text-xs leading-relaxed text-slate-500">{{ __('activities.redirect_notice') }}</p>
                                    </div>
                                    <ul class="space-y-3 border-t border-slate-100 bg-slate-50/60 px-6 py-5 text-sm text-slate-700">
                                        @foreach (['trust_official', 'trust_instant', 'trust_secure'] as $point)
                                            <li class="flex items-center gap-3">
                                                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-emerald-500 text-white">
                                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                                </span>
                                                {{ __('activity-show.' . $point) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                {{-- nasıl çalışır: satın alma partnerde --}}
                                <div class="rounded-3xl bg-slate-900 p-6 text-white">
                                    <p class="font-bold">{{ __('activity-show.how_title') }}</p>
                                    <ol class="mt-4 space-y-3">
                                        @foreach (['how_1', 'how_2', 'how_3'] as $step)
                                            <li class="flex items-start gap-3 text-sm text-white/80">
                                                <span class="grid size-6 shrink-0 place-items-center rounded-full bg-white/10 text-xs font-bold text-white ring-1 ring-white/20">{{ $loop->iteration }}</span>
                                                <span class="pt-0.5">{{ __('activities-page.' . $step, ['button' => __('activity-show.cta')]) }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>
        </x-slot:main>
    </x-page-sections>

    {{-- TELEFON: alttaki sabit rezervasyon çubuğu --}}
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 px-4 py-3 shadow-[0_-10px_30px_-12px_rgba(15,23,42,0.25)] backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-xl items-center gap-3">
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-bold text-slate-900">{{ $name }}</p>
                <p class="truncate text-xs text-slate-500">{{ $typeLabel }} · {{ $city->name }}</p>
            </div>
            <a href="{{ $activity->affiliate_url }}" target="_blank" rel="nofollow sponsored noopener"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#C62E2E] px-4 py-3 text-sm font-bold text-white transition active:scale-[0.98]">
                {{ __('activity-show.cta') }}
                <svg class="size-4 rtl:-scale-x-100" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M9 7h8v8"/></svg>
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0.36/dist/fancybox/fancybox.umd.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Fancybox) return;
            const options = { dragToClose: true, Thumbs: { type: 'classic' }, Toolbar: { display: { left: ['infobar'], right: ['close'] } } };
            Fancybox.bind('[data-fancybox="activity-gallery"]', options);
            Fancybox.bind('[data-fancybox="activity-gallery-desktop"]', options);
            // "Tüm fotoğraflar": galeriyi ilk resimden aç
            document.querySelector('[data-open-gallery]')?.addEventListener('click', () => {
                document.querySelector('[data-fancybox="activity-gallery-desktop"]')?.click();
            });
        });
    </script>
    @jsonld($activitySchema)
@endpush
