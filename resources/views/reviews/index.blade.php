@extends('layouts.app')

@section('title', __('reviews.meta.title'))
@section('meta_description', __('reviews.meta.description'))

@section('content')

    <section class="relative h-[480px] lg:h-[720px] overflow-hidden bg-black">

        <!-- HERO IMAGE -->
        <img src="{{ asset('images/review-all-hero.webp') }}" class="absolute inset-0 w-full h-full object-cover">

        <!-- OVERLAY -->
        <div class="absolute inset-0 bg-gradient-to-r from-black/60 via-black/30 to-black/10"></div>


        <div class="relative max-w-7xl mx-auto px-4 h-full flex items-center">

            <!-- LEFT CONTENT -->
            <div class="max-w-xl text-white">

                <span class="inline-flex items-center rounded-full bg-white/10 backdrop-blur px-4 py-1.5 text-sm text-white">
                    {{ __('reviews.badge') }}
                </span>

                <h1 class="mt-6 text-4xl md:text-6xl font-bold leading-[1.05] text-white">
                    {{ __('reviews.hero.title_line1') }}
                    <span class="block text-[#ff6b6b]">
                        {{ __('reviews.hero.title_line2') }}
                    </span>
                </h1>

                <p class="mt-6 text-lg text-white leading-relaxed">
                    {{ __('reviews.hero.description') }}
                </p>


                <!-- CTA -->
                <div class="mt-10 flex gap-4">

                    <button x-data @click="$dispatch('open-review')"
                        class="rounded-full bg-[#c62e2e] px-8 py-4 text-sm font-semibold text-white hover:bg-[#b92626] transition shadow-xl cursor-pointer">
                        {{ __('reviews.buttons.write_review') }}
                    </button>

                    <a href="#reviews"
                        class="rounded-full border border-white px-8 py-4 text-sm font-semibold text-white hover:bg-white/10 transition">
                        {{ __('reviews.buttons.browse_reviews') }}
                    </a>

                </div>


                <!-- TRUST -->
                <div class="mt-12 flex items-center gap-4 text-white">

                    <div class="flex items-center text-lg leading-none">

                        @for ($i = 1; $i <= 5; $i++)
                            <div class="relative w-[20px] h-[20px] flex items-center">

                                <!-- boş yıldız -->
                                <span class="absolute text-white/30 leading-none">★</span>

                                <!-- dolu yıldız -->
                                <span class="absolute text-amber-400 overflow-hidden leading-none"
                                    style="width: {{ max(min($general['averageRating'] - ($i - 1), 1), 0) * 100 }}%">
                                    ★
                                </span>

                            </div>
                        @endfor

                    </div>

                    <p class="text-sm leading-none">
                        <span class="font-semibold">{{ $general['averageRating'] }}</span>
                        {{ __('reviews.hero.rating_text', ['count' => '12,000+']) }}
                    </p>

                </div>

            </div>

        </div>

        <!-- DESKTOP FLOATING REVIEW CARDS -->
        <div class="absolute inset-0 pointer-events-none hidden lg:block">

            <!-- CARD 1 -->
            <div class="absolute top-28 right-20 bg-white rounded-2xl shadow-2xl p-6 w-72 rotate-[-2deg]">

                <div class="text-amber-400 text-sm">★★★★★</div>

                <p class="mt-3 text-sm text-slate-700 leading-relaxed">
                    “{{ __('reviews.cards.review1') }}”
                </p>

                <div class="mt-4 flex items-center gap-3">

                    <div
                        class="w-9 h-9 rounded-full bg-[#c62e2e] flex items-center justify-center text-white text-xs font-semibold">
                        EM
                    </div>

                    <div>
                        <p class="text-sm font-semibold">Emma Wilson</p>
                        <p class="text-xs text-slate-500">{{ __('reviews.cards.review1_city') }}</p>
                    </div>

                </div>

            </div>


            <!-- CARD 2 -->
            <div class="absolute top-60 right-64 bg-white rounded-2xl shadow-2xl p-6 w-72 rotate-[2deg]">

                <div class="text-amber-400 text-sm">★★★★★</div>

                <p class="mt-3 text-sm text-slate-700 leading-relaxed">
                    “{{ __('reviews.cards.review2') }}”
                </p>

                <div class="mt-4 flex items-center gap-3">

                    <div
                        class="w-9 h-9 rounded-full bg-slate-900 flex items-center justify-center text-white text-xs font-semibold">
                        JL
                    </div>

                    <div>
                        <p class="text-sm font-semibold">James Lee</p>
                        <p class="text-xs text-slate-500">{{ __('reviews.cards.review2_city') }}</p>
                    </div>

                </div>

            </div>


            <!-- CARD 3 -->
            <div class="absolute bottom-28 right-28 bg-white rounded-2xl shadow-2xl p-6 w-72 rotate-[-1deg]">

                <div class="text-amber-400 text-sm">★★★★★</div>

                <p class="mt-3 text-sm text-slate-700 leading-relaxed">
                    “{{ __('reviews.cards.review3') }}”
                </p>

                <div class="mt-4 flex items-center gap-3">

                    <div
                        class="w-9 h-9 rounded-full bg-emerald-600 flex items-center justify-center text-white text-xs font-semibold">
                        SR
                    </div>

                    <div>
                        <p class="text-sm font-semibold">Sofia Rossi</p>
                        <p class="text-xs text-slate-500">{{ __('reviews.cards.review3_city') }}</p>
                    </div>

                </div>

            </div>

        </div>

    </section>

    <section id="reviews" class="bg-white pt-24 pb-5">

        <div class="max-w-7xl mx-auto px-4">

            <!-- TITLE -->
            <div class="mb-16">

                <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                    {{ __('reviews.buttons.read_reviews') }}
                </h2>

                <p class="mt-4 text-slate-600">
                    {{ __('reviews.section_description') }}
                </p>

            </div>


            <!-- REVIEWS -->
            <div class="space-y-12">
                @php
                    $avatarColors = [
                        'bg-red-500',
                        'bg-blue-500',
                        'bg-emerald-500',
                        'bg-purple-500',
                        'bg-amber-500',
                        'bg-pink-500',
                        'bg-indigo-500',
                        'bg-teal-500',
                        'bg-orange-500',
                        'bg-cyan-500',
                    ];
                @endphp
                @foreach ($reviews as $review)
                    @php
                        // isme göre sabit ama "random gibi" renk
                        $colorIndex = crc32($review->name) % count($avatarColors);
                        $avatarBg = $avatarColors[$colorIndex];
                    @endphp

                    <div class="border-b border-slate-200 pb-10">

                        <!-- HEADER -->
                        <div class="flex items-center justify-between">

                            <div class="flex items-center gap-4">

                                <!-- AVATAR -->
                                <div
                                    class="w-11 h-11 rounded-full {{ $avatarBg }} flex items-center justify-center text-white text-sm font-semibold">
                                    {{ mb_strtoupper(mb_substr($review->name, 0, 1)) }}
                                </div>

                                <div>
                                    <p class="font-semibold text-slate-900">
                                        {{ $review->name }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{ $review->created_at->translatedFormat('M Y') }}
                                    </p>
                                </div>

                            </div>

                            <!-- STARS -->
                            <div class="flex text-amber-400 text-sm">
                                @for ($i = 1; $i <= 5; $i++)
                                    @if ($review->rating >= $i)
                                        ★
                                    @else
                                        <span class="text-slate-300">★</span>
                                    @endif
                                @endfor
                            </div>

                        </div>

                        <!-- COMMENT -->
                        <p class="mt-5 text-slate-700 leading-relaxed">
                            {{ $review->comment }}
                        </p>

                    </div>
                @endforeach

            </div>


            <!-- PAGINATION -->
            <div class="mt-16 flex justify-center md:justify-end">
                {{ $reviews->onEachSide(1)->links('components.pagination') }}
            </div>

        </div>

    </section>

    <x-social-presence-section :color="'bg-white'" />
    <x-review-modal :source="$source ?? null" :sourceId="$sourceId ?? null" />

@endsection

@push('scripts')
    <script type="application/ld+json">
        {!! json_encode($reviewSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

    <script type="application/ld+json">
        {!! json_encode($itemListSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush
