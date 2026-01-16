@extends('layouts.app')

@section('title', 'Travel Blog & Guides - TripSpoiler')

@section('content')
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                Travel stories & inspiration
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Travel, thoughtfully written<br>
                <span class="text-[#C62E2E]">guides, tips & real experiences</span>
            </h1>

            <p class="mt-4 text-slate-600 max-w-xl">
                Carefully written travel guides with real tips and honest experiences to help you plan better and travel
                with confidence.
            </p>

            <div class="mt-8 max-w-sm">
                <label class="text-sm font-medium text-slate-700">
                    Filter by city
                </label>

                <select id="cityFilter" class="mt-2 w-full rounded-2xl border border-[#F3D6D1] bg-white px-4 py-3">
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}" @selected($cityId == $city->id)>
                            {{ $city->getTranslation('name', $locale) }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </section>

    {{-- BLOG LIST --}}
    <div id="blogList">
        @include('blog.partials.list', ['blogs' => $blogs])
    </div>
@endsection

@section('scripts')
    <script>
        document.getElementById('cityFilter').addEventListener('change', function() {
            fetch(`{{ route('blog.index') }}?city_id=${this.value}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('blogList').innerHTML = html;
                });
        });
    </script>
@endsection
