@extends('layouts.app')

@section('title',
    $pageContent?->getTranslation('meta_title', $locale) ??
    $city->getTranslation('name', $locale) .
    '
    City Guide - TripSpoiler')

@section('meta_description',
    $pageContent?->getTranslation('meta_description', $locale) ??
    'Meaningful, calm and curated
    city guides for ' .
    $city->getTranslation('name', $locale) .
    '.')

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
    @endpush

@section('content')
    <section class="relative overflow-hidden
            bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        {{-- SOFT GLOW --}}
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-0
               w-[520px] h-[520px]
               bg-[#C62E2E]/10 rounded-full blur-[160px]">
            </div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
               text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                City Guides
            </span>

            {{-- TITLE --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Get to know
                <span id="currentCityName" class="text-[#C62E2E]">
                    {{ $city->getTranslation('name', $locale) }}
                </span>
                before you go
            </h1>

            {{-- DESCRIPTION --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                Thoughtful guides to museums, neighbourhoods and calm experiences.
                TripSpoiler helps you understand a city beyond the highlights,
                so you arrive feeling prepared and confident.
            </p>

            {{-- CITY SELECT --}}
            <div class="mt-10 max-w-sm">

                <label class="block text-sm font-semibold text-slate-800 mb-1">
                    Viewing guides for
                    <span id="currentCityLabel" class="text-[#C62E2E]">
                        {{ $city->getTranslation('name', $locale) }}
                    </span>
                </label>

                <p class="text-xs text-slate-500 mb-3">
                    Change the city to explore another destination
                </p>

                <div class="relative group">
                    <select id="cityFilter"
                        class="w-full appearance-none
                       bg-white
                       border border-[#F3D6D1]
                       rounded-full
                       px-6 py-4 pr-14
                       text-slate-900 text-base
                       shadow-sm
                       transition
                       hover:shadow-md
                       focus:outline-none
                       focus:border-[#C62E2E]
                       focus:ring-4 focus:ring-[#C62E2E]/15">

                        @foreach ($cities as $c)
                            <option data-slug="{{ $c->getTranslation('slug', $locale) }}" @selected($c->id === $city->id)>
                                {{ $c->getTranslation('name', $locale) }}
                            </option>
                        @endforeach

                    </select>

                    {{-- CHEVRON --}}
                    <div
                        class="pointer-events-none absolute inset-y-0 right-5
                       flex items-center text-slate-400
                       transition group-focus-within:text-[#C62E2E]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                </div>

            </div>

        </div>
    </section>

    @php
        $html = $pageContent?->getTranslation('content', $locale);
    @endphp

    @if (!empty($html))
        <section class="py-16 bg-white">
            <div class="max-w-6xl mx-auto px-4">

                <div
                    class="
                    text-slate-700 leading-relaxed
                    [&_p]:mb-4
                    [&_ul]:list-disc [&_ul]:pl-6 [&_ul]:mb-4
                    [&_ol]:list-decimal [&_ol]:pl-6 [&_ol]:mb-4
                    [&_li]:mb-1
                ">
                    {!! $html !!}
                </div>

            </div>
        </section>
    @endif

    @if($pageImages->isNotEmpty())
        <section class="py-16 bg-[#FFF5F3]">
            <div class="max-w-6xl mx-auto px-4">

                <div class="relative">

                    {{-- MOBİL OKLAR --}}
                    <button
                        class="embla-city-prev md:hidden absolute left-2 top-1/2 -translate-y-1/2 
                        z-50 pointer-events-auto cursor-pointer
                        w-10 h-10 bg-white shadow-lg rounded-full
                        flex items-center justify-center
                        hover:scale-110 active:scale-95 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>

                    <button
                        class="embla-city-next md:hidden absolute right-2 top-1/2 -translate-y-1/2 
                        z-50 pointer-events-auto cursor-pointer
                        w-10 h-10 bg-white shadow-lg rounded-full
                        flex items-center justify-center
                        hover:scale-110 active:scale-95 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5l7 7-7 7" />
                        </svg>
                    </button>

                    {{-- DESKTOP OKLAR --}}
                    <div class="hidden md:flex justify-end mb-6 gap-2">
                        <button
                            class="embla-city-prev cursor-pointer
                            w-11 h-11 bg-white shadow-sm rounded-xl
                            flex items-center justify-center
                            hover:bg-slate-50 hover:scale-105 active:scale-95
                            transition border border-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>

                        <button
                            class="embla-city-next cursor-pointer
                            w-11 h-11 bg-white shadow-sm rounded-xl
                            flex items-center justify-center
                            hover:bg-slate-50 hover:scale-105 active:scale-95
                            transition border border-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-700"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    {{-- SLIDER --}}
                @if($pageImages->isNotEmpty())
                        <div class="embla-city overflow-hidden relative z-0">
                            <div class="embla__container flex">

                                @foreach ($pageImages as $image)
                                    <div class="embla__slide flex-[0_0_80%] md:flex-[0_0_25%] px-2">
                                        <a href="{{ route('images.view', $image->id) }}"
                                        data-fancybox="city-gallery"
                                        class="block relative z-0 group">

                                            <img src="{{ route('images.view', $image->id) }}"
                                                class="rounded-2xl object-cover h-72 w-full
                                                        transition-transform duration-500
                                                        group-hover:scale-105">
                                        </a>
                                    </div>
                                @endforeach

                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">

            <div class="grid md:grid-cols-2 gap-16 items-center">

                <!-- IMAGE STACK -->
                <div class="relative h-[500px]">

                    <img src="https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?q=80&w=1200"
                        class="absolute w-[75%] rounded-3xl shadow-2xl object-cover">

                    <img src="https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?q=80&w=1200"
                        class="absolute bottom-0 right-0 w-[75%] rounded-3xl shadow-2xl object-cover border-8 border-white">

                </div>

                <!-- TEXT -->
                <div>
                    <h2 class="text-4xl font-bold text-slate-900 mb-6">
                        The rhythm of {{ $city->getTranslation('name', $locale) }}
                    </h2>

                    <p class="text-lg text-slate-600 leading-relaxed mb-6">
                        It’s chaos and calm at the same time.
                        Rooftop sunsets. Ferry crossings.
                        Hidden courtyards.
                    </p>

                    <a href="#"
                        class="inline-block bg-[#C62E2E] text-white px-6 py-3 rounded-full font-semibold hover:scale-105 transition">
                        See Highlights
                    </a>
                </div>

            </div>

        </div>
    </section> --}}

@endsection


@push('scripts')
    <script src="https://unpkg.com/embla-carousel/embla-carousel.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
    <script>
        document.getElementById('cityFilter')
            .addEventListener('change', function() {
                this.disabled = true;
                this.classList.add('opacity-50');

                const slug = this.options[this.selectedIndex].dataset.slug;

                window.location.href = `/cities/${slug}`;
            });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // City select redirect
            const cityFilter = document.getElementById('cityFilter');
            if (cityFilter) {
                cityFilter.addEventListener('change', function() {
                    this.disabled = true;
                    this.classList.add('opacity-50');

                    const slug = this.options[this.selectedIndex].dataset.slug;
                    window.location.href = `/cities/${slug}`;
                });
            }

            // Embla init
            const emblaNode = document.querySelector('.embla-city');

            if (emblaNode && typeof EmblaCarousel !== 'undefined') {
                const embla = EmblaCarousel(emblaNode, {
                    loop: false,
                    align: 'start',
                    dragFree: false,
                    containScroll: "trimSnaps"
                });

                const nextBtns = document.querySelectorAll('.embla-city-next');
                const prevBtns = document.querySelectorAll('.embla-city-prev');

                nextBtns.forEach(btn => {
                    btn.addEventListener('click', () => embla.scrollNext());
                });

                prevBtns.forEach(btn => {
                    btn.addEventListener('click', () => embla.scrollPrev());
                });
            }
            // Fancybox bind
            if (typeof Fancybox !== 'undefined') {
                Fancybox.bind("[data-fancybox='city-gallery']", {
                    dragToClose: false,
                    Toolbar: {
                        display: ["close"]
                    }
                });
            }

        });
    </script>
@endpush
