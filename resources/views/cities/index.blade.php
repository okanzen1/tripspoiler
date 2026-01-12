@extends('layouts.app')

@section('title', 'City Guides - TripSpoiler')
@section('meta_description', 'Meaningful, calm and curated city guides — created to help you plan smarter.')

@section('content')

    {{-- HERO (CITY CONCEPT) --}}
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-16 grid md:grid-cols-2 gap-10 items-center">

            {{-- LEFT --}}
            <div>

                <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                    City Guides
                </span>

                <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                    Get to know a city<br>
                    <span class="text-[#C62E2E]">before you step inside it</span>
                </h1>

                <p class="mt-4 text-slate-600 max-w-xl leading-relaxed">
                    TripSpoiler focuses on the parts of a city that truly matter:
                    museums, neighbourhoods, calm experiences and simple travel advice.
                    Our guides help you feel prepared — not overwhelmed.
                </p>

                {{-- SELECT --}}
                <div class="mt-8 max-w-sm">
                    <label class="text-sm font-medium text-slate-700">
                        Select a city
                    </label>

                    <select
                        class="mt-2 w-full rounded-2xl border border-[#F3D6D1] bg-white px-4 py-3 text-slate-900 shadow-sm outline-none focus:border-[#C62E2E] transition">

                        <option selected>Paris</option>
                        <option>Rome</option>
                        <option>London</option>
                        <option>Istanbul</option>

                    </select>
                </div>

            </div>


            {{-- RIGHT IMAGE --}}
            <div>
                <img src="https://images.unsplash.com/photo-1502602898657-3e91760cbb34?q=80&w=1200"
                    class="rounded-3xl shadow-lg border border-[#F3D6D1]" alt="City View">
            </div>

        </div>
    </section>




    {{-- SECTION — WHAT MAKES A CITY SPECIAL --}}
    <section class="bg-[#F7F9FB] py-14 border-b border-slate-200/30">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900">
                A calm way to understand a city
            </h2>

            <p class="mt-3 text-slate-600 max-w-3xl leading-relaxed">
                Instead of writing tourist checklists, we focus on context and meaning.
                Our city pages bring together museums, architecture, local culture,
                scenic viewpoints and real-world travel guidance — so your plans feel lighter,
                simpler and more intentional.
            </p>

        </div>
    </section>




    {{-- 3 FEATURE BLOCKS (CITY PHILOSOPHY) --}}
    <section class="bg-white py-14">
        <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-8">

            <div class="bg-white border border-[#E5EAF0] rounded-3xl shadow-sm p-6">
                <h3 class="font-semibold text-slate-900">
                    Museums with meaning
                </h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    We help you understand what makes each museum unique — so you choose
                    the ones that truly matter to you.
                </p>
            </div>

            <div class="bg-white border border-[#E5EAF0] rounded-3xl shadow-sm p-6">
                <h3 class="font-semibold text-slate-900">
                    Neighbourhood insights
                </h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Learn the character of each district — cafés, streets, rhythm and local life.
                </p>
            </div>

            <div class="bg-white border border-[#E5EAF0] rounded-3xl shadow-sm p-6">
                <h3 class="font-semibold text-slate-900">
                    Calm planning
                </h3>
                <p class="mt-2 text-sm text-slate-600 leading-relaxed">
                    Clear guidance without the noise — simple, helpful and human.
                </p>
            </div>

        </div>
    </section>


    {{-- IMAGE STRIP / VISUAL BREAK --}}
    <section class="bg-[#F7F9FB] py-10">
        <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-6">

            <img class="rounded-2xl shadow border border-[#E5EAF0] h-72 w-full object-cover"
                src="https://images.unsplash.com/photo-1501594907352-04cda38ebc29?auto=format&fit=crop&w=1200&q=80" />

            <img class="rounded-2xl shadow border border-[#E5EAF0] h-72 w-full object-cover"
                src="https://images.unsplash.com/photo-1471115853179-bb1d604434e0?auto=format&fit=crop&w=1200&q=80" />

            <img class="rounded-2xl shadow border border-[#E5EAF0] h-72 w-full object-cover"
                src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80" />

        </div>
    </section>


    {{-- CTA — DIRECT USERS TO CONTENT --}}
    <section class="bg-white py-14">
        <div class="max-w-6xl mx-auto px-4 grid md:grid-cols-3 gap-6">

            <a href="/museums"
                class="bg-white border border-[#E5EAF0] rounded-3xl shadow-sm p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-slate-900">Museums</h3>
                <p class="text-sm text-slate-600 mt-2">
                    Browse iconic collections and hidden gems.
                </p>
                <span class="text-sm font-semibold text-[#C62E2E] mt-3 inline-block">
                    View guides →
                </span>
            </a>

            <a href="/activities"
                class="bg-white border border-[#E5EAF0] rounded-3xl shadow-sm p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-slate-900">Activities</h3>
                <p class="text-sm text-slate-600 mt-2">
                    Meaningful things to do — not just busy schedules.
                </p>
                <span class="text-sm font-semibold text-[#C62E2E] mt-3 inline-block">
                    Explore →
                </span>
            </a>

            <a href="/blog" class="bg-white border border-[#E5EAF0] rounded-3xl shadow-sm p-6 hover:shadow-md transition">
                <h3 class="font-semibold text-slate-900">Travel Tips</h3>
                <p class="text-sm text-slate-600 mt-2">
                    Thoughtful reflections & calm planning advice.
                </p>
                <span class="text-sm font-semibold text-[#C62E2E] mt-3 inline-block">
                    Read more →
                </span>
            </a>

        </div>
    </section>

@endsection
