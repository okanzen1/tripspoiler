@extends('layouts.app')

@section('title', 'Activities - TripSpoiler')
@section('meta_description', 'Find the best activities, combo tours and city passes around the world.')

@section('content')

    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                Things to do & experiences
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Find the best activities<br>
                <span class="text-[#C62E2E]">in your city</span>
            </h1>

            <p class="mt-4 text-slate-600 max-w-xl">
                Choose a city to explore curated activities, guided tours, tickets and
                local experiences — carefully selected to help you plan smarter.
            </p>

            {{-- CITY SELECT --}}
            <div class="mt-8 max-w-sm">
                <label class="text-sm font-medium text-slate-700">
                    Select a city
                </label>

                <select
                    class="mt-2 w-full rounded-2xl border border-[#F3D6D1] bg-white px-4 py-3 text-slate-800 shadow-sm outline-none focus:border-[#C62E2E] transition">

                    <option selected disabled>Choose a city</option>
                    <option>Paris</option>
                    <option>Rome</option>
                    <option>London</option>
                    <option>Barcelona</option>
                    <option>Istanbul</option>

                </select>
            </div>

        </div>
    </section>



    {{-- ⭐ SEO INTRO TEXT --}}
    <section class="bg-white py-10">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900">
                Your guide to the best tours, passes and museum tickets
            </h2>

            <p class="mt-3 text-slate-600 max-w-3xl leading-relaxed">
                TripSpoiler helps you compare attractions, museum tickets, guided tours,
                combo experiences and city passes — all in one place. Whether you’re planning
                a short weekend escape or a long discovery trip, our curated activity guides
                help you make decisions with clarity and confidence.
            </p>

        </div>
    </section>



    {{-- CITY PASSES --}}
    <section class="bg-white pb-14">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900 mb-6">
                City Passes
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                {{-- PASS CARD --}}
                <div
                    class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=30" class="w-full h-40 object-cover">
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Paris Museum Pass
                        </h3>
                        <p class="text-sm text-slate-600 mt-2">
                            Unlimited access to 50+ museums and monuments.
                        </p>
                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View details →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- PASS CARD --}}
                <div
                    class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=31" class="w-full h-40 object-cover">
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Rome Tourist Card
                        </h3>
                        <p class="text-sm text-slate-600 mt-2">
                            Includes Colosseum entry, Vatican tickets & more.
                        </p>
                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View details →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- PASS CARD --}}
                <div
                    class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=32" class="w-full h-40 object-cover">
                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            London Explorer Pass
                        </h3>
                        <p class="text-sm text-slate-600 mt-2">
                            Pick your favourite attractions and save more.
                        </p>
                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View details →
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>



    {{-- 🚋 COMBO TOURS SLIDER --}}
    <section class="bg-[#FFF5F3] py-14 border-y border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4">

            {{-- TITLE + BUTTONS --}}
            <div class="flex items-center justify-between mb-6">

                <h2 class="text-xl font-bold text-slate-900">
                    Combo Tours
                </h2>

                <div class="flex gap-3">
                    <button id="prevBtn"
                        class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 active:scale-95 transition shadow-sm">
                        ←
                    </button>

                    <button id="nextBtn"
                        class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 active:scale-95 transition shadow-sm">
                        →
                    </button>
                </div>

            </div>


            <div class="relative overflow-hidden">

                <div id="comboTrack" class="flex gap-6 transition-transform ease-out duration-300">

                    {{-- SLIDE 1 --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=55" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900">
                                Vatican + Colosseum Combo Tour
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Two major Rome highlights in one guided tour.
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    View details →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDE 2 --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=56" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900">
                                Eiffel Tower + Seine Cruise
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Iconic Paris views from land and water.
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    View details →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDE 3 --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=57" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900">
                                Istanbul Bosphorus + City Tour
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Historic landmarks and scenic cruise experience.
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    View details →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDE 4 --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=58" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold text-slate-900">
                                London Eye + Thames Cruise
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Stunning city views from above and below.
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    View details →
                                </a>
                            </div>
                        </div>

                    </div>

                </div>

            </div>

        </div>
    </section>




    {{-- POPULAR ACTIVITIES --}}
    <section class="bg-white py-14">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900 mb-6">
                Popular Activities
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                {{-- CARD --}}
                <div
                    class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=10" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Louvre Museum Skip-the-Line Ticket
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            Discover masterpieces like the Mona Lisa with fast-track entry.
                        </p>

                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View details →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- CARD --}}
                <div
                    class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=11" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Colosseum Guided Tour
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            Step back into ancient Rome with an expert storyteller.
                        </p>

                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View details →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- CARD --}}
                <div
                    class="bg-white border border-[#F3D6D1] rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=12" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Seine River Cruise at Sunset
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            A relaxing boat experience with breathtaking city views.
                        </p>

                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View details →
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const track = document.getElementById('comboTrack');
            const next = document.getElementById('nextBtn');
            const prev = document.getElementById('prevBtn');

            const step = 340;
            let offset = 0;

            const maxOffset = -(track.scrollWidth - track.parentElement.clientWidth);

            next.addEventListener("click", () => {
                offset -= step;
                if (offset < maxOffset) offset = maxOffset;
                track.style.transform = `translateX(${offset}px)`;
            });

            prev.addEventListener("click", () => {
                offset += step;
                if (offset > 0) offset = 0;
                track.style.transform = `translateX(${offset}px)`;
            });

        });
    </script>
@endsection
