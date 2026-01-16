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

                <div class="mt-2 relative">
                    <select id="cityFilter"
                        class="w-full rounded-2xl border border-[#F3D6D1] bg-white px-4 py-3 pr-12 appearance-none">
                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected($cityId == $city->id)>
                                {{ $city->getTranslation('name', $locale) }}
                            </option>
                        @endforeach
                    </select>

                    {{-- OK (sağda ama içeride) --}}
                    <svg class="pointer-events-none absolute top-1/2 -translate-y-1/2 right-4 w-5 h-5 text-slate-400"
                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.24a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08z"
                            clip-rule="evenodd" />
                    </svg>
                </div>

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
