@extends('layouts.app')

@section('title', 'City Guides - TripSpoiler')
@section('meta_description', 'Meaningful, calm and curated city guides — created to help you plan smarter.')

@section('content')

    {{-- CITIES HERO — FULL WIDTH, LEFT ALIGNED --}}
    <section
        class="relative overflow-hidden
                bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        {{-- SOFT GLOW --}}
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-0
                   w-[520px] h-[520px]
                   bg-[#C62E2E]/10 rounded-full blur-[160px]">
            </div>
        </div>

        <div class="relative max-w-6xl mx-auto px-4 py-16 md:py-24">

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
                   text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                City Guides
            </span>

            {{-- TITLE --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Get to know
                <span id="currentCityName" class="text-[#C62E2E]">
                    {{ $currentCityName ?? 'a city' }}
                </span>
                before you go
            </h1>

            {{-- DESCRIPTION --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                Thoughtful guides to museums, neighbourhoods and calm experiences.
                TripSpoiler helps you understand a city beyond the highlights,
                so you arrive feeling prepared and confident.
            </p>

            {{-- CITY SELECT --}}
            <div class="mt-10 max-w-sm">

                {{-- CONTEXT LABEL --}}
                <label class="block text-sm font-semibold text-slate-800 mb-1">
                    Viewing guides for
                    <span id="currentCityLabel" class="text-[#C62E2E]">
                        {{ $currentCityName ?? 'this city' }}
                    </span>
                </label>

                <p class="text-xs text-slate-500 mb-3">
                    Change the city to explore another destination
                </p>

                {{-- SELECT --}}
                <div class="relative group">
                    <select id="cityFilter"
                        class="w-full appearance-none
                           bg-white
                           border border-[#F3D6D1]
                           rounded-full
                           px-6 py-4 pr-14
                           text-slate-900 text-base
                           shadow-sm
                           transition
                           hover:shadow-md
                           focus:outline-none
                           focus:border-[#C62E2E]
                           focus:ring-4 focus:ring-[#C62E2E]/15">

                        @foreach ($cities as $city)
                            <option value="{{ $city->id }}" @selected($cityId == $city->id)>
                                {{ $city->getTranslation('name', $locale) }}
                            </option>
                        @endforeach

                    </select>

                    {{-- CHEVRON --}}
                    <div
                        class="pointer-events-none absolute inset-y-0 right-5
                           flex items-center text-slate-400
                           transition group-focus-within:text-[#C62E2E]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
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
        const cityFilter = document.getElementById('cityFilter');
        const cityNameEl = document.getElementById('currentCityName');
        const cityLabelEl = document.getElementById('currentCityLabel');

        cityFilter.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const cityName = selectedOption.text;

            // Update hero texts
            if (cityNameEl) {
                cityNameEl.textContent = cityName;
            }

            if (cityLabelEl) {
                cityLabelEl.textContent = cityName;
            }

            // Fetch city list
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
