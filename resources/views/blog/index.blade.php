@extends('layouts.app')

@section('title', 'Travel Blog & Guides - TripSpoiler')
@section('meta_description', 'Read the latest travel guides, museum tips, city advice and inspiration from around the
    world.')

@section('content')

    {{-- HERO --}}
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                Travel stories & inspiration
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Your go-to travel blog<br>
                <span class="text-[#C62E2E]">for museums & cities</span>
            </h1>

            <p class="mt-4 text-slate-600 max-w-xl">
                Practical guides, museum tips, hidden gems and honest insights —
                written to help you plan smarter and travel better.
            </p>


            {{-- CITY FILTER --}}
            <div class="mt-8 max-w-sm">
                <label class="text-sm font-medium text-slate-700">
                    Filter by city
                </label>

                <select
                    class="mt-2 w-full rounded-2xl border border-[#F3D6D1] bg-white px-4 py-3 text-slate-800 shadow-sm outline-none focus:border-[#C62E2E] transition">

                    <option selected>All cities</option>
                    <option>Paris</option>
                    <option>Rome</option>
                    <option>London</option>
                    <option>Barcelona</option>
                    <option>Istanbul</option>

                </select>
            </div>

        </div>
    </section>



    {{-- SEO INTRO --}}
    <section class="bg-white py-10">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900">
                Museum-focused travel insights you can trust
            </h2>

            <p class="mt-3 text-slate-600 max-w-3xl leading-relaxed">
                At TripSpoiler, we look at cities through the lens of culture and museums.
                Our blog explores the best exhibitions, smart ticket options, city passes,
                crowd-avoiding strategies and genuinely useful travel advice — so you can
                enjoy your trip instead of worrying about logistics.
            </p>

        </div>
    </section>



    {{-- FEATURED POSTS --}}
    <section class="bg-white pb-6">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900 mb-6">
                Featured Posts
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- CARD --}}
                <a href="#"
                    class="block bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/800/500?random=100" class="h-44 w-full object-cover">

                    <div class="p-6">
                        <span class="text-xs font-semibold text-[#C62E2E]">
                            Paris
                        </span>

                        <h3 class="font-semibold text-slate-900 mt-2">
                            Louvre Museum — Everything you need before visiting
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            From ticket options to best visiting times, a complete guide…
                        </p>
                    </div>
                </a>

                {{-- CARD --}}
                <a href="#"
                    class="block bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/800/500?random=101" class="h-44 w-full object-cover">

                    <div class="p-6">
                        <span class="text-xs font-semibold text-[#C62E2E]">
                            Rome
                        </span>

                        <h3 class="font-semibold text-slate-900 mt-2">
                            Vatican Museums — Skip-the-line or guided tour?
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            Which option actually makes the most sense?
                        </p>
                    </div>
                </a>

                {{-- CARD --}}
                <a href="#"
                    class="block bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/800/500?random=102" class="h-44 w-full object-cover">

                    <div class="p-6">
                        <span class="text-xs font-semibold text-[#C62E2E]">
                            London
                        </span>

                        <h3 class="font-semibold text-slate-900 mt-2">
                            Free museums in London you shouldn’t miss
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            Culture doesn’t always need a ticket…
                        </p>
                    </div>
                </a>

            </div>

        </div>
    </section>



    {{-- BLOG LIST GRID --}}
    <section class="bg-white py-10">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900 mb-6">
                Latest Articles
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                @for ($i = 1; $i <= 9; $i++)
                    <a href="#"
                        class="block bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">

                        <img src="https://picsum.photos/600/400?random={{ $i + 400 }}"
                            class="h-40 w-full object-cover">

                        <div class="p-5">
                            <span class="text-xs font-semibold text-[#C62E2E]">
                                Museum Tips
                            </span>

                            <h3 class="font-semibold text-slate-900 mt-2">
                                Sample article title number {{ $i }}
                            </h3>

                            <p class="text-sm text-slate-600 mt-2">
                                Short preview text about the topic to encourage reading…
                            </p>

                        </div>
                    </a>
                @endfor

            </div>

        </div>
    </section>

@endsection
