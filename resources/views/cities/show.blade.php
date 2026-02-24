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

    {{-- BURASI ARTIK ŞEHİR İÇERİĞİ --}}
    <section class="py-16 bg-white">
        <div class="max-w-6xl mx-auto px-4">

            {{-- ÖRNEK İÇERİK ALANI --}}
            <h2 class="text-xl font-bold text-slate-900 mb-4">
                About {{ $city->getTranslation('name', $locale) }}
            </h2>

            <p class="text-slate-600 leading-relaxed">
                {{ $city->description ?? 'City content will appear here.' }}
            </p>

        </div>
    </section>

    <section class="py-28 bg-white">
        <div class="max-w-6xl mx-auto px-4 relative">

            <div class="grid md:grid-cols-2 gap-20 items-center">

                <!-- IMAGE -->
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?q=80&w=1600"
                        class="rounded-3xl shadow-2xl object-cover">

                    <!-- FLOATING CARD -->
                    <div class="absolute -bottom-10 -right-10 bg-white p-6 rounded-2xl shadow-xl w-64 hidden md:block">
                        <p class="text-sm text-slate-500">Best time to visit</p>
                        <p class="text-xl font-bold text-[#C62E2E] mt-2">April – June</p>
                    </div>
                </div>

                <!-- TEXT -->
                <div>
                    <h2 class="text-4xl font-bold text-slate-900 mb-6">
                        Why {{ $city->getTranslation('name', $locale) }} feels different
                    </h2>

                    <p class="text-slate-600 leading-relaxed mb-6">
                        It’s not just monuments. It’s atmosphere. It’s light.
                        It’s the rhythm of ferries crossing the water.
                    </p>

                    <a href="#"
                        class="inline-block bg-[#C62E2E] text-white px-6 py-3 rounded-full font-semibold hover:opacity-90 transition">
                        Explore Experiences
                    </a>
                </div>

            </div>

        </div>
    </section>

    <section class="py-28 bg-[#FFF5F3]">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-4xl font-bold text-slate-900 mb-16">
                Featured Experiences
            </h2>

            <div class="grid md:grid-cols-2 gap-10">

                <!-- BIG CARD -->
                <div class="relative rounded-3xl overflow-hidden group">

                    <img src="https://images.unsplash.com/photo-1553913861-c0fddf2619ee?q=80&w=2000"
                        class="w-full h-[400px] object-cover transition group-hover:scale-105 duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

                    <div class="absolute bottom-0 p-8 text-white">
                        <h3 class="text-2xl font-bold mb-4">
                            Hagia Sophia & Blue Mosque
                        </h3>

                        <p class="text-white/80 mb-6">
                            Skip lines. Understand history. Visit smarter.
                        </p>

                        <a href="#" class="font-semibold underline">
                            Read Guide →
                        </a>
                    </div>

                </div>

                <!-- SECOND CARD -->
                <div class="relative rounded-3xl overflow-hidden group">

                    <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=2000"
                        class="w-full h-[400px] object-cover transition group-hover:scale-105 duration-500">

                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>

                    <div class="absolute bottom-0 p-8 text-white">
                        <h3 class="text-2xl font-bold mb-4">
                            Bosphorus Sunset Cruise
                        </h3>

                        <p class="text-white/80 mb-6">
                            Experience the city from the water.
                        </p>

                        <a href="#" class="font-semibold underline">
                            View Details →
                        </a>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <section class="py-16 bg-[#FFF5F3]">
        <div class="max-w-6xl mx-auto px-4">

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                <img src="https://images.unsplash.com/photo-1508672019048-805c876b67e2?q=80&w=1000"
                    class="rounded-2xl object-cover h-60 w-full">

                <img src="https://images.unsplash.com/photo-1553913861-c0fddf2619ee?q=80&w=1000"
                    class="rounded-2xl object-cover h-60 w-full">

                <img src="https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?q=80&w=1000"
                    class="rounded-2xl object-cover h-60 w-full">

                <img src="https://images.unsplash.com/photo-1541432901042-2d8bd64b4a9b?q=80&w=1000"
                    class="rounded-2xl object-cover h-60 w-full">

            </div>

        </div>
    </section>

    <section class="py-28 bg-white">
        <div class="max-w-6xl mx-auto px-4">

            <div class="relative rounded-3xl overflow-hidden group">

                <img src="https://images.unsplash.com/photo-1553913861-c0fddf2619ee?q=80&w=2000"
                    class="w-full h-[500px] object-cover transition duration-700 group-hover:scale-105">

                <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>

                <div class="absolute bottom-0 p-12 text-white max-w-xl">
                    <h3 class="text-4xl font-bold mb-6">
                        Experience Hagia Sophia differently
                    </h3>

                    <p class="text-white/80 text-lg mb-8">
                        Skip the chaos. Understand the history.
                        Visit at the right time.
                    </p>

                    <a href="#" class="bg-[#C62E2E] px-6 py-3 rounded-full font-semibold">
                        View Guide
                    </a>
                </div>

            </div>

        </div>
    </section>

    <section class="grid md:grid-cols-2">

        <div class="bg-slate-900 text-white flex items-center p-16">
            <div>
                <h2 class="text-4xl font-bold mb-6">
                    European Side
                </h2>
                <p class="text-white/80 text-lg">
                    Imperial mosques, grand bazaars and historic skyline.
                </p>
            </div>
        </div>

        <div class="relative h-[400px] md:h-auto">
            <img src="https://images.unsplash.com/photo-1508672019048-805c876b67e2?q=80&w=2000"
                class="absolute inset-0 w-full h-full object-cover">
        </div>

    </section>

    <section class="py-24 bg-[#C62E2E] text-white text-center">
        <div class="max-w-3xl mx-auto px-6">

            <h2 class="text-4xl md:text-5xl font-extrabold">
                Don’t just visit.
                <br>
                Feel it.
            </h2>

        </div>
    </section>

    <section class="py-28 bg-white">
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
    </section>

@endsection


@push('scripts')
    <script>
        document.getElementById('cityFilter')
            .addEventListener('change', function() {

                // 🔹 Select'i disable et
                this.disabled = true;
                this.classList.add('opacity-50');

                const slug = this.options[this.selectedIndex].dataset.slug;

                window.location.href = `/cities/${slug}`;
            });
    </script>
@endpush
