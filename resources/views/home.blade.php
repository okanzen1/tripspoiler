@extends('layouts.app')

@section('title', 'TripSpoiler - Travel Guides & Museum Tips')
@section('meta_description', 'Discover the best cities, museums, travel tips and hidden gems around the world.')

@section('content')

    {{-- HERO SECTION --}}
    <section class="bg-[#FFF5F3]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20 grid md:grid-cols-2 gap-12 items-center">

            {{-- LEFT SIDE --}}
            <div>

                <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                    Plan with confidence
                </span>

                <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                    Discover cities & museums<br>
                    <span class="text-[#C62E2E]">before you go.</span>
                </h1>

                <p class="mt-4 text-slate-600 max-w-md">
                    TripSpoiler exists to make travel decisions feel a little easier.
                    We bring together clear and trustworthy insights about cities,
                    museums, local experiences and reservation options — so you can
                    plan with confidence.
                </p>



                <div class="mt-6 max-w-xl">
                    <x-search-box />
                  
                </div>
            </div>

            {{-- RIGHT SIDE (LOGO + PANEL) --}}
            <div class="flex flex-col items-center">

                {{-- LOGO (SALLANAN) --}}
                <img src="{{ asset('images/logo-tripspoiler.png') }}" alt="TripSpoiler"
                    class="h-24 md:h-32 drop-shadow-xl logo-swing mb-6">

                {{-- PANEL --}}
                <div class="bg-white border border-[#F3D6D1] rounded-3xl shadow-lg p-6 md:p-10 w-full">

                    <div class="grid grid-cols-3 gap-4 text-center text-sm font-medium text-slate-700">

                        {{-- Activities --}}
                        <a href="{{ url('/activities') }}"
                            class="p-4 rounded-2xl bg-[#FFF5F3] hover:bg-[#FBE3DF] hover:shadow-md transition-all duration-200 hover:-translate-y-[1px]">
                            Activities
                        </a>

                        {{-- Museums --}}
                        <a href="{{ url('/museums') }}"
                            class="p-4 rounded-2xl bg-[#FFF5F3] hover:bg-[#FBE3DF] hover:shadow-md transition-all duration-200 hover:-translate-y-[1px]">
                            Museums
                        </a>

                        {{-- Cities --}}
                        <a href="{{ url('/cities') }}"
                            class="p-4 rounded-2xl bg-[#FFF5F3] hover:bg-[#FBE3DF] hover:shadow-md transition-all duration-200 hover:-translate-y-[1px]">
                            Cities
                        </a>

                        {{-- Blog --}}
                        <a href="{{ url('/blog') }}"
                            class="p-4 rounded-2xl bg-[#FFF5F3] hover:bg-[#FBE3DF] hover:shadow-md transition-all duration-200 hover:-translate-y-[1px]">
                            Blog
                        </a>

                        {{-- Travel Tips — passive --}}
                        <div class="p-4 rounded-2xl bg-[#FFF5F3] text-slate-800 border border-transparent">
                            Travel Tips
                        </div>

                        {{-- All Passes — passive --}}
                        <div class="p-4 rounded-2xl bg-[#FFF5F3] text-slate-800 border border-transparent">
                            All Passes
                        </div>

                    </div>


                </div>

            </div>

        </div>
    </section>

    @include('sections.museums-popular')
    <section class="bg-white">
        <div class="max-w-6xl mx-auto px-4 py-16">

            <div class="text-center mb-12">
                <h2 class="text-2xl md:text-3xl font-bold text-slate-900">
                    Plan smarter in 3 simple steps
                </h2>
                <p class="mt-3 text-slate-600 text-sm max-w-xl mx-auto">
                    No endless tabs, no confusing tickets.
                    Just clear choices before you travel.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mt-5">

                {{-- STEP 1 --}}
                <div class="bg-white rounded-3xl border border-[#F3D6D1] p-6 shadow-sm">
                    <div class="text-[#C62E2E] font-bold text-sm mb-2">
                        STEP 01
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">
                        Explore your destination
                    </h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Browse cities, museums and activities with
                        real context — not marketing fluff.
                    </p>
                </div>

                {{-- STEP 2 --}}
                <div class="bg-white rounded-3xl border border-[#F3D6D1] p-6 shadow-sm">
                    <div class="text-[#C62E2E] font-bold text-sm mb-2">
                        STEP 02
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">
                        Compare before you book
                    </h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        See what’s worth it, what’s overrated
                        and when skip-the-line actually helps.
                    </p>
                </div>

                {{-- STEP 3 --}}
                <div class="bg-white rounded-3xl border border-[#F3D6D1] p-6 shadow-sm">
                    <div class="text-[#C62E2E] font-bold text-sm mb-2">
                        STEP 03
                    </div>
                    <h3 class="font-semibold text-slate-900 mb-2">
                        Travel without second thoughts
                    </h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Book confidently knowing you’ve already
                        avoided common travel mistakes.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <x-most-popular-blog source="home" :source-id="null" />
    <x-faq source="home" :source-id="null" />

    @section('modals')
        <x-search-modal />
    @endsection
@endsection
