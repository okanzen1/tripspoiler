@extends('layouts.app')

@section('title', $metaTitle)
@section('meta_description', Str::limit(strip_tags($metaDescription), 155))

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
@endpush

@section('content')

    {{-- HERO --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        {{-- SOFT GLOW --}}
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-1/2 -translate-x-1/2
                   w-[520px] h-[520px]
                   bg-[#C62E2E]/10 rounded-full blur-[160px]">
            </div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 py-14">

            {{-- BREADCRUMB --}}
            <nav class="text-sm text-slate-500 mb-4">
                @include('breadcrumbs::tailwind', [
                    'breadcrumbs' => Breadcrumbs::generate('activities.show', $activity),
                ])
            </nav>

            {{-- TITLE --}}
            @php
                $titleWords = explode(' ', $activity->getTranslation('name', app()->getLocale()));
                $firstPart = implode(' ', array_slice($titleWords, 0, 4));
                $secondPart = implode(' ', array_slice($titleWords, 4));
            @endphp

            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ $firstPart }}
                @if ($secondPart)
                    <br>
                    <span class="text-[#C62E2E]">
                        {{ $secondPart }}
                    </span>
                @endif
            </h1>

            {{-- META --}}
            <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-600">
                <span>
                    📍
                    {{ mb_convert_case($activity->city->getTranslation('name', app()->getLocale()), MB_CASE_TITLE, 'UTF-8') }},
                    {{ mb_convert_case(
                        $activity->city->country->getTranslation('name', app()->getLocale()),
                        MB_CASE_TITLE,
                        'UTF-8',
                    ) }}
                </span>

                @if ($activity->duration)
                    <span>
                        ⏱️ {{ $activity->duration }}
                    </span>
                @endif

                @if ($activity->audio_guide)
                    <span>
                        🎧 {{ __('Audio Guide Included') }}
                    </span>
                @endif
            </div>

        </div>
    </section>

    {{-- MAIN --}}
    <section class="bg-white pb-28 lg:pb-0">
        <div class="max-w-6xl mx-auto px-4 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- LEFT --}}
            <div class="lg:col-span-2">

                {{-- IMAGE SWIPER --}}
                <div class="mb-10">
                    <div class="swiper activitySwiper rounded-3xl overflow-hidden">

                        <div class="swiper-wrapper">

                            @if ($activity->images && count($activity->images) > 0)
                                @foreach ($activity->images as $image)
                                    <div class="swiper-slide">
                                        <img src="{{ route('images.view', $image->id) }}"
                                            data-full="{{ route('images.view', $image->id) }}"
                                            class="w-full h-[260px] sm:h-[360px] md:h-[420px] object-cover cursor-pointer activity-image">
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="swiper-button-next !text-white"></div>
                        <div class="swiper-button-prev !text-white"></div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>

                <div
                    class="lg:hidden mt-6 bg-white border border-[#F3D6D1] rounded-2xl p-4 text-sm text-slate-600 space-y-2 shadow-sm">
                    <div>✔ {{ __('Official ticket provider') }}</div>
                    <div>✔ {{ __('Instant confirmation') }}</div>

                    <p class="text-xs text-slate-400 pt-2">
                        {{ __('You’ll be redirected to the official ticket provider to complete your booking.') }}
                    </p>

                </div>

                {{-- DESCRIPTION --}}
                @if ($activity->description)
                    <div class="prose prose-slate max-w-none">
                        {!! $activity->getTranslation('description', app()->getLocale()) !!}
                    </div>
                @endif

            </div>

            {{-- RIGHT --}}
            <aside class="hidden lg:block lg:col-span-1">
                <div class="sticky top-24 border border-[#F3D6D1] rounded-3xl p-6 shadow-sm bg-white">

                    {{-- CTA --}}
                    <a href="{{ $activity->affiliate_link }}" target="_blank" rel="nofollow sponsored noopener"
                        class="block text-center bg-[#C62E2E] text-white font-semibold py-4 rounded-full hover:opacity-90 transition">
                        {{ __('Check availability') }}
                    </a>

                    {{-- Redirect info --}}
                    <p class="text-xs text-slate-400 text-center mt-3">
                        {{ __('You’ll be redirected to the official ticket provider to complete your booking.') }}
                    </p>

                    {{-- Trust points --}}
                    <div class="mt-6 text-sm text-slate-600 space-y-2">
                        <div>✔ {{ __('Official ticket provider') }}</div>
                        <div>✔ {{ __('Instant confirmation') }}</div>
                    </div>

                </div>
            </aside>

        </div>
    </section>
    {{-- MOBILE FIXED CTA --}}
    <div
        class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-[#F3D6D1] p-4 shadow-[0_-4px_20px_rgba(0,0,0,0.05)]">

        <a href="{{ $activity->affiliate_link }}" target="_blank" rel="nofollow sponsored noopener"
            class="block text-center bg-[#C62E2E] text-white font-semibold py-4 rounded-full">
            {{ __('Check availability') }}
        </a>

    </div>
    {{-- FAQ --}}
    <x-faq source="activity-show" :source-id="$activity->id" bgColor="bg-[#FFF8F6]" />

    <div id="imageModal"
        class="fixed inset-0 hidden z-50 bg-black/90 backdrop-blur-sm flex items-center justify-center p-4">
        <button id="closeModal" class="absolute top-6 right-6 text-white text-3xl font-bold">
            &times;
        </button>

        <div class="swiper modalSwiper w-full max-w-5xl">
            <div class="swiper-wrapper">

                @foreach ($activity->images as $image)
                    <div class="swiper-slide flex items-center justify-center">
                        <img src="{{ route('images.view', $image->id) }}"
                            class="w-full max-h-[85vh] object-contain rounded-xl">
                    </div>
                @endforeach

            </div>

            <div class="swiper-button-next !text-white !right-2"></div>
            <div class="swiper-button-prev !text-white !left-2"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        new Swiper('.activitySwiper', {
            loop: true,
            spaceBetween: 16,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>

    <script>
        let modalSwiper;

        const modal = document.getElementById('imageModal');
        const closeModal = document.getElementById('closeModal');

        function openModal(index) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');

            if (!modalSwiper) {
                modalSwiper = new Swiper('.modalSwiper', {
                    loop: true,
                    navigation: {
                        nextEl: '.modalSwiper .swiper-button-next',
                        prevEl: '.modalSwiper .swiper-button-prev',
                    },
                });
            }

            modalSwiper.slideToLoop(index);
        }

        function closeModalFunc() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }

        document.querySelectorAll('.activity-image').forEach((img, index) => {
            img.addEventListener('click', function() {
                openModal(index);
            });
        });

        closeModal.addEventListener('click', closeModalFunc);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModalFunc();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModalFunc();
            }
        });
    </script>
@endpush
