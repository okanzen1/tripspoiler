    @props([
        'color' => 'bg-[#FFFAF9]',
        'source' => null,
        'sourceId' => null,
        'sectionHeader' => true,
        'reviewSummary' => true,
        'testimonials' => true,
    ])

    @if ($reviews->count() < 2)
        <section class="{{ $color }} py-20 md:py-28">

            <div class="max-w-7xl mx-auto px-4">

                <div class="mb-8 md:hidden">

                    <span
                        class="inline-flex items-center rounded-full border border-[#c62e2e]/15 bg-[#fff5f2] px-4 py-1.5 text-sm font-medium text-[#c62e2e]">

                        {{ __('reviews.badge') }}

                    </span>

                </div>

                <div class="grid lg:grid-cols-2 gap-16 items-center">

                    <!-- LEFT CONTENT -->
                    <div class="max-w-xl order-2 lg:order-1">

                        <span
                            class="hidden md:inline-flex items-center rounded-full border border-[#c62e2e]/15 bg-[#fff5f2] px-4 py-1.5 text-sm font-medium text-[#c62e2e]">

                            {{ __('reviews.badge') }}

                        </span>

                        <h3 class="mt-6 text-4xl md:text-5xl font-bold leading-[1.05] text-slate-900">

                            {{ __('reviews.empty.title') }}

                            <span class="block text-[#c62e2e]">
                                {{ __('reviews.empty.highlight') }}
                            </span>

                        </h3>

                        <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                            {{ __('reviews.empty.description') }}
                        </p>

                        <!-- STARS -->
                        <div class="mt-8 flex items-center gap-3">

                            <div class="flex text-amber-400 text-xl tracking-[2px]">
                                ★★★★★
                            </div>

                            <p class="text-sm text-slate-500">
                                {{ __('reviews.empty.sub') }}
                            </p>

                        </div>

                        <!-- BUTTONS -->
                        <div class="mt-10 flex flex-col sm:flex-row gap-4">

                            <button x-data @click="$dispatch('open-review')"
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full bg-[#c62e2e] px-7 py-3.5 text-sm font-semibold text-white shadow-lg hover:bg-[#b92626] transition cursor-pointer">

                                {{ __('reviews.buttons.write_review') }}

                            </button>

                            <form action="{{ route('reviews.go') }}" method="POST" class="w-full sm:w-auto">

                                @csrf

                                <input type="hidden" name="source" value="{{ $source }}">
                                <input type="hidden" name="source_id" value="{{ $sourceId }}">

                                <button
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full border border-[#c62e2e]/20 bg-white px-7 py-3.5 text-sm font-semibold text-[#c62e2e] hover:border-[#c62e2e] hover:bg-[#fff5f2] transition cursor-pointer">

                                    {{ __('reviews.buttons.read_reviews') }}

                                </button>

                            </form>

                        </div>

                    </div>

                    <!-- RIGHT VISUALS -->
                    <div class="relative h-[420px] hidden md:block order-1 lg:order-2">

                        <!-- back card -->
                        <div
                            class="absolute top-10 left-0 w-[250px] rotate-[-8deg] overflow-hidden rounded-[26px] border border-white/70 bg-white p-2 shadow-[0_18px_40px_rgba(15,23,42,0.10)] transition duration-500 hover:-translate-y-1 hover:rotate-[-10deg]">
                            <img src="{{ asset('/images/no-empty-three.png') }}"
                                alt="Travel planning" class="h-[320px] w-full rounded-[20px] object-cover">
                        </div>

                        <!-- middle main card -->
                        <div
                            class="absolute top-0 left-[145px] z-10 w-[280px] rotate-[4deg] overflow-hidden rounded-[28px] border border-white/70 bg-white p-2 shadow-[0_22px_50px_rgba(15,23,42,0.14)] transition duration-500 hover:-translate-y-2 hover:rotate-[2deg]">
                            <img src="{{asset('/images/no-empty-two.png') }}"
                                alt="City traveler" class="h-[355px] w-full rounded-[22px] object-cover">
                        </div>

                        <!-- front card -->
                        <div
                            class="absolute right-0 bottom-4 z-20 w-[235px] rotate-[9deg] overflow-hidden rounded-[24px] border border-white/70 bg-white p-2 shadow-[0_18px_40px_rgba(15,23,42,0.12)] transition duration-500 hover:-translate-y-1 hover:rotate-[7deg]">
                            <img src="{{ asset('/images/no-empty-one.png') }}"
                                alt="Travel memories" class="h-[290px] w-full rounded-[18px] object-cover">
                        </div>

                        <!-- soft accent -->
                        <div class="absolute -top-6 right-10 h-24 w-24 rounded-full bg-[#c62e2e]/10 blur-2xl"></div>
                        <div class="absolute bottom-0 left-24 h-28 w-28 rounded-full bg-[#f97316]/10 blur-2xl"></div>

                    </div>

                    <!-- MOBILE VISUALS -->
                    <div class="relative h-[280px] md:hidden order-1">

                        <div
                            class="absolute top-8 left-0 w-[150px] rotate-[-8deg] overflow-hidden rounded-[20px] border border-white/70 bg-white p-1.5 shadow-lg">
                            <img src="{{ asset('/images/no-empty-three.png') }}"
                                alt="Travel planning" class="h-[210px] w-full rounded-[16px] object-cover">
                        </div>

                        <div
                            class="absolute top-0 left-[92px] z-10 w-[170px] rotate-[4deg] overflow-hidden rounded-[22px] border border-white/70 bg-white p-1.5 shadow-xl">
                            <img src="{{asset('/images/no-empty-two.png') }}"
                                alt="City traveler" class="h-[240px] w-full rounded-[18px] object-cover">
                        </div>

                        <div
                            class="absolute right-0 bottom-2 z-20 w-[145px] rotate-[8deg] overflow-hidden rounded-[18px] border border-white/70 bg-white p-1.5 shadow-lg">
                            <img src="{{ asset('/images/no-empty-one.png') }}"
                                alt="Travel memories" class="h-[190px] w-full rounded-[14px] object-cover">
                        </div>

                    </div>

                </div>

            </div>

            <x-review-modal :source="$source ?? null" :sourceId="$sourceId ?? null" />
        </section>
    @endif

    @if ($reviews->count() >= 3)

        <section class="relative overflow-hidden {{ $color }} py-20 md:py-28">

            <div class="relative max-w-7xl mx-auto px-4">

                <!-- HEADER -->
                @if ($sectionHeader)
                    <div class="max-w-3xl mx-auto text-center">

                        <span
                            class="inline-flex items-center rounded-full border border-[#c62e2e]/15 bg-white px-4 py-1.5 text-sm font-medium text-[#c62e2e] shadow-sm">

                            {{ __('reviews.badge') }}

                        </span>

                        <h2 class="mt-6 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                            {{ __('reviews.title') }}
                        </h2>

                        <p class="mt-6 text-lg text-slate-600 leading-relaxed">
                            {{ __('reviews.description') }}
                        </p>

                    </div>
                @endif

                <!-- REVIEW SUMMARY -->
                @if ($reviewSummary)
                    <div
                        class="mx-auto mt-12 grid max-w-5xl grid-cols-1 gap-6 rounded-[28px] border border-black/5 bg-white p-6 shadow-[0_20px_60px_rgba(0,0,0,0.08)] backdrop-blur md:grid-cols-3 md:p-8">

                        <div class="flex flex-col justify-center rounded-2xl bg-[#fff7f5] p-6">

                            <div class="flex items-center gap-3">

                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#c62e2e] text-white shadow-lg">
                                    ★
                                </div>

                                <div>
                                    <p class="text-sm text-slate-500">{{ __('reviews.summary.average_rating') }}</p>
                                    <p class="text-3xl font-bold text-slate-900">{{ $general['averageRating'] }}/5</p>
                                </div>

                            </div>

                            <div class="mt-4 flex text-lg">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span class="relative inline-block w-5 h-5">

                                        <span class="text-gray-300">★</span>

                                        <span class="absolute top-0 left-0 overflow-hidden text-amber-400"
                                            style="width: {{ max(min($general['averageRating'] - ($i - 1), 1), 0) * 100 }}%">
                                            ★
                                        </span>

                                    </span>
                                @endfor
                            </div>

                        </div>


                        <div class="flex flex-col justify-center rounded-2xl bg-[#fafafa] p-6">

                            <p class="text-sm text-slate-500">{{ __('reviews.summary.happy_travelers') }}</p>

                            <p class="mt-2 text-3xl font-bold text-slate-900">
                                12,000+
                            </p>

                            <p class="mt-3 text-sm text-slate-600">
                                {{ __('reviews.summary.happy_travelers_desc') }}
                            </p>

                        </div>


                        <div class="flex flex-col justify-center rounded-2xl bg-[#fafafa] p-6">

                            <p class="text-sm text-slate-500">{{ __('reviews.summary.why_love') }}</p>

                            <ul class="mt-3 space-y-2 text-sm text-slate-600">

                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#c62e2e]"></span>
                                    {{ __('reviews.summary.reasons.0') }}
                                </li>

                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#c62e2e]"></span>
                                    {{ __('reviews.summary.reasons.1') }}
                                </li>

                                <li class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-[#c62e2e]"></span>
                                    {{ __('reviews.summary.reasons.2') }}
                                </li>

                            </ul>

                        </div>

                    </div>
                @endif

                <!-- REVIEWS -->
                <div class="mt-16">

                    <div class="swiper reviewsSwiper">

                        <div class="swiper-wrapper">

                            @foreach ($reviews as $review)
                                <div class="swiper-slide">

                                    <div
                                        class="group h-[260px] flex flex-col
                                        rounded-[28px] border border-black/5 bg-white p-7
                                        shadow-none
                                        transition duration-300 hover:-translate-y-1">

                                        <div class="flex justify-between">

                                            <div class="text-amber-400 text-lg">

                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $review->rating)
                                                        ★
                                                    @else
                                                        ☆
                                                    @endif
                                                @endfor

                                            </div>

                                            <span
                                                class="rounded-full bg-[#fff5f2] px-3 py-1 text-xs font-semibold text-[#c62e2e]">
                                                {{ __('reviews.badge_review') }}
                                            </span>

                                        </div>


                                        <p class="mt-6 text-[17px] leading-relaxed text-[#353535] line-clamp-3">
                                            “{{ $review->comment }}”
                                        </p>


                                        <div class="mt-auto flex items-center gap-4">

                                            @php

                                                $name = $review->name ?? 'U';
                                                $parts = preg_split('/\s+/u', trim($name));

                                                $initials = mb_substr($parts[0], 0, 1);

                                                if (count($parts) > 1) {
                                                    $initials .= mb_substr($parts[count($parts) - 1], 0, 1);
                                                }

                                                $colors = ['bg-[#C62E2E]', 'bg-[#F97316]', 'bg-[#10B981]'];

                                                $color = $colors[$loop->index % 3];

                                            @endphp

                                            <div
                                                class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold {{ $color }}">
                                                {{ mb_strtoupper($initials) }}
                                            </div>

                                            <div>

                                                <p class="font-semibold text-slate-900">
                                                    {{ $review->name }}
                                                </p>

                                                <p class="text-sm text-slate-500">
                                                    {{ $review->created_at->translatedFormat('F Y') }}
                                                </p>

                                            </div>

                                        </div>

                                    </div>

                                </div>
                            @endforeach

                        </div>

                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="mt-16 flex flex-col md:flex-row gap-4 justify-center items-center">

                    <form action="{{ route('reviews.go') }}" method="POST" class="w-full md:w-auto">
                        @csrf
                        <input type="hidden" name="source" value="{{ $source }}">
                        <input type="hidden" name="source_id" value="{{ $sourceId }}">

                        <button
                            class="w-full md:w-auto inline-flex justify-center items-center gap-2 rounded-full bg-[#c62e2e] px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-[#b92626] transition cursor-pointer">
                            {{ __('reviews.buttons.read_reviews') }}
                        </button>
                    </form>

                    <button x-data @click="$dispatch('open-review')"
                        class="w-full md:w-auto inline-flex justify-center items-center gap-2 rounded-full border border-[#c62e2e] px-6 py-3 text-sm font-semibold text-[#c62e2e] hover:bg-[#c62e2e] hover:text-white transition cursor-pointer">

                        {{ __('reviews.buttons.write_review') }}

                    </button>

                </div>

            </div>



            <x-review-modal :source="$source ?? null" :sourceId="$sourceId ?? null" />
        </section>

        @once
            @push('scripts')
                <script>
                    new Swiper(".reviewsSwiper", {

                        spaceBetween: 24,

                        slidesPerView: 1.15,

                        breakpoints: {

                            640: {
                                slidesPerView: 1.5
                            },

                            1024: {
                                slidesPerView: 3
                            }

                        }

                    });
                </script>
            @endpush
        @endonce

    @endif
