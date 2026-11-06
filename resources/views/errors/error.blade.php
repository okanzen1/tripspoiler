@extends('layouts.app')

@php
    // Handler (production) $code + $message gönderir; 404 view'i de aynı değişkenleri geçer.
    $status = (int) ($code ?? 404);
    $heading = match ($status) {
        404 => __('error.heading'),
        403 => __('error.code403'),
        419 => __('error.code419'),
        default => __('error.code500'),
    };
    // 404 için uzun açıklama; diğer durumlarda başlık zaten mesajın kendisi
    $description = $status === 404 ? __('error.desc') : null;

    // Hızlı çıkışlar: yalnızca route() — sayfa 500 sırasında da güvenle çizilebilsin.
    $links = [
        ['icon' => 'compass', 'label' => __('error.home'), 'url' => route('home')],
        ['icon' => 'map-pin', 'label' => __('error.cities'), 'url' => route('cities.index')],
        ['icon' => 'ticket', 'label' => __('error.activities'), 'url' => route('activities.index')],
        ['icon' => 'book', 'label' => __('error.blog'), 'url' => route('blog.index')],
    ];
@endphp

@section('title', $status === 404 ? __('error.title') : $status . ' – TripSpoiler')
@if ($status === 404)
    @section('meta_description', __('error.meta_description'))
@endif

@section('content')
    {{-- Oops: nötr gri-beyaz zemin; sağda marka logosu için sakin bir amblem panosu --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-50 to-white">
        <div class="relative mx-auto max-w-7xl px-4 pb-16 pt-6 md:pb-20 md:pt-8">
            <x-breadcrumbs :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => $status . ' · ' . __('error.eyebrow')],
            ]" />

            <div class="mt-10 grid items-center gap-12 md:mt-14 lg:grid-cols-12 lg:gap-12">

                {{-- SOL: durum etiketi, başlık, açıklama, arama --}}
                <div class="min-w-0 lg:col-span-6">
                    <span class="inline-flex items-center gap-2.5 rounded-full bg-white py-1 pl-3 pr-1.5 text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500 shadow-[0_1px_2px_rgba(15,23,42,0.05)] ring-1 ring-slate-200">
                        <span class="size-1.5 rounded-full bg-[#C62E2E]" aria-hidden="true"></span>
                        {{ __('error.eyebrow') }}
                        <span class="rounded-full bg-slate-900 px-2.5 py-1 text-[10px] tabular-nums tracking-[0.1em] text-white">{{ $status }}</span>
                    </span>

                    <h1 class="mt-6 text-[40px] font-extrabold leading-[1.02] tracking-[-0.045em] text-slate-900 sm:text-5xl lg:text-[58px]">
                        {{ $heading }}
                    </h1>

                    <div class="mt-5 flex items-center gap-2" aria-hidden="true">
                        <span class="h-px w-14 bg-[#C62E2E]"></span>
                        <span class="size-1.5 rounded-full bg-[#C62E2E]"></span>
                    </div>

                    @if ($description)
                        <p class="mt-5 max-w-lg text-base leading-relaxed text-slate-600 md:text-lg">
                            {{ $description }}
                        </p>
                    @endif

                    {{-- arama: sitenin mevcut hero arama çubuğu --}}
                    <div class="mt-8 max-w-lg">
                        <x-search-modal hero />
                    </div>
                </div>

                {{-- SAĞ: marka amblemi — logo, ince halkalar ve tek kırmızı yay --}}
                <div class="lg:col-span-6">
                    <a href="{{ route('home') }}" aria-label="{{ __('error.home') }}"
                        class="group relative mx-auto flex aspect-square w-full max-w-[440px] flex-col items-center justify-center overflow-hidden rounded-[32px] bg-[radial-gradient(circle_at_50%_42%,#FFFFFF_0%,#F1F5F9_100%)] ring-1 ring-slate-200/80 shadow-[0_30px_80px_-40px_rgba(15,23,42,0.35)] transition duration-500 hover:ring-slate-300 lg:ms-auto lg:me-0">

                        {{-- amblem halkaları --}}
                        <div class="relative grid size-[210px] shrink-0 place-items-center sm:size-[300px]">
                            <svg class="absolute inset-0 h-full w-full" viewBox="0 0 300 300" fill="none" aria-hidden="true">
                                <circle cx="150" cy="150" r="148" stroke="#E6EDF4" stroke-width="1.4"/>
                                <circle cx="150" cy="150" r="128" stroke="#EEF3F8" stroke-width="1.2"/>
                                <path d="M150 2A148 148 0 0 1 254.7 45.3" stroke="#C62E2E" stroke-width="2.2" stroke-linecap="round"/>
                                <circle cx="254.7" cy="45.3" r="3.2" fill="#C62E2E"/>
                            </svg>

                            <img src="{{ asset('images/logo-mark.webp') }}" alt="" width="210" height="240"
                                class="relative h-40 w-auto drop-shadow-[0_22px_38px_rgba(15,23,42,0.14)] transition duration-500 group-hover:-translate-y-1 sm:h-56">
                        </div>

                        <p class="relative mt-3 text-[22px] font-extrabold tracking-[-0.03em] text-slate-900 sm:mt-5 sm:text-[28px]">
                            Trip<span class="text-[#C62E2E]">Spoiler</span>
                        </p>
                        <p class="relative mt-2 hidden text-[10px] font-bold uppercase tracking-[0.22em] text-slate-500 sm:block">
                            {{ __('home-hero.eyebrow') }}
                        </p>
                    </a>
                </div>

            </div>

            {{-- KEŞİF: hızlı çıkışlar --}}
            <div class="mt-12 border-t border-slate-200/80 pt-8 md:mt-14">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-500">{{ __('footer.explore') }}</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($links as $link)
                        <a href="{{ $link['url'] }}"
                            class="group flex items-center gap-3 rounded-2xl bg-white p-3 ring-1 ring-slate-200/80 transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_18px_40px_-24px_rgba(15,23,42,0.3)] hover:ring-slate-300">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-slate-900 text-white transition group-hover:bg-[#C62E2E]">
                                {!! \App\Support\FeatureIcons::svg($link['icon'], 'size-5') !!}
                            </span>
                            <span class="min-w-0 flex-1 text-sm font-semibold text-slate-900">{{ $link['label'] }}</span>
                            <svg class="size-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-[#C62E2E] rtl:rotate-180"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endsection
