@extends('layouts.app')

@section('title', $blog->getTranslation('meta_title', $locale))
@section('meta_description', $blog->getTranslation('meta_description', $locale))

@section('content')
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20 space-y-4">

            @if (!empty($hero['themes']))
                <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                    {{ implode(' • ', $hero['themes']) }}
                </span>
            @endif

            <h1 class="text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ $hero['title']['first'] }}

                @if (!empty($hero['title']['second']))
                    @if ($hero['title']['break'])
                        <br>
                    @endif
                    <span class="text-[#C62E2E]">{{ $hero['title']['second'] }}</span>
                @endif
            </h1>

            @if (!empty($hero['excerpt']))
                <p class="text-slate-600 max-w-xl">
                    {{ $hero['excerpt'] }}
                </p>
            @endif

        </div>
    </section>

    <x-venue-card source="blog" :source-id="$blog->id" :id="$blog->source_venue_id"/>

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
