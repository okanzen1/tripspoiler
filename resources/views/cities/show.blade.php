@extends('layouts.app')

@section('title', $pageContent?->getTranslation('meta_title', $locale) ?? $city->getTranslation('name', $locale) . ' ' .
    __('cities.title_suffix'))

@section('meta_description', $pageContent?->getTranslation('meta_description', $locale) ??
    __('cities.meta_description_prefix') . ' ' . $city->getTranslation('name', $locale) . '.')

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

        <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
               text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                {{ __('cities-hero.eyebrow') }}
            </span>

            {{-- TITLE --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ __('cities-hero.title_prefix') }}
                <span id="currentCityName" class="text-[#C62E2E]">
                    {{ $city->getTranslation('name', $locale) }}
                </span>
                {{ __('cities-hero.title_suffix') }}
            </h1>

            {{-- DESCRIPTION --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                {{ __('cities-hero.description') }}
            </p>

            {{-- CITY SELECT --}}
            <div class="mt-10 flex flex-col md:flex-row gap-6 md:items-end">

                {{-- CITY FILTER --}}
                <div class="max-w-sm w-full">

                    <label class="block text-sm font-semibold text-slate-800 mb-1">
                        {{ __('cities-hero.filter_label_prefix') }}
                        <span id="currentCityLabel" class="text-[#C62E2E]">
                            {{ $city->getTranslation('name', $locale) }}
                        </span>
                    </label>

                    <p class="text-xs text-slate-500 mb-3">
                        {{ __('cities-hero.filter_description') }}
                    </p>

                    <div class="relative group">
                        <select id="cityFilter"
                            class="w-full appearance-none
                            bg-white border border-[#F3D6D1]
                            rounded-full px-6 py-4 pr-14
                            text-slate-900 text-base
                            shadow-sm transition hover:shadow-md
                            focus:outline-none
                            focus:border-[#C62E2E]
                            focus:ring-4 focus:ring-[#C62E2E]/15">

                            @foreach ($cities as $c)
                                <option data-slug="{{ $c->getTranslation('slug', $locale) }}" @selected($c->id === $city->id)>
                                    {{ $c->getTranslation('name', $locale) }}
                                </option>
                            @endforeach

                        </select>

                        <div class="pointer-events-none absolute inset-y-0 right-5 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </div>
                    </div>

                </div>

                {{-- SEARCH --}}
                <div class="flex-1 w-full">
                    <x-search-modal full="true" />
                </div>

            </div>

        </div>
    </section>

    @if ($pageContent?->experienceCategories?->count())

        @foreach ($pageContent->experienceCategories as $category)
            @php
                $desc = $category->descriptions->first();
                $descriptionHtml = $desc?->getTranslation('description', $locale);
                $categoryName = $category->getTranslation('name', $locale);
            @endphp

            @if (!empty($descriptionHtml))
                @if ($categoryName === 'City Overview')
                    <section class="relative py-20 bg-white overflow-hidden">
                        <div class="max-w-7xl mx-auto px-4">

                            <div class="mb-3">
                                <span class="text-[11px] tracking-[0.25em] text-gray-500 uppercase">
                                    {{ __('cities.overview_label') }}
                                </span>
                            </div>

                            <h2 class="text-4xl md:text-5xl font-serif text-gray-900 leading-[1.1] mb-6">
                                {{ $city->getTranslation('name', $locale) }}{{ __('cities.overview_title_suffix') }}
                            </h2>

                            <div
                                class="prose !max-w-none
                                        prose-p:text-gray-700
                                        prose-p:leading-[1.7]
                                        prose-p:mb-4
                                        text-left">

                                {!! $descriptionHtml !!}

                            </div>

                        </div>
                    </section>
                @endif

                @if ($categoryName === 'History & Identity')
                    <section class="relative py-20 bg-white overflow-hidden">

                        <div class="max-w-7xl mx-auto px-4">

                            <!-- Label -->
                            <div class="mb-4">
                                <span class="text-[11px] tracking-[0.25em] text-gray-500 uppercase">
                                    {{ __('cities.history_label') }}
                                </span>
                            </div>

                            <!-- Heading -->
                            <h2 class="text-4xl md:text-5xl font-serif text-gray-900 leading-[1.1] mb-12">
                                {{ __('cities.history_title_prefix') }} {{ $city->getTranslation('name', $locale) }}
                            </h2>

                            @if ($city->slug == 'istanbul')
                                <!-- TOP IMAGE -->
                                <div class="mb-16 overflow-hidden rounded-3xl shadow-[0_30px_80px_rgba(0,0,0,0.18)]">
                                    <img src="{{ asset('images/cities/istanbul-history.png') }}"
                                        alt="{{ $city->getTranslation('name', $locale) }} {{ __('cities.skyline_suffix') }}"
                                        class="w-full h-[380px] md:h-[520px] object-cover">
                                </div>
                            @endif

                            <!-- CONTENT GRID -->
                            <div class="grid md:grid-cols-2 gap-16">

                                <!-- LEFT SIDE -->
                                <div
                                    class="prose !max-w-none
                                            prose-p:text-gray-700
                                            prose-p:leading-[1.85]
                                            prose-p:mb-6
                                            prose-strong:text-gray-900
                                            prose-headings:font-serif">

                                    {!! $descriptionHtml !!}

                                </div>

                                @if ($city->slug == 'istanbul')
                                    <!-- RIGHT SIDE VISUAL BLOCK -->
                                    <div class="relative">
                                        <div class="sticky top-32 space-y-8">

                                            <div class="border-l-4 border-[#C62E2E] pl-6">
                                                <p class="text-lg text-gray-900 font-serif leading-snug">
                                                    {{ __('cities.history_quote') }}
                                                </p>
                                            </div>

                                            <div class="text-gray-600 text-[15px] leading-relaxed">
                                                {{ __('cities.history_text') }}
                                            </div>

                                            <!-- CTA BUTTON (SİYAH ALANIN YERİ) -->
                                            <div class="pt-6">
                                                <a href="{{ route('blog.index', ['city_id' => $city->id]) }}"
                                                    class="inline-flex items-center justify-center gap-2
                                                        w-full sm:w-auto
                                                        px-7 py-3
                                                        rounded-full
                                                        bg-[#C62E2E]
                                                        text-white text-sm font-medium
                                                        shadow-md
                                                        hover:bg-[#a82222]
                                                        transition duration-300">

                                                    {{ __('cities.history_cta', ['city' => $city->getTranslation('name', $locale)]) }}
                                                    <span>→</span>
                                                </a>
                                            </div>

                                        </div>
                                    </div>
                                @endif

                            </div>

                        </div>
                    </section>
                @endif

                @if ($categoryName === 'Iconic Landmarks')
                    <section class="relative py-20 bg-white overflow-hidden">

                        <div class="max-w-7xl mx-auto px-4">

                            <!-- Header -->
                            <div class="mb-20 text-center">
                                <span class="text-[11px] tracking-[0.3em] text-gray-500 uppercase">
                                    {{ __('cities.landmarks_label') }}
                                </span>

                                <h2 class="mt-6 text-4xl md:text-5xl font-serif text-gray-900 leading-[1.05]">
                                    {{ __('cities.landmarks_title_prefix') }}
                                    <span class="text-[#C62E2E]">
                                        {{ $city->getTranslation('name', $locale) }}
                                    </span>
                                </h2>
                            </div>

                            <!-- Main Layout -->
                            <div class="grid lg:grid-cols-2 gap-16 items-center">

                                <!-- Left: Large Image -->
                                <div class="relative">
                                    <div class="overflow-hidden rounded-3xl shadow-[0_35px_90px_rgba(0,0,0,0.18)]">
                                        <img src="{{ asset('images/cities/' . $city->slug . '-landmarks.png') }}"
                                            alt="{{ $city->getTranslation('name', $locale) }} landmarks"
                                            class="w-full h-[420px] md:h-[520px] object-cover transition duration-700 hover:scale-105">
                                    </div>

                                    <!-- Architectural Accent Line -->
                                    <div
                                        class="absolute -bottom-6 -right-6 w-32 h-32 border-r-4 border-b-4 border-[#C62E2E] opacity-40">
                                    </div>
                                </div>

                                <!-- Right: Description -->
                                <div class="relative">

                                    <div class="border-l-4 border-[#C62E2E] pl-6 mb-8">
                                        <p class="text-xl font-serif text-gray-900 leading-snug">
                                            {{ __('cities.landmarks_quote') }}
                                        </p>
                                    </div>

                                    <div
                                        class="prose !max-w-none
                                                prose-p:text-gray-700
                                                prose-p:leading-[1.9]
                                                prose-p:mb-6
                                                prose-strong:text-gray-900
                                                prose-headings:font-serif">

                                        {!! $descriptionHtml !!}

                                    </div>

                                </div>

                            </div>

                        </div>
                    </section>
                @endif
            @endif
        @endforeach
    @endif

    @php
        $html = $pageContent?->getTranslation('content', $locale);
        $plainText = trim(strip_tags($html));
        $wordCount = str_word_count($plainText);
    @endphp

    @if (!empty($plainText))
        <section class="py-16 bg-white">
            <div class="max-w-7xl mx-auto px-4">

                <div x-data="{ expanded: {{ $wordCount > 80 ? 'false' : 'true' }} }" class="relative">
                    {{-- Content wrapper --}}
                    <div class="quill-content overflow-hidden"
                        :style="expanded
                            ?
                            'max-height: ' + $refs.content.scrollHeight +
                            'px; transition: max-height 0.6s cubic-bezier(0.4, 0, 0.2, 1)' :
                            'max-height: 320px; transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1)'"
                        x-ref="content">

                        {!! $html !!}
                    </div>

                    {{-- Fade gradient --}}
                    <div x-show="!expanded" x-transition:enter="transition-opacity duration-300"
                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="absolute bottom-8 left-0 right-0 h-28 bg-gradient-to-t from-white to-transparent pointer-events-none">
                    </div>

                    @if ($wordCount > 80)
                        <div class="mt-4 relative z-10">
                            <button @click="expanded = !expanded"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-[#C62E2E] hover:text-[#a02020] transition-colors duration-200">

                                <span
                                    x-text="expanded ? '{{ __('cities.see_less') }}' : '{{ __('cities.see_more') }}'"></span>

                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 transition-transform duration-500 ease-in-out"
                                    :class="expanded ? 'rotate-180' : 'rotate-0'" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">

                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>
                    @endif

                </div>

            </div>
        </section>
    @endif

    <x-reviews :source="'cities'" :source-id="null" :sectionHeader="true" :reviewSummary="true" :testimonials="true" :color="'bg-white'" />

    @if ($pageImages->isNotEmpty())
        <section class="py-20 bg-[#F7F9FB]">
            <div class="max-w-7xl mx-auto px-6">

                <div class="flex items-center justify-between mb-10">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                        {{ __('cities.gallery_title_prefix') }} {{ $city->getTranslation('name', $locale) }}
                    </h2>

                    <div class="hidden md:flex gap-3">
                        <button
                            class="embla-city-prev w-12 h-12 rounded-full bg-white shadow-sm hover:shadow-md transition flex items-center justify-center">
                            ←
                        </button>
                        <button
                            class="embla-city-next w-12 h-12 rounded-full bg-white shadow-sm hover:shadow-md transition flex items-center justify-center">
                            →
                        </button>
                    </div>
                </div>

                <div class="embla-city overflow-hidden">
                    <div class="embla__container flex">

                        @foreach ($pageImages as $image)
                            <div class="embla__slide flex-[0_0_75%] md:flex-[0_0_45%] lg:flex-[0_0_35%] pr-6">
                                <a href="{{ route('images.view', $image->id) }}" data-fancybox="city-gallery"
                                    class="group block relative overflow-hidden rounded-3xl">

                                    <img src="{{ route('images.view', $image->id) }}"
                                        class="w-full h-[420px] object-cover
                                                transition duration-700 ease-out
                                                group-hover:scale-105">

                                    <!-- Soft Overlay -->
                                    <div
                                        class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent opacity-0 group-hover:opacity-100 transition duration-500">
                                    </div>

                                    <!-- Caption -->
                                    <div
                                        class="absolute bottom-6 left-6 text-white opacity-0 group-hover:opacity-100 transition duration-500">
                                        <span class="text-sm tracking-wide uppercase">
                                            {{ $city->getTranslation('name', $locale) }}
                                        </span>
                                    </div>

                                </a>
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>
        </section>
    @endif

@endsection


@push('scripts')
    <script src="https://unpkg.com/embla-carousel/embla-carousel.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
    <script type="application/ld+json">
        {!! json_encode($citySchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script type="application/ld+json">
        {!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
