@if (!empty($activities))
    <!-- SWIPER CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <section class="bg-[#F7F9FB] py-20">
        <div class="max-w-7xl mx-auto px-4">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-10">
                <div class="max-w-2xl">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                        Most Visited Activities
                        <span class="text-[#C62E2E]">on TripSpoiler</span>
                    </h2>
                    <p class="text-slate-600 mt-3 text-lg">
                        A quick look at the activities travellers keep coming back to.
                    </p>
                </div>

                <!-- DESKTOP ONLY NAV -->
                <div class="hidden md:flex gap-3">
                    <div
                        class="swiper-button-prev-custom w-11 h-11 flex items-center justify-center
                        rounded-lg border bg-white hover:bg-slate-100 shadow cursor-pointer">
                        ←
                    </div>

                    <div
                        class="swiper-button-next-custom w-11 h-11 flex items-center justify-center
                        rounded-lg border bg-white hover:bg-slate-100 shadow cursor-pointer">
                        →
                    </div>
                </div>
            </div>

            <!-- SWIPER -->
            <div class="swiper">
                <div class="swiper-wrapper">
                    @foreach ($activities as $activity)
                        <div class="swiper-slide max-w-[300px]">
                            <div
                                class="relative bg-white rounded-3xl border border-[#F3D6D1]
                                shadow-sm hover:shadow-lg transition overflow-hidden group cursor-pointer">

                                {{-- FULL CARD CLICK --}}
                                <a href="{{ $activity->affiliate_link }}" target="_blank" rel="noopener noreferrer"
                                    class="absolute inset-0 z-30"></a>

                                {{-- IMAGE --}}
                                <div class="relative h-44 overflow-hidden z-10 pointer-events-none">
                                    <img src="{{ $activity->images->isNotEmpty() ? route('images.view', $activity->images->first()->id) : asset('') }}"
                                        alt="{{ $activity->name }}"
                                        class="h-full w-full object-cover group-hover:scale-105 transition duration-500">

                                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
                                </div>

                                {{-- CONTENT --}}
                                <div class="p-4 flex flex-col h-[180px] relative z-20 pointer-events-none">
                                    <h3 class="font-semibold text-lg text-slate-900">
                                        {{ $activity->name }}
                                    </h3>

                                    <div class="mt-auto flex justify-end">
                                        <span class="text-sm font-semibold text-[#C62E2E] group-hover:underline">
                                            View details →
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </section>

    <!-- SWIPER JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        new Swiper('.swiper', {
            loop: false,
            spaceBetween: 20,
            grabCursor: true,
            slidesPerView: 'auto',

            navigation: {
                nextEl: '.swiper-button-next-custom',
                prevEl: '.swiper-button-prev-custom',
            },
        });
    </script>
@endif
