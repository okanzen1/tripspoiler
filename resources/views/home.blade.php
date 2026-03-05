@extends('layouts.app')

@section('title', 'TripSpoiler - Travel Guides & Museum Tips')
@section('meta_description', 'Discover the best cities, museums, travel tips and hidden gems around the world.')

@section('content')
    {{-- HERO SECTION --}}
    <section class="relative overflow-hidden
                bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        {{-- VERY SOFT GLOW --}}
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-24 left-1/2 -translate-x-1/2
                    w-[520px] h-[520px]
                    bg-[#C62E2E]/10 rounded-full blur-[160px]">
            </div>
        </div>

        <div class="relative max-w-4xl mx-auto px-4 py-16 md:py-24 text-center">

            {{-- MAIN LOGO --}}
            <img src="{{ asset('images/logo-tripspoiler-two.png') }}" alt="TripSpoiler"
                class="mx-auto h-48 md:h-48 mb-6 drop-shadow-md logo-swing" />

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
                   text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                Travel, thoughtfully written
            </span>

            {{-- HEADLINE --}}
            <h1 class="mt-6 text-4xl md:text-6xl font-bold text-slate-900 leading-tight">
                TripSpoiler Travel Guides, Cities & Activities
            </h1>

            {{-- SUBTEXT --}}
            <p class="mt-6 text-base md:text-lg text-slate-600
                   max-w-2xl mx-auto leading-relaxed">
                TripSpoiler helps you plan your trip with confidence.
                Clear guides and blogs about cities, museums and experiences.
            </p>

            {{-- SEARCH --}}
            <div class="mt-10">
                <x-search-modal />
            </div>

            {{-- HERO FOOT LINKS (CENTERED & ICON BASED) --}}
            <div class="mt-10 flex flex-col items-center text-center">

                {{-- PRIMARY NAV --}}
                <div class="flex items-center justify-center gap-10 text-sm font-medium text-slate-700">

                    <a href="{{ url('/activities') }}" class="underline md:no-underline hover:text-[#C62E2E] transition">
                        Activities
                    </a>

                    <a href="{{ url('/cities') }}" class="underline md:no-underline hover:text-[#C62E2E] transition">
                        Cities
                    </a>

                    <a href="{{ url('/blog') }}" class="underline md:no-underline hover:text-[#C62E2E] transition">
                        Blog
                    </a>

                </div>

                {{-- DIVIDER --}}
                <div class="my-6 w-20 h-px bg-[#F3D6D1]"></div>

                {{-- SOCIAL ICONS --}}
                <div class="flex items-center justify-center gap-6 text-slate-500">

                    {{-- Instagram --}}
                    <a href="https://instagram.com/tripspoilerofficial" target="_blank" aria-label="Instagram"
                        class="hover:text-[#C62E2E] transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10z" />
                            <path d="M12 7.5A4.5 4.5 0 1016.5 12 4.51 4.51 0 0012 7.5z" />
                            <circle cx="17.5" cy="6.5" r="1" />
                        </svg>
                    </a>

                    {{-- TikTok --}}
                    <a href="https://tiktok.com/@tripspoilerofficial" target="_blank" aria-label="TikTok"
                        class="hover:text-[#C62E2E] transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M21 8.3a6.6 6.6 0 01-3.8-1.2v7.1a6.4 6.4 0 11-6.4-6.4c.4 0 .8 0 1.2.1v3.3a3.1 3.1 0 10 2.2 3v-12h3.2a6.6 6.6 0 003.6 3.1z" />
                        </svg>
                    </a>

                </div>

            </div>


        </div>
    </section>

    <x-steps-section />
    <x-most-popular-activities source="home" :source-id="null" limit="12" />
    <x-city-entry-section />
    <x-most-popular-blog source="home" :source-id="null" />
    <x-tripspoiler-intro />
    <x-social-presence-section />
    <x-faq source="home" :source-id="null" />
@endsection

