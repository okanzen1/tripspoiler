@extends('layouts.app')

@section('title', __('home.title'))
@section('meta_description', __('home.meta_description'))

{{-- Kapak görseli (sayfanın en büyük öğesi) HTML'in geri kalanını beklemeden inmeye başlasın --}}
@push('preload')
    @php $banner = $hero['banner']; @endphp
    @if ($banner['mobile'])
        <link rel="preload" as="image" href="{{ $banner['mobile'] }}" media="(max-width: 767px)" fetchpriority="high">
        <link rel="preload" as="image" href="{{ $banner['src'] }}" @if ($banner['srcset']) imagesrcset="{{ $banner['srcset'] }}" imagesizes="100vw" @endif media="(min-width: 768px)" fetchpriority="high">
    @else
        <link rel="preload" as="image" href="{{ $banner['src'] }}" @if ($banner['srcset']) imagesrcset="{{ $banner['srcset'] }}" imagesizes="100vw" @endif fetchpriority="high">
    @endif
@endpush

@section('content')
    @include('partials.home-hero', $hero)
    {{-- Sıra panelden (Yönetim › Bölümler, sürükle-bırak): App\Support\HomeSections --}}
    @foreach (\App\Support\HomeSections::order() as $section)
        <div data-nav-section="{{ $section }}" class="scroll-mt-28">
        @switch($section)
            @case('features') @include('partials.home-features', ['features' => $features]) @break
            @case('steps') <x-steps-section /> @break
            @case('popular') <x-most-popular-activities source="home" :source-id="null" limit="12" /> @break
            @case('city_entry') <x-city-entry-section /> @break
            @case('blog') <x-most-popular-blog /> @break
            @case('intro') <x-tripspoiler-intro /> @break
            @case('social') <x-social-presence-section /> @break
            @case('reviews') <x-reviews :source="'home'" :source-id="null" :sectionHeader="true" :reviewSummary="true" :testimonials="true" /> @break
            @case('faq') <x-faq :source="'home'" :source-id="null" /> @break
        @endswitch
        </div>
    @endforeach

    {{-- telefonda bölüm kılavuzu --}}
    <x-section-nav page="home" :keys="\App\Support\HomeSections::order()" />
@endsection

