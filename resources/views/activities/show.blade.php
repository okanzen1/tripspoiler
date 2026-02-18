@extends('layouts.app')

@section('title', $metaTitle)
@section('meta_description', Str::limit(strip_tags($metaDescription), 155))

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.css" />
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
    <section class="bg-white pb-0 lg:pb-0">
        <div class="max-w-6xl mx-auto px-4 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">

            {{-- LEFT --}}
            <div class="lg:col-span-2">
                @if ($activity->images && count($activity->images) > 0)
                    <div class="mb-10">

                        <div class="embla overflow-hidden rounded-3xl relative">
                            <div class="embla__container flex">

                                @foreach ($activity->images as $image)
                                    <div class="embla__slide flex-[0_0_100%] px-2">
                                        <a href="{{ route('images.view', $image->id) }}" data-fancybox="activity-gallery">
                                            <img src="{{ route('images.view', $image->id) }}"
                                                class="w-full h-[260px] sm:h-[360px] md:h-[420px] object-cover rounded-3xl">
                                        </a>
                                    </div>
                                @endforeach

                            </div>

                            <button
                                class="embla-prev
                                absolute left-4 top-1/2 -translate-y-1/2 z-10
                                w-11 h-11
                                rounded-full
                                bg-white/70 backdrop-blur-md
                                border border-white/40
                                shadow-lg
                                flex items-center justify-center
                                text-slate-800
                                hover:bg-white
                                hover:scale-105
                                transition">

                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>

                            <button
                                class="embla-next
                                absolute right-4 top-1/2 -translate-y-1/2 z-10
                                w-11 h-11
                                rounded-full
                                bg-white/70 backdrop-blur-md
                                border border-white/40
                                shadow-lg
                                flex items-center justify-center
                                text-slate-800
                                hover:bg-white
                                hover:scale-105
                                transition">

                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <div
                    class="lg:hidden mt-6 bg-white border border-[#F3D6D1] rounded-2xl p-4 text-sm text-slate-600 space-y-2 shadow-sm mb-8">
                    <div>✔ {{ __('Official ticket provider') }}</div>
                    <div>✔ {{ __('Instant confirmation') }}</div>

                    <p class="text-xs text-slate-400 pt-2">
                        {{ __('You’ll be redirected to the official ticket provider to complete your booking.') }}
                    </p>

                </div>

                {{-- DESCRIPTION --}}
                @if ($activity->description)
                    <div x-data="{ expanded: false }" class="relative">

                        {{-- Content wrapper --}}
                        <div class="prose prose-slate max-w-none overflow-hidden"
                            :style="expanded
                                ?
                                'max-height: ' + $refs.content.scrollHeight +
                                'px; transition: max-height 0.6s cubic-bezier(0.4, 0, 0.2, 1)' :
                                'max-height: 320px; transition: max-height 0.5s cubic-bezier(0.4, 0, 0.2, 1)'"
                            x-ref="content">
                            {!! $activity->getTranslation('description', app()->getLocale()) !!}
                        </div>

                        {{-- Fade gradient --}}
                        <div x-show="!expanded" x-transition:enter="transition-opacity duration-300"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute bottom-8 left-0 right-0 h-28 bg-gradient-to-t from-white to-transparent pointer-events-none">
                        </div>

                        {{-- Toggle button --}}
                        <div class="mt-4 relative z-10">
                            <button @click="expanded = !expanded"
                                class="inline-flex items-center gap-2 text-sm font-semibold text-[#C62E2E] hover:text-[#a02020] transition-colors duration-200">
                                <span x-text="expanded ? '{{ __('See less') }}' : '{{ __('See more') }}'"></span>
                                <svg xmlns="http://www.w3.org/2000/svg"
                                    class="w-4 h-4 transition-transform duration-500 ease-in-out"
                                    :class="expanded ? 'rotate-180' : 'rotate-0'" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                        </div>

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
@endsection

@push('scripts')
    <script src="https://unpkg.com/embla-carousel/embla-carousel.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox.umd.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emblaNode = document.querySelector('.embla');
            if (!emblaNode) return;

            const embla = EmblaCarousel(emblaNode, {
                loop: true,
                align: 'start'
            });

            document.querySelector('.embla-next').addEventListener('click', () => {
                embla.scrollNext();
            });

            document.querySelector('.embla-prev').addEventListener('click', () => {
                embla.scrollPrev();
            });

            Fancybox.bind("[data-fancybox='activity-gallery']", {
                dragToClose: false,
                Toolbar: {
                    display: ["close"]
                }
            });

        });
    </script>
@endpush
