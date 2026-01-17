@extends('layouts.app')

@section('title', 'City Guides - TripSpoiler')
@section('meta_description', 'Meaningful, calm and curated city guides — created to help you plan smarter.')

@section('content')

    {{-- HERO (CITY CONCEPT) --}}
    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-16 grid md:grid-cols-2 gap-10 items-center">

            {{-- LEFT --}}
            <div>

                <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                    City Guides
                </span>

                <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                    Get to know a city<br>
                    <span class="text-[#C62E2E]">before you step inside it</span>
                </h1>

                <p class="mt-4 text-slate-600 max-w-xl leading-relaxed">
                    TripSpoiler focuses on the parts of a city that truly matter:
                    museums, neighbourhoods, calm experiences and simple travel advice.
                    Our guides help you feel prepared — not overwhelmed.
                </p>

                {{-- SELECT --}}
                <div class="mt-8 max-w-sm">
                    <label class="text-sm font-medium text-slate-700">
                        Select a city
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
        </div>
    </section>

    <div id="cityList">
        @include('cities.partials.list', ['cities' => $cities])
    </div>

@endsection

@section('scripts')
    <script>
        document.getElementById('cityFilter').addEventListener('change', function() {
            fetch(`{{ route('cities.index') }}?city_id=${this.value}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('cityList').innerHTML = html;
                });
        });
    </script>
@endsection
