@extends('layouts.app')

@section('title', __('home.title'))
@section('meta_description', __('home.meta_description'))

@section('content')
    @include('partials.home-hero', $hero)
    {{-- Sıra panelden (Yönetim › Bölümler, sürükle-bırak): App\Support\HomeSections --}}
    @foreach (\App\Support\HomeSections::order() as $section)
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
    @endforeach
@endsection

