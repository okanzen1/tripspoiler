@if (count($reviews) > 0)

    <section class="relative overflow-hidden bg-[#FFFAF9] py-20 md:py-28">

        <div class="relative max-w-7xl mx-auto px-4">

            <!-- HEADER -->
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


            <!-- REVIEW SUMMARY -->
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
            <div class="mt-16 flex flex-col md:flex-row gap-4 justify-center">

                {{-- <a href="#reviews"
                    class="inline-flex items-center gap-2 rounded-full bg-[#c62e2e] px-6 py-3 text-sm font-semibold text-white shadow-lg hover:bg-[#b92626] transition">

                    {{ __('reviews.buttons.read_reviews') }}

                </a> --}}

                <button x-data @click="$dispatch('open-review')"
                    class="inline-flex items-center gap-2 rounded-full border border-[#c62e2e] px-6 py-3 text-sm font-semibold text-[#c62e2e] hover:bg-[#c62e2e] hover:text-white transition cursor-pointer relative z-10">

                    {{ __('reviews.buttons.write_review') }}

                </button>

            </div>

        </div>



    </section>
    <div x-data="reviewModal()"
        x-on:open-review.window="open=true; success=false; name=''; email=''; comment=''; rating=0; hover=0;"
        x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm px-4">

        <div @click.away="open=false"
            class="w-full max-w-xl rounded-[32px] bg-white shadow-[0_30px_80px_rgba(0,0,0,0.2)] p-10 relative">

            <button @click="open=false"
                class="absolute right-6 top-6 text-slate-400 hover:text-black text-xl cursor-pointer">
                ✕
            </button>


            <!-- SUCCESS SCREEN -->

            <template x-if="success">

                <div class="text-center py-10">

                    <div class="flex justify-center mb-6">

                        <div class="w-20 h-20 rounded-full bg-green-100 flex items-center justify-center">

                            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-green-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7" />

                            </svg>

                        </div>

                    </div>

                    <h3 class="text-2xl font-bold text-slate-900">
                        {{ __('reviews.success.title') }}
                    </h3>

                    <p class="text-slate-500 mt-2">
                        {{ __('reviews.success.description') }}
                    </p>

                </div>

            </template>



            <!-- FORM -->

            <template x-if="!success">

                <form @submit.prevent="submitReview" class="space-y-6">
                    <div x-show="error" x-text="error"
                        class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3">
                    </div>

                    <input type="hidden" name="source" value="{{ $source }}">
                    <input type="hidden" name="source_id" value="{{ $sourceId }}">

                    <div class="hidden">
                        <input type="text" x-model="website">
                    </div>


                    <h3 class="text-2xl font-bold text-slate-900 text-center">
                        {{ __('reviews.form.title') }}
                    </h3>


                    <div>

                        <label class="text-sm font-medium text-slate-700">
                            {{ __('reviews.form.name') }}
                        </label>

                        <input type="text" x-model="name" required
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-[#c62e2e] focus:ring-0">

                    </div>


                    <div>

                        <label class="text-sm font-medium text-slate-700">
                            {{ __('reviews.form.email') }}
                        </label>

                        <input type="email" x-model="email" required
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-[#c62e2e] focus:ring-0">

                    </div>



                    <div>

                        <label class="text-sm font-medium text-slate-700">
                            {{ __('reviews.form.rating') }}
                        </label>

                        <div class="flex gap-2 mt-3 text-3xl">

                            <template x-for="i in 5">

                                <span @click="rating=i" @mouseenter="hover=i" @mouseleave="hover=0"
                                    class="cursor-pointer transition"
                                    :class="(hover >= i || rating >= i) ? 'text-amber-400 scale-110' : 'text-gray-300'">

                                    ★
                                </span>

                            </template>

                        </div>

                    </div>



                    <div>

                        <label class="text-sm font-medium text-slate-700">
                            {{ __('reviews.form.comment') }}
                        </label>

                        <textarea x-model="comment" rows="4" required
                            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 focus:border-[#c62e2e] focus:ring-0 resize-none"></textarea>

                    </div>


                    <button type="submit" :disabled="loading"
                        class="w-full rounded-full bg-[#c62e2e] py-4 text-white font-semibold text-lg hover:bg-[#b92626] transition disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">

                        <span x-show="!loading">{{ __('reviews.form.submit') }}</span>
                        <span x-show="loading">{{ __('reviews.form.submitting') }}</span>

                    </button>

                </form>

            </template>

        </div>
    </div>
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
            <script>
                function reviewModal() {
                    return {
                        open: false,
                        success: false,

                        name: '',
                        email: '',
                        comment: '',
                        rating: 0,
                        hover: 0,
                        website: '',
                        error: '',
                        loading: false,

                        submitReview() {

                            if (this.rating === 0) {
                                this.error = "{{ __('reviews.errors.rating_required') }}"
                                return
                            }

                            this.error = ''
                            this.loading = true

                            fetch("{{ route('reviews.store') }}", {

                                    method: "POST",

                                    headers: {
                                        "Content-Type": "application/json",
                                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                        "Accept": "application/json"
                                    },

                                    body: JSON.stringify({
                                        name: this.name,
                                        email: this.email,
                                        comment: this.comment,
                                        rating: this.rating,
                                        source: "{{ $source }}",
                                        source_id: "{{ $sourceId }}",
                                        website: this.website
                                    })

                                })
                                .then(res => res.json())
                                .then(data => {

                                    this.loading = false

                                    if (data.success) {

                                        this.success = true

                                        this.name = ''
                                        this.email = ''
                                        this.comment = ''
                                        this.rating = 0

                                        setTimeout(() => {
                                            this.open = false
                                        }, 3000)

                                    } else {

                                        this.error = data.message ?? "{{ __('reviews.errors.something_wrong') }}"

                                    }

                                })
                                .catch(() => {

                                    this.loading = false
                                    this.error = "{{ __('reviews.errors.server_error') }}"

                                })

                        }
                    }
                }
            </script>
        @endpush
    @endonce

@endif
