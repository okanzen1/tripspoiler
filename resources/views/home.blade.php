@extends('layouts.app')

@section('title', __('home.title'))
@section('meta_description', __('home.meta_description'))

@section('content')
    @include('partials.home-hero', $hero)
    @include('partials.home-features', ['features' => $features])

    <x-steps-section />
    <x-most-popular-activities source="home" :source-id="null" limit="12" />
    <x-city-entry-section />
    <x-most-popular-blog />
    <x-tripspoiler-intro />
    <x-social-presence-section />
    <x-reviews :source="'home'" :source-id="null" :sectionHeader="true" :reviewSummary="true" :testimonials="true" />
    <x-faq :source="'home'" :source-id="null" />
@endsection

