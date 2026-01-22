@extends('layouts.app')

@section('title', $blog->getTranslation('meta_title', $locale))
@section('meta_description', $blog->getTranslation('meta_description', $locale))

@section('content')
    {{-- BLOG DETAIL HERO --}}
    <section class="relative overflow-hidden
                bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24 space-y-4">

            @if (!empty($hero['themes']))
                <span
                    class="inline-block text-xs font-semibold tracking-wide uppercase
                         text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                    {{ implode(' • ', $hero['themes']) }}
                </span>
            @endif

            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ $hero['title']['first'] }}

                @if (!empty($hero['title']['second']))
                    @if ($hero['title']['break'])
                        <br>
                    @endif
                    <span class="text-[#C62E2E]">
                        {{ $hero['title']['second'] }}
                    </span>
                @endif
            </h1>

            @if (!empty($hero['excerpt']))
                <p class="text-slate-600 text-base md:text-lg leading-relaxed">
                    {{ $hero['excerpt'] }}
                </p>
            @endif

        </div>
    </section>


    {{-- <x-venue-card source="blog" :source-id="$blog->id" :id="$blog->source_venue_id"/> --}}

    <section class="bg-white py-10">
        <div class="max-w-6xl mx-auto px-4">
            @foreach ($blog->contents as $content)
                <h2 class="text-xl font-bold text-slate-900">
                    {{ $content->getTranslation('title', $locale) }}
                </h2>

                <p class="mt-3 text-slate-600 max-w-3xl leading-relaxed">
                    {!! $content->getTranslation('content', $locale) !!}
                </p>
            @endforeach
        </div>
    </section>

    <x-faq source="blog" :source-id="$blog->id" />

@endsection
