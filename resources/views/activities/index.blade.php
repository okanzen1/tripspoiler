@extends('layouts.app')

@section('title', 'Activities - TripSpoiler')
@section('meta_description', 'Find the best activities, combo tours and city passes around the world.')

@section('content')

    <section class="bg-[#FFF5F3] border-b border-[#F3D6D1]">
        <div class="max-w-6xl mx-auto px-4 py-14 md:py-20">

            <span class="text-sm font-semibold text-[#C62E2E] tracking-wide">
                Things to do & experiences
            </span>

            <h1 class="mt-3 text-3xl md:text-5xl font-bold text-slate-900 leading-tight">
                Find the best activities<br>
                <span class="text-[#C62E2E]">in your city</span>
            </h1>

            <p class="mt-4 text-slate-600 max-w-xl">
                Choose a city to explore curated activities, guided tours, tickets and
                local experiences — carefully selected to help you plan smarter.
            </p>

            {{-- CITY SELECT --}}
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

    <div id="activityList">
        @include('activities.partials.list', ['activities' => $activities])
    </div>

@endsection

@section('scripts')
    <script>
        document.getElementById('cityFilter').addEventListener('change', function() {
            fetch(`{{ route('activities.index') }}?city_id=${this.value}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.text())
                .then(html => {
                    document.getElementById('activityList').innerHTML = html;
                });
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const track = document.getElementById('comboTrack');
            const next = document.getElementById('nextBtn');
            const prev = document.getElementById('prevBtn');

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
@endsection
