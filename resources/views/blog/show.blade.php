@extends('layouts.app')

@section('title', $blog->getTranslation('meta_title', $locale))
@section('meta_description', $blog->getTranslation('meta_description', $locale))

@push('styles')
    <style>
        html {
            scroll-behavior: smooth;
        }

        #activitiesSection {
            scroll-margin-top: 100px;
        }
    </style>
@endpush
@php
    $coverImage = $blog->images->first();

    $articleSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        '@id' => url()->current() . '#article',

        'headline' => $blog->getTranslation('title', $locale),

        'description' => $blog->getTranslation('meta_description', $locale),

        'image' => [
            '@type' => 'ImageObject',
            'url' => $coverImage ? route('images.view', $coverImage->id) : asset('android-chrome-512x512.png'),
        ],

        'author' => [
            '@type' => 'Organization',
            'name' => 'TripSpoiler',
            'url' => 'https://tripspoiler.com',
        ],

        'publisher' => [
            '@type' => 'Organization',
            'name' => 'TripSpoiler',
            'url' => 'https://tripspoiler.com',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => asset('android-chrome-512x512.png'),
            ],
        ],

        'datePublished' => $blog->created_at->toIso8601String(),
        'dateModified' => $blog->updated_at->toIso8601String(),

        'inLanguage' => $locale,

        'url' => url()->current(),

        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => url()->current(),
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => __('blog.home'),
                'item' => url('/'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => __('blog.blog'),
                'item' => route('blog.index'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 3,
                'name' => $blog->getTranslation('title', $locale),
                'item' => url()->current(),
            ],
        ],
    ];
@endphp

