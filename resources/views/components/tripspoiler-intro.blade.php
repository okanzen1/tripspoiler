{{-- "TripSpoiler nedir": solda tanıtım ve butonlar, sağda sitedeki sayılarla dört kısayol kartı --}}
@php
    // Başlık yazıları panelden (Site ayarları > Bölüm yazıları); yoksa dil dosyası
    $texts = \App\Models\SectionText::texts('intro', app()->getLocale(), [
        'eyebrow' => __('tripspoiler-intro.eyebrow'), 'title' => __('tripspoiler-intro.title'),
        'title_highlight' => __('tripspoiler-intro.title_highlight'), 'description' => __('tripspoiler-intro.desc'),
    ]);
@endphp
@php
    $cards = [
        ['key' => 'cities', 'url' => route('cities.index'), 'img' => 'images/tripspoiler-statement.webp',
         'icon' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>'],
        ['key' => 'experiences', 'url' => \App\Support\PageSections::activitiesLink(), 'img' => 'images/all-activities-cover.webp',
         'icon' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/>'],
        ['key' => 'passes', 'url' => \App\Support\PageSections::activitiesLink(), 'img' => null,
         'icon' => '<path d="M19 5 5 19"/><circle cx="7" cy="7" r="2.5"/><circle cx="17" cy="17" r="2.5"/>'],
        ['key' => 'stories', 'url' => route('blog.index'), 'img' => null,
         'icon' => '<path d="M4 5a2 2 0 0 1 2-2h12v16H6a2 2 0 0 0-2 2z"/><path d="M8 7h6M8 11h4"/>'],
    ];
@endphp

<section class="relative overflow-hidden bg-slate-50 py-16 md:py-24">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 lg:grid-cols-12 lg:gap-16">

        {{-- SOL --}}
        <div class="lg:col-span-5">
            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                <span class="h-px w-6 bg-[#C62E2E]"></span>{{ $texts['eyebrow'] }}
            </p>
            <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                {{ $texts['title'] }} <span class="text-[#C62E2E]">{{ $texts['title_highlight'] }}</span>
            </h2>
            <p class="mt-5 text-base font-medium text-slate-800 md:text-lg">{{ $texts['description'] }}</p>
            <p class="mt-3 text-[15px] leading-relaxed text-slate-600">{{ __('tripspoiler-intro.p2') }}</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('cities.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-[#C62E2E] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[#C62E2E]/20 transition hover:bg-[#A82424] active:scale-[0.98]">
                    {{ __('tripspoiler-intro.explore_cities') }} <span aria-hidden="true" class="rtl:rotate-180">→</span>
                </a>
                <a href="{{ \App\Support\PageSections::activitiesLink() }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-slate-900 ring-1 ring-slate-200 transition hover:ring-slate-400 active:scale-[0.98]">
                    {{ __('tripspoiler-intro.discover_activities') }}
                </a>
            </div>
        </div>

        {{-- SAĞ: KARTLAR --}}
        <div class="grid grid-cols-2 gap-4 lg:col-span-7 lg:gap-5">
            @foreach ($cards as $card)
                @php $dark = (bool) $card['img']; @endphp
                <a href="{{ $card['url'] }}" @class([
                    'group relative isolate flex min-h-[11rem] flex-col overflow-hidden rounded-3xl p-5 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1.5 sm:min-h-[13rem] sm:p-6',
                    'bg-slate-900 text-white shadow-[0_24px_50px_-28px_rgba(2,6,23,0.6)]' => $dark,
                    'bg-white text-slate-900 ring-1 ring-slate-200/80 shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)] hover:ring-[#C62E2E]/30' => !$dark,
                    'lg:translate-y-6 lg:hover:translate-y-4' => $loop->odd === false,
                ])>
                    @if ($dark)
                        <img src="{{ asset($card['img']) }}" alt="" loading="lazy" decoding="async" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-60 transition duration-700 group-hover:scale-105">
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950 via-slate-950/50 to-slate-950/10"></div>
                    @endif
                    <span @class(['grid size-11 place-items-center rounded-2xl transition duration-500 group-hover:scale-110',
                                  'bg-white/15 ring-1 ring-white/20 backdrop-blur' => $dark,
                                  'bg-[#C62E2E]/[0.08] text-[#C62E2E] group-hover:bg-[#C62E2E] group-hover:text-white' => !$dark])>
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $card['icon'] !!}</svg>
                    </span>
                    <span class="mt-auto pt-6">
                        <span @class(['block text-xs font-bold uppercase tracking-[0.14em]', 'text-white/70' => $dark, 'text-[#C62E2E]' => !$dark])>
                            {{ __('tripspoiler-intro.count_' . $card['key'], ['count' => $counts[$card['key']]]) }}
                        </span>
                        <span class="mt-1 flex items-center justify-between gap-2 text-lg font-extrabold tracking-tight sm:text-xl">
                            {{ __('tripspoiler-intro.' . $card['key']) }}
                            <span aria-hidden="true" class="transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180">→</span>
                        </span>
                        <span @class(['mt-1 block text-sm leading-snug', 'text-white/75' => $dark, 'text-slate-500' => !$dark])>{{ __('tripspoiler-intro.' . $card['key'] . '_desc') }}</span>
                    </span>
                </a>
            @endforeach
        </div>
    </div>
</section>
