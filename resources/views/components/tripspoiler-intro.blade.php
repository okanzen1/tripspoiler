<section class="py-20 bg-[#FFFAF9] relative overflow-hidden">

    {{-- SOFT GLOW --}}
    <div class="absolute -top-20 right-0 w-[500px] h-[500px] bg-[#C62E2E]/10 blur-[160px] rounded-full"></div>

    <div class="max-w-7xl mx-auto px-4">

        <div class="grid md:grid-cols-2 gap-14 items-center">

            {{-- LEFT --}}
            <div>

                <span class="text-xs font-semibold tracking-widest uppercase text-[#C62E2E]">
                    What is TripSpoiler
                </span>

                <h2 class="mt-4 text-4xl md:text-5xl font-bold text-slate-900 leading-tight">
                    Travel deeper. <br>
                    Not just further.
                </h2>

                <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                    TripSpoiler is built for travelers who want to understand a city before they arrive.
                </p>

                <p class="mt-4 text-slate-600 leading-relaxed">
                    We share thoughtful guides, museum insights and meaningful experiences that help you see a place
                    with clarity.
                    Instead of overwhelming lists, we focus on what truly makes a city memorable.
                </p>

                <div class="mt-8 flex flex-wrap gap-4">

                    <a href="{{ url('/cities') }}"
                        class="px-6 py-3 bg-[#C62E2E] text-white rounded-xl font-semibold
                              hover:bg-[#B91C1C] transition">
                        Explore Cities
                    </a>

                    <a href="{{ url('/activities') }}"
                        class="px-6 py-3 border border-slate-200 rounded-xl font-semibold
                              hover:border-[#C62E2E] hover:text-[#C62E2E] transition">
                        Discover Activities
                    </a>

                </div>

            </div>

            {{-- RIGHT --}}
            <div class="grid grid-cols-2 gap-4">

                <div class="bg-[#FFF5F3] p-6 rounded-2xl shadow-sm">
                    <div class="text-3xl font-bold text-[#C62E2E]">Cities</div>
                    <p class="text-sm text-slate-600 mt-2">
                        Curated city guides for curious travelers.
                    </p>
                </div>

                <div class="bg-[#FFF5F3] p-6 rounded-2xl shadow-sm">
                    <div class="text-3xl font-bold text-[#C62E2E]">Museums</div>
                    <p class="text-sm text-slate-600 mt-2">
                        Discover museums with context and meaning.
                    </p>
                </div>

                <div class="bg-[#FFF5F3] p-6 rounded-2xl shadow-sm">
                    <div class="text-3xl font-bold text-[#C62E2E]">Experiences</div>
                    <p class="text-sm text-slate-600 mt-2">
                        Carefully selected activities worth your time.
                    </p>
                </div>

                <div class="bg-[#FFF5F3] p-6 rounded-2xl shadow-sm">
                    <div class="text-3xl font-bold text-[#C62E2E]">Stories</div>
                    <p class="text-sm text-slate-600 mt-2">
                        Thoughtful reads for mindful travelers.
                    </p>
                </div>

            </div>

        </div>

    </div>

</section>
