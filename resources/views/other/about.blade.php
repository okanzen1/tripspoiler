@extends('layouts.app')

@section('title', __('about.meta_title'))
@section('meta_description',
    __('about.meta_description'))

@section('content')
    {{-- ABOUT HERO — FULL WIDTH TEXT --}}
    <section class="relative overflow-hidden
                bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        {{-- SOFT GLOW --}}
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-0
                   w-[520px] h-[520px]
                   bg-[#C62E2E]/10 rounded-full blur-[160px]">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 py-16 md:py-24">

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
                   text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                {{ __('about.hero_badge') }}
            </span>

            {{-- TITLE --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ __('about.hero_title_1') }}<br>
                <span class="text-[#C62E2E]">
                    {{ __('about.hero_title_2') }}
                </span>
            </h1>

            {{-- DESCRIPTION — NO WIDTH LIMIT --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                {{ __('about.hero_desc') }}
            </p>

        </div>
    </section>

    {{-- CONTENT --}}
    <section class="bg-white py-14">
        <div class="max-w-7xl mx-auto px-4 leading-relaxed text-slate-700 space-y-6">

            <p>
                {{ __('about.p1') }}
            </p>

            <p>
                {{ __('about.p2') }}
            </p>

            <p>
                {{ __('about.p3') }}
            </p>

            <p>
                {{ __('about.p4') }}
            </p>

            <p>
                {{ __('about.p5') }}
                <strong>{{ __('about.p6') }}</strong>
            </p>

        </div>
    </section>


@endsection