@extends('layouts.app')

@section('title', __('activities.index_meta_title'))
@section('meta_description', __('activities.index_meta_description'))

@section('content')

    {{-- ACTIVITIES HERO — FULL WIDTH, LEFT ALIGNED --}}
    <section class="relative overflow-hidden
                bg-gradient-to-b from-[#FFF5F3] via-[#FFF8F6] to-white">

        {{-- SOFT GLOW --}}
        <div class="absolute inset-0 pointer-events-none">
            <div
                class="absolute -top-32 left-0
                   w-[520px] h-[520px]
                   bg-[#C62E2E]/10 rounded-full blur-[160px]">
            </div>
        </div>

        <div class="relative max-w-7xl mx-auto px-6 md:px-8 py-16 md:py-24">

            {{-- EYEBROW --}}
            <span
                class="inline-block text-xs font-semibold tracking-wide uppercase
                   text-[#C62E2E] bg-[#C62E2E]/10 px-4 py-1 rounded-full">
                {{ __('activities-hero.eyebrow') }}
            </span>

            {{-- TITLE (FULL WIDTH) --}}
            <h1 class="mt-4 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                {{ __('activities-hero.title') }}
                <span id="currentCityName" class="text-[#C62E2E]">
                    {{ __('activities-hero.across') }} {{ $currentCityName }}
                </span>
            </h1>

            {{-- DESCRIPTION (FULL WIDTH, NO MAX-W) --}}
            <p class="mt-5 text-slate-600 leading-relaxed text-base md:text-lg">
                {{ __('activities-hero.description') }}
            </p>

            <div class="mt-10 flex flex-col md:flex-row gap-6 md:items-end">

                {{-- CITY FILTER (AYNI BOYUTTA) --}}
                <div class="max-w-sm w-full">

                    <label class="block text-sm font-semibold text-slate-800 mb-1">
                        {{ __('activities-hero.showing_in') }}
                        <span id="currentCityLabel" class="text-[#C62E2E]">
                            {{ $currentCityName ?? __('activities-hero.this_city') }}
                        </span>
                    </label>

                    <p class="text-xs text-slate-500 mb-3">
                        {{ __('activities-hero.change_city_hint') }}
                    </p>

                    <div class="relative group">
                        <select id="cityFilter"
                            class="w-full appearance-none
                            bg-white border border-[#F3D6D1]
                            rounded-full px-6 py-4 pr-14
                            text-slate-900 text-base
                            shadow-sm transition hover:shadow-md
                            focus:outline-none
                            focus:border-[#C62E2E]
                            focus:ring-4 focus:ring-[#C62E2E]/15">

                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" @selected($cityId == $city->id)>
                                    {{ $city->getTranslation('name', $locale) }}
                                </option>
                            @endforeach

                        </select>

                        <div class="pointer-events-none absolute inset-y-0 right-5 flex items-center text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M6 9l6 6 6-6" />
                            </svg>
                        </div>
                    </div>

                </div>


                {{-- SEARCH (KALAN ALANI DOLDURUR) --}}
                <div class="flex-1">
                    <x-search-modal full="true" />
                </div>

            </div>


        </div>
    </section>

    <div id="activityList">
        @include('activities.partials.list', ['activities' => $activities])
    </div>

    <x-reviews :source="'activity'" :source-id="null" :color="'bg-[#F8FAFC]'" :sectionHeader="false" :reviewSummary="false" :testimonials="true" />
    <x-social-presence-section :color="'bg-[#F8FAFC]'" />
@endsection

@push('scripts')
    <script>
        const cityFilter = document.getElementById('cityFilter');
        const cityNameEl = document.getElementById('currentCityName');
        const cityLabelEl = document.getElementById('currentCityLabel');

        cityFilter.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const cityName = selectedOption.text;

            // Update hero text
            if (cityNameEl) {
                cityNameEl.textContent = "{{ __('activities.across') }} " + cityName;
            }

            if (cityLabelEl) {
                cityLabelEl.textContent = cityName;
            }

            // Fetch activities (existing logic)
            fetch(`{{ route('activities.index') }}?city_id=${this.value}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('activityList').innerHTML = html;
                    initSwipers();
                });
        });
    </script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const track = document.getElementById('comboTrack');
            const next = document.getElementById('nextBtn');
            const prev = document.getElementById('prevBtn');

            if (!track || !next || !prev) return;

            const step = 340;
            let offset = 0;

            const maxOffset = -(track.scrollWidth - track.parentElement.clientWidth);

            next.addEventListener("click", () => {
                offset -= step;
                if (offset < maxOffset) offset = maxOffset;
                track.style.transform = `translateX(${offset}px)`;
            });

            prev.addEventListener("click", () => {
                offset += step;
                if (offset > 0) offset = 0;
                track.style.transform = `translateX(${offset}px)`;
            });

        });
    </script>
@endpush
