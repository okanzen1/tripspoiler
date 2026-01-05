@extends('layouts.app')

@section('title', 'Museums - TripSpoiler')
@section('meta_description', 'Discover top museums, exhibitions and cultural collections around the world.')

@section('content')

    {{-- HERO --}}
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                Art • History • Culture
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Explore the world’s most<br>
                <span class="text-[#C62E2E]">remarkable museums</span>
            </h1>

            <p class="mt-4 text-slate-600 max-w-xl">
                From iconic galleries to hidden cultural gems — discover exhibitions,
                masterworks and museum experiences curated for curious travellers.
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



    {{-- INTRO TEXT --}}
    <section class="bg-white py-10">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900">
                Discover museums worth travelling for
            </h2>

            <p class="mt-3 text-slate-600 max-w-3xl leading-relaxed">
                TripSpoiler highlights the most inspiring museums worldwide — from legendary
                collections to innovative contemporary spaces. Compare exhibitions, plan
                your visit and make the most out of every cultural experience.
            </p>

        </div>
    </section>



    {{-- FEATURED MUSEUMS --}}
    <section class="bg-white pb-14">
        <div class="max-w-6xl mx-auto px-4">

            <h2 class="text-xl font-bold text-slate-900 mb-6">
                Featured Museums
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

                {{-- CARD --}}
                <div
                    class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=80" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Louvre Museum — Paris
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            Home to masterpieces including the Mona Lisa & Venus de Milo.
                        </p>

                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View more →
                            </a>
                        </div>
                    </div>
                </div>


                <div
                    class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=81" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            British Museum — London
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            One of the world’s greatest collections of human history.
                        </p>

                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View more →
                            </a>
                        </div>
                    </div>
                </div>


                <div
                    class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden hover:shadow-lg transition">
                    <img src="https://picsum.photos/600/400?random=82" class="w-full h-40 object-cover">

                    <div class="p-5">
                        <h3 class="font-semibold text-slate-900">
                            Vatican Museums — Rome
                        </h3>

                        <p class="text-sm text-slate-600 mt-2">
                            Masterpieces of Renaissance art & the Sistine Chapel.
                        </p>

                        <div class="mt-4 flex items-center justify-end">
                            <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                View more →
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>




    {{-- 🎨 MUSEUM COLLECTIONS SLIDER --}}
    <section class="bg-[#FFF5F3] py-14 border-y border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4">

            {{-- TITLE + BUTTONS --}}
            <div class="flex items-center justify-between mb-6">

                <h2 class="text-xl font-bold text-slate-900">
                    Curated Collections
                </h2>

                <div class="flex gap-3">
                    <button id="mPrevBtn"
                        class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 active:scale-95 transition shadow-sm">
                        ←
                    </button>

                    <button id="mNextBtn"
                        class="px-3 py-2 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 active:scale-95 transition shadow-sm">
                        →
                    </button>
                </div>

            </div>


            <div class="relative overflow-hidden">

                <div id="museumTrack" class="flex gap-6 transition-transform ease-out duration-300">

                    {{-- SLIDE --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=83" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold">
                                Impressionist Highlights
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Monet • Degas • Renoir • Pissarro
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    Explore →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDE --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=84" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold">
                                Ancient Civilisations
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Egypt • Greece • Mesopotamia
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    Explore →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDE --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=85" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold">
                                Modern Art Icons
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Picasso • Kandinsky • Warhol
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    Explore →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- SLIDE --}}
                    <div
                        class="min-w-[280px] sm:min-w-[320px] bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
                        <img src="https://picsum.photos/600/400?random=86" class="h-40 w-full object-cover">
                        <div class="p-5">
                            <h3 class="font-semibold">
                                Renaissance Masterpieces
                            </h3>
                            <p class="text-sm text-slate-600 mt-2">
                                Leonardo • Michelangelo • Raphael
                            </p>
                            <div class="mt-4 flex justify-end">
                                <a href="#" class="text-sm font-semibold text-[#C62E2E] hover:underline">
                                    Explore →
                                </a>
                            </div>

                        </div>
                    </div>

                </div>
    </section>


@endsection



@section('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const track = document.getElementById('museumTrack');
            const next = document.getElementById('mNextBtn');
            const prev = document.getElementById('mPrevBtn');

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
