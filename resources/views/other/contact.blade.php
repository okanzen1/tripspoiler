@extends('layouts.app')

@section('title', __('contact.meta_title'))
@section('meta_description', __('contact.meta_description'))

@section('content')

    {{-- CONTACT HERO — FULL WIDTH --}}
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
                {{ __('contact.hero_badge') }}
            </span>

            {{-- TITLE (FULL WIDTH) --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ __('contact.hero_title_1') }}
                <span class="text-[#C62E2E]">{{ __('contact.hero_title_2') }}</span>
            </h1>

            {{-- DESCRIPTION (NO MAX-W, FULL WIDTH) --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                {{ __('contact.hero_desc') }}
            </p>

        </div>
    </section>


    {{-- CONTACT FORM --}}
    <section class="bg-white py-16">
        <div class="max-w-4xl mx-auto px-4">

            {{-- SUCCESS ALERT --}}
            @if (session('success'))
                <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-6">
                @csrf

                {{-- HONEYPOT --}}
                <div class="hidden">
                    <input type="text" name="website" value="">
                </div>

                {{-- NAME --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('contact.name_label') }}
                    </label>
                    <input type="text" name="name" required
                        class="w-full rounded-2xl border border-[#F3D6D1]
                           px-5 py-3 text-slate-900
                           outline-none
                           focus:border-[#C62E2E]
                           focus:ring-4 focus:ring-[#C62E2E]/15">
                </div>

                {{-- EMAIL --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('contact.email_label') }}
                    </label>
                    <input type="email" name="email" required
                        class="w-full rounded-2xl border border-[#F3D6D1]
                           px-5 py-3 text-slate-900
                           outline-none
                           focus:border-[#C62E2E]
                           focus:ring-4 focus:ring-[#C62E2E]/15">
                </div>

                {{-- MESSAGE --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        {{ __('contact.message_label') }}
                    </label>
                    <textarea name="message" rows="5" required
                        class="w-full rounded-2xl border border-[#F3D6D1]
                           px-5 py-3 text-slate-900
                           outline-none resize-none
                           focus:border-[#C62E2E]
                           focus:ring-4 focus:ring-[#C62E2E]/15"></textarea>
                </div>

                {{-- SUBMIT --}}
                <button
                    class="inline-flex items-center justify-center
                        bg-[#C62E2E] hover:bg-red-700
                        text-white font-semibold
                        px-8 py-3 rounded-full
                        transition
                        cursor-pointer">
                    {{ __('contact.submit_button') }}
                </button>

            </form>

            <p class="text-sm text-slate-500 mt-6">
                {{ __('contact.reply_text') }}
            </p>

        </div>
    </section>

@endsection