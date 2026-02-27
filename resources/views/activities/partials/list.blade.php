@php
    $types = $activities->pluck('activity_type')->unique()->values();
@endphp

<style>
    [x-cloak] {
        display: none !important;
    }
</style>

<section class="bg-[#F8FAFC] py-14" x-data="{ filter: 'all' }" x-cloak>

    <div class="max-w-7xl mx-auto px-4">
        
        {{-- TITLE --}}
        <h2 class="text-xl md:text-2xl font-bold text-slate-900">
            Experience the city more deeply
        </h2>

        <p class="text-slate-600 text-base md:text-lg max-w-2xl mb-6 mt-1">
            Carefully chosen experiences and passes to help you explore beyond the surface.
        </p>

        {{-- FILTER BAR --}}
        <div class="flex gap-3 flex-wrap mb-8">

            <button @click="filter='all'"
                :class="filter === 'all' ?
                    'bg-[#C62E2E] text-white' :
                    'bg-white text-slate-700 hover:bg-slate-100'"
                class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 shadow-sm">
                All
            </button>

            @foreach ($types as $type)
                <button @click="filter='{{ $type }}'"
                    :class="filter === '{{ $type }}' ?
                        'bg-[#C62E2E] text-white' :
                        'bg-white text-slate-700 hover:bg-slate-100'"
                    class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-300 shadow-sm">

                    {{ $type === 'pass'
                        ? 'City Pass'
                        : ($type === 'product'
                            ? 'Experiences'
                            : ($type === 'package'
                                ? 'Packages'
                                : ucfirst($type))) }}

                </button>
            @endforeach
        </div>

        {{-- GRID --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 items-stretch">

            @foreach ($activities as $activity)
                @php
                    $title = $activity->getTranslation('name', $locale);
                    $slug = $activity->getTranslation('slug', $locale);
                    $image = $activity->images->first();
                    $type = $activity->activity_type;
                @endphp

                <div x-show="filter==='all' || filter==='{{ $type }}'"
                    x-transition:enter="transition-all duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition-all duration-400 ease-in"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95" class="flex h-full">

                    <a href="{{ route('activities.show', $slug) }}"
                        class="group flex flex-col w-full h-full bg-white rounded-xl
                              overflow-hidden shadow-sm hover:shadow-md
                              transition-all duration-300">

                        {{-- IMAGE --}}
                        <div class="relative h-32 md:h-44 overflow-hidden bg-slate-100">
                            @if ($image)
                                <img src="{{ route('images.view', $image->id) }}" alt="{{ $title }}"
                                    class="w-full h-full object-cover
                                            transition duration-500
                                            group-hover:scale-105">
                            @else
                                <div class="w-full h-full bg-gradient-to-b from-slate-100 to-slate-200"></div>
                            @endif

                            {{-- BADGE --}}
                            @if ($type === 'pass')
                                <div class="absolute top-2 left-2">
                                    <span class="text-[10px] font-medium bg-white px-2 py-1 rounded-full shadow-sm">
                                        City Pass
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- CONTENT --}}
                        <div class="p-4 flex flex-col flex-1">
                            <span class="text-[11px] text-slate-500 mb-1">
                                {{ $type === 'pass'
                                    ? 'City Pass'
                                    : ($type === 'product'
                                        ? 'Experience'
                                        : ($type === 'package'
                                            ? 'Package'
                                            : ucfirst($type))) }}
                            </span>

                            <h3 class="text-sm font-semibold text-slate-900 leading-snug line-clamp-2">
                                {{ $title }}
                            </h3>

                            <div class="mt-auto pt-4">
                                <span
                                    class="text-sm font-medium text-[#C62E2E]
                                             group-hover:translate-x-1
                                             inline-block transition">
                                    View details →
                                </span>
                            </div>
                        </div>

                    </a>

                </div>
            @endforeach

        </div>

        <div class="pt-20">
            <x-most-popular-blog source="home" :source-id="null"  :city-id="$cityId"  />
        </div>

    </div>
</section>

@push('scripts')
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endpush