@section('content')

    {{-- BLOG DETAIL HERO --}}
    <section class="relative overflow-hidden
                bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24 space-y-4">

            @if (!empty($hero['themes']))
                <span
                    class="inline-block text-xs font-semibold tracking-wide uppercase
                         text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                    {{ implode(' • ', $hero['themes']) }}
                </span>
            @endif

            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ $hero['title']['first'] }}

                @if (!empty($hero['title']['second']))
                    @if ($hero['title']['break'])
                        <br>
                    @endif
                    <span class="text-[#C62E2E]">
                        {{ $hero['title']['second'] }}
                    </span>
                @endif
            </h1>

            @if (!empty($hero['excerpt']))
                <p class="text-slate-600 text-base md:text-lg leading-relaxed">
                    {{ $hero['excerpt'] }}
                </p>
            @endif

        </div>
    </section>

    @if ($blog->activities->count() === 1)
        <section id="activitiesSection" class="bg-white">
            <div class="max-w-7xl mx-auto px-4">

                <!-- Feature Card -->
                <div class="relative rounded-3xl overflow-hidden group">

                    <!-- Background Image -->
                    <img src="{{ $blog->activities->first()->images->isNotEmpty()
                        ? route('images.view', $blog->activities->first()->images->first()->id)
                        : asset('images/placeholder.jpg') }}"
                        alt="{{ $blog->activities->first()->name }}"
                        class="w-full h-[420px] object-cover transition duration-700 group-hover:scale-105">

                    <!-- Dark Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent"></div>

                    <!-- Content -->
                    <div class="absolute bottom-0 p-10 text-white max-w-2xl">

                        <span class="text-sm uppercase tracking-wide text-white/80">
                            {{ $blog->activities->first()->city->name }},
                            {{ $blog->activities->first()->city->country->name }}
                        </span>

                        <h3 class="text-2xl md:text-3xl font-semibold mt-2">
                            {{ $blog->activities->first()->name }}
                        </h3>

                        <a href="{{ $blog->activities->first()->affiliate_link }}" target="_blank"
                            class="mt-6 inline-block bg-white text-slate-900 px-6 py-3 rounded-full text-sm font-semibold hover:bg-[#C62E2E] hover:text-white transition">
                            {{ __('blog.explore_experience') }}
                        </a>

                    </div>

                </div>

            </div>
        </section>
    @endif

    @if ($blog->activities->count() > 1)
        <section id="activitiesSection" class="bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-4">

                <!-- HEADER -->
                <div class="flex items-center justify-end mb-12">

                    <!-- Desktop Arrows -->
                    <div class="hidden md:flex gap-3">
                        <div
                            class="swiper-button-prev-custom w-11 h-11 flex items-center justify-center
                            rounded-full border bg-white hover:bg-slate-100 shadow cursor-pointer">
                            ←
                        </div>

                        <div
                            class="swiper-button-next-custom w-11 h-11 flex items-center justify-center
                            rounded-full border bg-white hover:bg-slate-100 shadow cursor-pointer">
                            →
                        </div>
                    </div>

                </div>

                <div class="swiper premiumSwiper">
                    <div class="swiper-wrapper">
                        @foreach ($blog->activities as $activity)
                            <div class="swiper-slide">
                                <div class="relative rounded-3xl overflow-hidden group cursor-pointer">

                                    <img src="{{ $activity->images->isNotEmpty() ? route('images.view', $activity->images->first()->id) : asset('images/placeholder.jpg') }}"
                                        alt="{{ $activity->name }}"
                                        class="w-full h-[520px] object-cover transition duration-700 group-hover:scale-105">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                                    <div class="absolute bottom-0 p-8 text-white">
                                        <span class="text-sm uppercase tracking-wide text-white/80">
                                            {{ $activity->city->name }},
                                            {{ $activity->city->country->name }}
                                        </span>

                                        <h3 class="text-2xl md:text-3xl font-semibold mt-2">
                                            {{ $activity->name }}
                                        </h3>

                                        <a href="{{ $activity->affiliate_link }}" target="_blank"
                                            class="mt-6 inline-block bg-white text-slate-900 px-6 py-3 rounded-full text-sm font-semibold hover:bg-[#C62E2E] hover:text-white transition">
                                            {{ __('blog.explore_experience') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </section>
    @endif

    <section class="bg-white py-10">
        <div class="max-w-7xl mx-auto px-4">
            @foreach ($blog->contents as $content)
                <h2 class="text-xl font-bold text-slate-900">
                    {{ $content->getTranslation('title', $locale) }}
                </h2>

                <p class="mt-3 text-slate-600 max-w-3xl leading-relaxed">
                    {!! $content->getTranslation('content', $locale) !!}
                </p>
            @endforeach
        </div>
    </section>
    
    <x-reviews :source="'blog-show'" :source-id="$blog->id" :sectionHeader="true" :reviewSummary="true" :testimonials="true" :color="'bg-white'" />
    <x-faq source="blog-content" :source-id="$blog->id" />

    <!-- MOBILE STICKY BAR -->
    <div id="mobileStickyBar"
        class="lg:hidden fixed bottom-0 left-0 right-0 z-50  translate-y-full transition-transform duration-500">
        <div
            class="mx-4 mb-4 bg-white shadow-[0_10px_40px_rgba(0,0,0,0.15)] rounded-2xl border border-[#F3D6D1] p-4 flex items-center justify-between">
            @if ($blog->activities->count() === 1)
                <div class="pr-3">
                    <div class="text-xs text-slate-500">
                        {{ __('blog.editors_pick') }}
                    </div>
                    <div class="text-sm font-semibold text-slate-900 truncate max-w-[180px]">
                        {{ $blog->activities->first()->name }}
                    </div>
                </div>
            @endif

            @if ($blog->activities->count() > 1)
                <div class="pr-3">
                    <div class="text-xs text-slate-500">
                        {{ __('blog.editors_picks') }}
                    </div>
                    <div class="text-sm font-semibold text-slate-900 truncate max-w-[180px]">
                        {{ $blog->activities->count() }} {{ __('blog.experiences') }}
                    </div>
                </div>
            @endif

            @if ($blog->activities->count() === 1)
            <a href="{{ $blog->activities->first()->affiliate_link }}" target="_blank" @else <a
                    href="#activitiesSection" @endif
                    class="bg-[#C62E2E] text-white text-xs font-semibold px-4 py-2 rounded-full whitespace-nowrap">
                    {{ __('blog.view') }}
                </a>

        </div>
    </div>

    <div id="stickyCtaBar"
        class="hidden md:block fixed bottom-0 left-0 right-0 z-50 translate-y-full transition-transform duration-500">

        <div class="bg-white border-t border-[#F3D6D1] shadow-[0_-10px_40px_rgba(0,0,0,0.08)]">

            <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">

                <!-- LEFT -->
                <div class="flex items-center gap-4">
                    @if ($blog->activities->count() === 1)
                        <!-- Desktop Thumbnail -->
                        <img src="{{ $blog->activities->first()->images->isNotEmpty()
                            ? route('images.view', $blog->activities->first()->images->first()->id)
                            : asset('images/placeholder.jpg') }}"
                            alt="{{ $blog->activities->first()->name }}"
                            class="hidden md:block w-14 h-14 object-cover rounded-lg">

                        <div>
                            <div class="text-xs text-slate-500 uppercase tracking-wide">
                                {{ __('blog.editors_pick') }}
                            </div>

                            <div class="text-sm md:text-base font-semibold text-slate-900">
                                {{ $blog->activities->first()->name }}
                            </div>
                        </div>
                    @endif

                    @if ($blog->activities->count() > 1)
                        <div>
                            <div class="text-xs text-slate-500 uppercase tracking-wide">
                                {{ __('blog.editors_picks') }}
                            </div>

                            <div class="text-sm md:text-base font-semibold text-slate-900">
                                {{ $blog->activities->count() }} {{ __('blog.experiences') }}
                            </div>
                        </div>
                    @endif

                </div>

                <!-- RIGHT -->
                @if ($blog->activities->count() === 1)
                <a href="{{ $blog->activities->first()->affiliate_link }}" target="_blank" @else <a
                        href="#activitiesSection" @endif
                        class="bg-[#C62E2E] text-white text-sm font-semibold px-6 py-2.5 rounded-full hover:opacity-90 transition">
                        {{ __('blog.view') }}
                    </a>

            </div>

        </div>

    </div>


@endsection
@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const mobileBar = document.getElementById("mobileStickyBar");
            const desktopBar = document.getElementById("stickyCtaBar");

            window.addEventListener("scroll", function() {

                const show = window.scrollY > 600;

                mobileBar?.classList.toggle("translate-y-full", !show);
                desktopBar?.classList.toggle("translate-y-full", !show);

            });

        });
    </script>
    
    <script type="application/ld+json">
        {!! json_encode($articleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
