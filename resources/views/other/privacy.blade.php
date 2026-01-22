@extends('layouts.app')

@section('title', 'Privacy Policy - TripSpoiler')
@section('meta_description', 'Learn how TripSpoiler handles personal data, cookies and analytics.')

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

        <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
                   text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                Privacy Policy
            </span>

            {{-- TITLE (FULL WIDTH) --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Your privacy
                <span class="text-[#C62E2E]">matters to us</span>
            </h1>

            {{-- DESCRIPTION (NO MAX-W, FULL WIDTH) --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                This page explains how TripSpoiler collects and uses information
                when you browse our website, and how we approach data protection
                with transparency and care.
            </p>

        </div>
    </section>

    <section class="bg-white py-14">
        <div class="max-w-6xl mx-auto px-4 leading-relaxed text-slate-700 space-y-6">


            <h2 class="font-semibold text-slate-900 text-lg">
                1. About TripSpoiler
            </h2>

            <p>
                TripSpoiler is a travel discovery website that provides guides about
                museums, attractions, activities and city experiences around the world.
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                2. Information we collect
            </h2>

            <p>
                We do not ask visitors to create accounts or submit personal details.
                However, like most websites, we may collect anonymous usage data such as:
            </p>

            <ul class="list-disc pl-6 space-y-2">
                <li>pages viewed</li>
                <li>time spent on the site</li>
                <li>device and browser type</li>
                <li>approximate location (country level)</li>
            </ul>

            <p>
                This information helps us understand how people use the site so we
                can improve content and user experience.
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                3. Cookies & Analytics
            </h2>

            <p>
                We may use cookies and analytics tools to better understand website traffic.
                These cookies do not personally identify you.
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                4. External links
            </h2>

            <p>
                TripSpoiler may include links to third-party websites.
                We are not responsible for the privacy practices of external platforms.
                We recommend reviewing their policies when visiting them.
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                5. Data security
            </h2>

            <p>
                We do not sell or trade user data.
                Any analytics data is stored securely by trusted providers.
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                6. Changes to this policy
            </h2>

            <p>
                This page may be updated in the future to reflect improvements
                or legal requirements.
            </p>



            <h2 class="font-semibold text-slate-900 text-lg">
                7. Contact
            </h2>

            <p>
                If you have questions about this policy,
                you can contact us at:
            </p>

            <p class="font-medium">
                <a href="{{ url('/contact') }}" class="text-[#C62E2E] hover:underline">
                    Contact page →
                </a>
            </p>



        </div>
    </section>

@endsection
