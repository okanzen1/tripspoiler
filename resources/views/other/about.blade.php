@extends('layouts.app')

@section('title', __('about.meta_title'))
@section('meta_description', __('about.meta_description'))

{{--
    Hakkımızda: büyük fotoğraf kartı (panel: Sayfalar › Hakkımızda › Hero alanı), hikâye, nasıl çalışır, ilkeler ve kapanış.
    Bütün yazılar panelden (Sayfalar › Hakkımızda › Sayfa içeriği); boşsa dil dosyası.
--}}
@php
    $hero = \App\Support\PageHero::texts('about', [
        'eyebrow' => __('about-page.eyebrow'),
        'title' => __('about-page.title'),
        'description' => __('about-page.desc'),
    ]);
    $look = \App\Support\PageHero::visual('about');
    ['desktop' => $photo, 'mobile' => $photoMobile] = \App\Models\PageBanner::urls('about', null);
    $stats = array_values(array_filter([
        [\App\Models\City::visible()->count(), __('about-page.stat_cities')],
        [\App\Models\Activity::visible()->count(), __('about-page.stat_activities')],
        [\App\Models\Blog::published()->count(), __('about-page.stat_guides')],
    ], fn ($s) => $s[0] > 0));
    $overlay = ['soft' => 'from-slate-950/70 via-slate-950/20', 'medium' => 'from-slate-950/90 via-slate-950/40', 'strong' => 'from-slate-950 via-slate-950/70'][$look['overlay']];
@endphp

@section('content')
    {{-- BANNER: beyaz sayfada büyük yuvarlak fotoğraf kartı; yazı sol altta, sayılar beyaz haplar --}}
    <section class="bg-white pb-6 pt-6 md:pt-8">
        <div class="mx-auto max-w-7xl px-4">
            <x-breadcrumbs :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('footer.about'), 'url' => route('about')],
            ]" />

            <div class="relative isolate mt-6 flex min-h-[560px] flex-col justify-end overflow-hidden rounded-[36px] bg-slate-900 p-6 text-white sm:p-10 md:min-h-[620px] lg:p-14">
                <picture class="absolute inset-0 -z-10">
                    @if ($photoMobile)<source media="(max-width: 767px)" srcset="{{ $photoMobile }}">@endif
                    <img src="{{ $photo }}" alt="" fetchpriority="high" class="h-full w-full object-cover" style="object-position: {{ $look['focus'] }}% center">
                </picture>
                <div class="absolute inset-0 -z-10 bg-gradient-to-t {{ $overlay }} to-transparent"></div>

                <div class="max-w-2xl">
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3.5 py-1.5 text-xs font-bold uppercase tracking-[0.16em] backdrop-blur">
                        <span class="size-1.5 rounded-full bg-[#FF6B6E]"></span>{{ $hero['eyebrow'] }}
                    </span>
                    <h1 class="mt-5 text-[44px] font-extrabold leading-[0.98] tracking-[-0.04em] sm:text-6xl lg:text-[72px]">
                        {{ $hero['title'] }} <span class="text-[#FF8A8C]">{{ __('about-page.title_sub') }}</span>
                    </h1>
                    <p class="mt-5 max-w-xl text-base leading-relaxed text-white/80 md:text-lg">{{ $hero['description'] }}</p>
                </div>

                @if ($stats)
                    <dl class="mt-8 flex flex-wrap gap-3">
                        @foreach ($stats as [$value, $label])
                            <div class="inline-flex items-baseline gap-2 rounded-2xl bg-white px-5 py-3 text-slate-900 shadow-lg">
                                <dd class="text-2xl font-extrabold tabular-nums tracking-tight">{{ $value }}</dd>
                                <dt class="text-sm font-medium text-slate-500">{{ $label }}</dt>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </div>
    </section>

    {{-- Bölümler: hikâye, nasıl çalışır, ilkeler, kapanış + diğerleri; sıra ve aç/kapa panelde (Sayfalar › Hakkımızda › Bölümler) --}}
    {{-- yapısal veri: Hakkımızda sayfası, konusu şirketin kendisi --}}
    @jsonld(\App\Support\Schema::webPage('AboutPage', __('about.meta_title'), __('about.meta_description'), ['mainEntity' => \App\Support\Schema::organizationRef(), 'about' => \App\Support\Schema::organizationRef()]))

    <x-page-sections page="about" />
@endsection
