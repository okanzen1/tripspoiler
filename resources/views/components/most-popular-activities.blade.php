@props([ 
    'color' => 'bg-[#F7F9FB]',
])

@if (!empty($activities))
    <section class="{{ $color }} py-20">
        <div class="max-w-7xl mx-auto px-4">

            <!-- HEADER -->
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12">
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                        {{ __('most-popular-activities.title_prefix') }}
                        <span class="text-[#C62E2E]">{{ __('most-popular-activities.title_highlight') }}</span>
                    </h2>
                    <p class="text-slate-600 mt-3 text-lg">
                        {{ __('most-popular-activities.desc') }}
                    </p>
                </div>

                <!-- NAV -->
                <div class="hidden md:flex gap-3">
                    <div
                        class="swiper-button-prev-custom w-11 h-11 flex items-center justify-center
                            rounded-xl bg-white border border-slate-200
                            shadow-sm hover:shadow-md transition cursor-pointer">
                        ←
                    </div>

                    <div
                        class="swiper-button-next-custom w-11 h-11 flex items-center justify-center
                            rounded-xl bg-white border border-slate-200
                            shadow-sm hover:shadow-md transition cursor-pointer">
                        →
                    </div>
                </div>
            </div>

            <!-- SWIPER -->
            <div class="swiper">
                <div class="swiper-wrapper">
                    {{-- ALL ACTIVITIES COVER --}}
                    <div class="swiper-slide max-w-[300px]">
                        <div class="swiper-slide max-w-[300px]">
                            <a href="{{ route('activities.index') }}" class="group block h-full">

                                <div
                                    class="relative h-[360px] rounded-2xl overflow-hidden
                                        shadow-[0_6px_18px_rgba(0,0,0,0.06)]
                                        hover:shadow-[0_12px_28px_rgba(0,0,0,0.10)]
                                        transition-all duration-300">

                                    {{-- IMAGE --}}
                                    <img src="{{ asset('images/all-activities-cover.png') }}"
                                        alt="{{ __('most-popular-activities.cover_title') }}"
                                        class="w-full h-full object-cover
                                        transition duration-700 group-hover:scale-105">

                                    {{-- DARK GRADIENT OVERLAY --}}
                                    <div
                                        class="absolute inset-0 
                                            bg-gradient-to-t 
                                            from-black/55 
                                            via-black/20 
                                            to-transparent">
                                    </div>

                                    {{-- TEXT CONTENT --}}
                                    <div class="absolute inset-0 flex flex-col justify-end p-6">

                                        <h3 class="text-xl font-semibold text-white leading-snug">
                                            {{ __('most-popular-activities.cover_title') }}
                                        </h3>

                                        <span
                                            class="mt-3 text-sm font-medium text-white/90 
                                                inline-flex items-center gap-1 
                                                group-hover:translate-x-1 
                                                transition duration-300">
                                            {{ __('most-popular-activities.cover_cta') }}
                                        </span>

                                    </div>

                                </div>

                            </a>
                        </div>
                    </div>

                    @foreach ($activities as $activity)
                        @php
                            $image = $activity->images->first();
                            $type = $activity->activity_type;
                        @endphp

                        <div class="swiper-slide max-w-[300px]">
                            <a href="{{ $activity->affiliate_link }}" target="_blank" rel="noopener noreferrer"
                                class="group block h-full">

                                <div
                                    class="relative h-[360px] rounded-2xl overflow-hidden
                                        shadow-[0_6px_18px_rgba(0,0,0,0.06)]
                                        hover:shadow-[0_12px_28px_rgba(0,0,0,0.10)]
                                        transition-all duration-300">

                                    {{-- IMAGE --}}
                                    @if ($image)
                                        <img src="{{ route('images.view', $image->id) }}" alt="{{ $activity->name }}"
                                            class="w-full h-full object-cover
                                                transition duration-700
                                                group-hover:scale-105">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-b from-slate-100 to-slate-200"></div>
                                    @endif

                                    {{-- DARK GRADIENT OVERLAY --}}
                                    <div
                                        class="absolute inset-0 
                                            bg-gradient-to-t 
                                            from-black/55 
                                            via-black/20 
                                            to-transparent">
                                    </div>

                                    {{-- TEXT CONTENT --}}
                                    <div class="absolute inset-0 flex flex-col justify-end p-6">

                                        {{-- CATEGORY --}}
                                        <span class="text-xs text-white/80 mb-2 tracking-wide">
                                            {{ $type === 'pass'
                                                ? __('most-popular-activities.category_pass')
                                                : ($type === 'product'
                                                    ? __('most-popular-activities.category_product')
                                                    : ($type === 'package'
                                                        ? __('most-popular-activities.category_package')
                                                        : ucfirst($type))) }}
                                        </span>

                                        {{-- TITLE --}}
                                        <h3 class="text-lg font-semibold text-white leading-snug line-clamp-2">
                                            {{ $activity->name }}
                                        </h3>

                                        {{-- CTA --}}
                                        <span
                                            class="mt-3 text-sm font-medium text-white/90 
                                                inline-flex items-center gap-1 
                                                group-hover:translate-x-1 
                                                transition duration-300">
                                            {{ __('most-popular-activities.cta') }}
                                        </span>

                                    </div>

                                </div>

                            </a>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    </section>

    @push('scripts')
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
@endpush
