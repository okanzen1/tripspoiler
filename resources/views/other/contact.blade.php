@extends('layouts.app')

@section('title', 'Contact TripSpoiler')
@section('meta_description', 'Get in touch with TripSpoiler for travel & museum related questions.')

@section('content')

    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                Contact
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                We'd love to hear from you
            </h1>

        </div>
    </section>


    <section class="bg-white py-14">
        <div class="max-w-4xl mx-auto px-4">

            {{-- SUCCESS ALERT --}}
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl text-green-700">
                    {{ session('success') }}
                </div>
            @endif


            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                @csrf

                {{-- HONEYPOT FIELD (HIDDEN) --}}
                <div style="display:none;">
                    <input type="text" name="website" value="">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Your Name
                    </label>
                    <input type="text" name="name" required
                        class="w-full mt-1 border border-[#F3D6D1] rounded-2xl px-4 py-3 outline-none">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Your Email
                    </label>
                    <input type="email" name="email" required
                        class="w-full mt-1 border border-[#F3D6D1] rounded-2xl px-4 py-3 outline-none">
                </div>

                <div>
                    <label class="text-sm font-medium text-slate-700">
                        Message
                    </label>
                    <textarea name="message" rows="5" required
                        class="w-full mt-1 border border-[#F3D6D1] rounded-2xl px-4 py-3 outline-none"></textarea>
                </div>

                <button class="bg-[#C62E2E] hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-2xl">
                    Send message
                </button>
            </form>

            <p class="text-sm text-slate-500 mt-6">
                We usually reply within 1–2 business days.
            </p>

        </div>
    </section>

@endsection
