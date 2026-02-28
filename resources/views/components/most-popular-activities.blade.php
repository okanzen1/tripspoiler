@if (!empty($activities))
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<section class="bg-[#F7F9FB] py-20">
    <div class="max-w-7xl mx-auto px-4">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12">
            <div class="max-w-2xl">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                    Most Visited Activities
                    <span class="text-[#C62E2E]">on TripSpoiler</span>
                </h2>
                <p class="text-slate-600 mt-3 text-lg">
                    A quick look at the activities travellers keep coming back to.
                </p>
            </div>

            <!-- NAV -->
            <div class="hidden md:flex gap-3">
                <div class="swiper-button-prev-custom w-11 h-11 flex items-center justify-center
                            rounded-xl bg-white border border-slate-200
                            shadow-sm hover:shadow-md transition cursor-pointer">
                    ←
                </div>

                <div class="swiper-button-next-custom w-11 h-11 flex items-center justify-center
                            rounded-xl bg-white border border-slate-200
                            shadow-sm hover:shadow-md transition cursor-pointer">
                    →
                </div>
            </div>
        </div>

        <!-- SWIPER -->
        <div class="swiper">
            <div class="swiper-wrapper">

                @foreach ($activities as $activity)
                    @php
                        $image = $activity->images->first();
                        $type = $activity->activity_type;
                    @endphp

                    <div class="swiper-slide max-w-[300px]">

                        <a href="{{ $activity->affiliate_link }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group block h-full">

                            <div class="flex flex-col h-[360px]   <!-- BURASI SABİTLENDİ -->
                                        bg-white rounded-2xl overflow-hidden
                                        border border-slate-200
                                        shadow-[0_6px_18px_rgba(0,0,0,0.06)]
                                        hover:shadow-[0_12px_28px_rgba(0,0,0,0.10)]
                                        transition-all duration-300">

                                {{-- IMAGE (ZATEN SABİT) --}}
                                <div class="relative h-44 overflow-hidden bg-slate-100">
                                    @if ($image)
                                        <img src="{{ route('images.view', $image->id) }}"
                                            alt="{{ $activity->name }}"
                                            class="w-full h-full object-cover
                                                    transition duration-500
                                                    group-hover:scale-105">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-b from-slate-100 to-slate-200"></div>
                                    @endif
                                </div>

                                {{-- CONTENT --}}
                                <div class="flex flex-col flex-1 bg-[#F9FAFB] px-5 py-5">

                                    {{-- CATEGORY --}}
                                    <span class="text-xs text-slate-500 mb-2 tracking-wide">
                                        {{ $type === 'pass'
                                            ? 'City Pass'
                                            : ($type === 'product'
                                                ? 'Experience'
                                                : ($type === 'package'
                                                    ? 'Package'
                                                    : ucfirst($type))) }}
                                    </span>

                                    {{-- TITLE (SABİT YÜKSEKLİK) --}}
                                    <h3 class="text-base font-semibold text-slate-900 leading-snug
                                            line-clamp-2 min-h-[48px]">
                                        {{ $activity->name }}
                                    </h3>

                                    {{-- CTA --}}
                                    <div class="mt-auto pt-6">
                                        <span class="text-sm font-medium text-[#C62E2E]
                                                    inline-flex items-center gap-1">
                                            View details →
                                        </span>
                                    </div>

                                </div>

                            </div>

                        </a>

                    </div>
                @endforeach

            </div>
        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    new Swiper('.swiper', {
        loop: false,
        spaceBetween: 24,
        grabCursor: true,
        slidesPerView: 'auto',

        navigation: {
            nextEl: '.swiper-button-next-custom',
            prevEl: '.swiper-button-prev-custom',
        },
    });
</script>
@endif