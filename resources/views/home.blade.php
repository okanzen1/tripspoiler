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

                {{-- SEARCH BOX --}}
                <div class="mt-6 max-w-xl">
                    <form>
                        <div class="flex bg-white rounded-2xl shadow-md border border-[#F3D6D1] overflow-hidden">
                            <input type="text" placeholder="Search cities or museums (Rome, Louvre, Paris...)"
                                class="w-full px-4 py-3 text-slate-900 outline-none text-sm">

                            <button class="px-6 text-white font-semibold bg-[#C62E2E] hover:bg-red-700 transition">
                                Search
                            </button>
                        </div>
                    </form>
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
                        <div class="p-4 rounded-2xl bg-[#FFF5F3]">
                            Museums
                        </div>
                        <div class="p-4 rounded-2xl bg-[#FFF5F3]">
                            Cities
                        </div>
                        <div class="p-4 rounded-2xl bg-[#FFF5F3]">
                            Travel Tips
                        </div>
                        <div class="p-4 rounded-2xl bg-[#FFF5F3]">
                            Blog
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>


    {{-- PAGE BODY BACKGROUND (LIGHT GREY) --}}
    {{-- <section class="bg-[#F7F9FB] py-10">
        <div class="max-w-6xl mx-auto px-4 text-slate-500 text-sm text-center">
            COMING SOON
        </div>
    </section> --}}

    @include('sections.museums-popular')
    @include('sections.cities-popular')
    @include('sections.blog-popular-newsletter')
    @include('partials.home.faq')

@endsection
