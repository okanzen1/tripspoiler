@extends('layouts.app')

@section('title', 'About TripSpoiler')
@section('meta_description',
    'TripSpoiler helps travelers discover museums, activities and city highlights with clear
    and useful travel guides.')

@section('content')
    {{-- ABOUT HERO — FULL WIDTH TEXT --}}
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
                About TripSpoiler
            </span>

            {{-- TITLE --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Your guide to cities, activities and stories<br>
                <span class="text-[#C62E2E]">
                    with museums that truly matter
                </span>
            </h1>

            {{-- DESCRIPTION — NO WIDTH LIMIT --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                TripSpoiler is a travel discovery platform focused on museums, cultural
                attractions and memorable city experiences. We help travelers plan
                with clarity, confidence and a deeper understanding of the places
                they visit.
            </p>

        </div>
    </section>

    {{-- CONTENT --}}
    <section class="bg-white py-14">
        <div class="max-w-6xl mx-auto px-4 leading-relaxed text-slate-700 space-y-6">

            <p>
                When you travel, your time matters. You want to experience the city itself,
                not struggle with endless tabs, confusing ticket options or outdated advice.
                TripSpoiler was created to make travel planning clear, focused and easy to navigate.
            </p>

            <p>
                Here you will find carefully curated guides covering cities, activities,
                museums and travel stories. Everything is written with one goal in mind:
                to be practical, simple and genuinely useful.
            </p>

            <p>
                Museums and cultural places play a central role in how we see a city.
                They reveal its history, identity and everyday life.
                From well known institutions to quieter local spaces,
                our guides help you understand what is truly worth your time.
            </p>

            <p>
                TripSpoiler also brings together activities, tours and city passes
                that can enrich your trip. We focus on clarity and comparison,
                so you can choose confidently and plan in a way that fits your travel style.
            </p>

            <p>
                Whether you are visiting for a short break or staying longer,
                our purpose is simple.
                <strong>To help you plan smarter, explore deeper and enjoy the journey more.</strong>
            </p>

        </div>
    </section>


@endsection
