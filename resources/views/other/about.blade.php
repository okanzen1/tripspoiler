@extends('layouts.app')

@section('title', 'About TripSpoiler')
@section('meta_description',
    'TripSpoiler helps travelers discover museums, activities and city highlights with clear
    and useful travel guides.')

@section('content')

    {{-- HERO --}}
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                About TripSpoiler
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Your guide to museums,<br>
                <span class="text-[#C62E2E]">cities & great experiences</span>
            </h1>

            <p class="mt-4 text-slate-600 max-w-2xl">
                TripSpoiler is a travel discovery platform focused on museums,
                cultural attractions and memorable city experiences.
                Our goal is to make planning easier — and exploring more meaningful.
            </p>

        </div>
    </section>

    {{-- CONTENT --}}
    <section class="bg-white py-14">
        <div class="max-w-6xl mx-auto px-4 leading-relaxed text-slate-700 space-y-6">

            <p>
                When you travel, you want to spend your time enjoying the city —
                not getting lost in endless tabs, confusing ticket options or
                outdated advice. TripSpoiler was created to bring everything
                together in one clear, well-organized place.
            </p>

            <p>
                Here you’ll find curated guides to the world’s best museums,
                inspiring city highlights, cultural experiences and helpful
                travel tips — written to be practical, simple and genuinely useful.
            </p>

            <p>
                We focus especially on museums and cultural spots because they
                tell the real story of a city. From world-famous collections to
                lesser-known gems, our content helps you understand
                what is worth seeing and why.
            </p>

            <p>
                TripSpoiler also highlights activities, tours and passes
                that can enhance your trip — so you can plan confidently,
                compare options easily and choose what fits your style.
            </p>

            <p>
                Whether you’re visiting for a weekend or exploring for longer,
                our mission is simple:
                <strong>help you travel smarter, discover deeper and enjoy more.</strong>
            </p>

        </div>
    </section>

@endsection
