@extends('layouts.app')

@section('title', __('error.title'))
@section('meta_description', __('error.meta_description'))

@section('content')

    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1] min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto px-4 text-center w-full">

            <img src="{{ asset('images/logo-tripspoiler-two.png') }}" alt="TripSpoiler"
                class="mx-auto h-48 md:h-48 mb-6 drop-shadow-md logo-swing" />

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide uppercase">
                {{ __('error.eyebrow') }}
            </span>

            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900">
                {{ __('error.heading') }}
            </h1>

            <p class="mt-4 text-slate-600 max-w-xl mx-auto leading-relaxed">
                {{ __('error.desc') }}
            </p>

            <div class="mt-8 max-w-xl mx-auto">
               <x-search-modal />
            </div>

            <div class="mt-10 flex flex-wrap justify-center gap-4 text-sm font-medium">
                <a href="{{ url('/') }}" class="px-5 py-3 rounded-2xl bg-white border">
                    {{ __('error.home') }}
                </a>

                <a href="{{ url('/cities') }}" class="px-5 py-3 rounded-2xl bg-white border">
                    {{ __('error.cities') }}
                </a>

                <a href="{{ url('/activities') }}" class="px-5 py-3 rounded-2xl bg-white border">
                    {{ __('error.activities') }}
                </a>

                <a href="{{ url('/blog') }}" class="px-5 py-3 rounded-2xl bg-white border">
                    {{ __('error.blog') }}
                </a>
            </div>


        </div>
    </section>

@endsection