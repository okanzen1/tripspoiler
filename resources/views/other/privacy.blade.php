@extends('layouts.app')

@section('title', __('privacy.meta_title'))
@section('meta_description', __('privacy.meta_description'))

@section('content')

    {{-- PRIVACY POLICY HERO — FULL WIDTH --}}
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
                {{ __('privacy.hero_badge') }}
            </span>

            {{-- TITLE (FULL WIDTH) --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ __('privacy.hero_title_1') }}
                <span class="text-[#C62E2E]">{{ __('privacy.hero_title_2') }}</span>
            </h1>

            {{-- DESCRIPTION (NO MAX-W, FULL WIDTH) --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                {{ __('privacy.hero_desc') }}
            </p>

        </div>
    </section>

    <section class="bg-white py-14">
        <div class="max-w-7xl mx-auto px-4 leading-relaxed text-slate-700 space-y-6">


            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section1_title') }}
            </h2>

            <p>
                {{ __('privacy.section1_text') }}
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section2_title') }}
            </h2>

            <p>
                {{ __('privacy.section2_text') }}
            </p>

            <ul class="list-disc pl-6 space-y-2">
                <li>{{ __('privacy.list_1') }}</li>
                <li>{{ __('privacy.list_2') }}</li>
                <li>{{ __('privacy.list_3') }}</li>
                <li>{{ __('privacy.list_4') }}</li>
            </ul>

            <p>
                {{ __('privacy.section2_text2') }}
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section3_title') }}
            </h2>

            <p>
                {{ __('privacy.section3_text') }}
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section4_title') }}
            </h2>

            <p>
                {{ __('privacy.section4_text') }}
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section5_title') }}
            </h2>

            <p>
                {{ __('privacy.section5_text') }}
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section6_title') }}
            </h2>

            <p>
                {{ __('privacy.section6_text') }}
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                {{ __('privacy.section7_title') }}
            </h2>

            <p>
                {{ __('privacy.section7_text') }}
            </p>

            <p class="font-medium">
                <a href="{{ url('/contact') }}" class="text-[#C62E2E] hover:underline">
                    {{ __('privacy.contact_link') }}
                </a>
            </p>



        </div>
    </section>

@endsection