{{--
    "Nasıl çalışır" (panel > Site ayarları > Nasıl çalışır): numaralı adımlar. Her yerde aynı düzen:
    rozetler üstte kesikli yolla bağlı, kartlar altında. Masaüstünde hepsi görünür; telefon/tablette yana kayar.
    Adımlar ve yazılar: App\View\Components\StepsSection.
--}}
@php
    $count = $steps->count();
    $desktopCols = ['1' => 'lg:grid-cols-1', '2' => 'lg:grid-cols-2', '3' => 'lg:grid-cols-3'][$count] ?? 'lg:grid-cols-4';
@endphp

<section class="relative overflow-hidden bg-white py-16 md:py-24" x-data="{ active: 0 }">
    {{-- hafif nokta dokusu --}}
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(15,23,42,0.06)_1px,transparent_0)] [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,transparent,black_30%,black_70%,transparent)]"></div>

    <div class="relative mx-auto max-w-7xl px-4">

        {{-- BAŞLIK --}}
        <div class="mx-auto max-w-2xl text-center">
            @if (filled($texts['eyebrow']))
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ $texts['eyebrow'] }}<span class="h-px w-6 bg-[#C62E2E]"></span>
                </p>
            @endif
            <h2 class="mt-4 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                {{ $texts['title'] }}
                <span class="text-[#C62E2E]">{{ $texts['title_highlight'] }}</span>
            </h2>
            @if (filled($texts['description']))
                <p class="mt-4 text-base text-slate-600 md:text-lg">{{ $texts['description'] }}</p>
            @endif
        </div>

        {{-- ADIMLAR --}}
        <ol x-ref="steps" @scroll.debounce.60ms="active = Math.round(Math.abs($el.scrollLeft) / ($el.querySelector('li').offsetWidth + 16))"
            class="-mx-4 mt-10 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 pb-6 pt-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden
                   md:mt-14 lg:mx-0 lg:grid {{ $desktopCols }} lg:gap-6 lg:overflow-visible lg:px-0">
            @foreach ($steps as $step)
                <li class="relative flex w-[80%] shrink-0 snap-start flex-col items-center sm:w-[46%] lg:w-auto">

                    {{-- yol çizgisi: bu rozetten bir sonrakine --}}
                    @unless ($loop->last)
                        <span aria-hidden="true" class="pointer-events-none absolute left-1/2 top-7 w-[calc(100%+1rem)] border-t-2 border-dashed border-slate-200 lg:w-[calc(100%+1.5rem)] rtl:left-auto rtl:right-1/2"></span>
                    @endunless

                    {{-- numara rozeti --}}
                    <span class="relative z-10 grid size-14 shrink-0 place-items-center rounded-full bg-white ring-1 ring-slate-200 shadow-[0_10px_24px_-14px_rgba(15,23,42,0.35)]">
                        <span class="grid size-10 place-items-center rounded-full bg-gradient-to-br from-[#E0393E] to-[#A82424] text-sm font-extrabold text-white">
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>
                    </span>

                    {{-- kart --}}
                    @php $url = $step->cta ? $step->url() : null; @endphp
                    <{{ $url ? 'a' : 'div' }} @if ($url) href="{{ $url }}" @if ($step->isExternal()) target="_blank" rel="noopener noreferrer" @endif @endif
                        class="group relative mt-5 flex w-full flex-1 flex-col items-center overflow-hidden rounded-3xl bg-white p-6 text-center ring-1 ring-slate-200/80 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                               shadow-[0_1px_2px_rgba(15,23,42,0.04),0_14px_36px_-24px_rgba(15,23,42,0.3)]
                               hover:-translate-y-1.5 hover:ring-[#C62E2E]/30 hover:shadow-[0_26px_56px_-28px_rgba(198,46,46,0.45)] lg:p-8">

                        <span aria-hidden="true" class="pointer-events-none absolute -bottom-6 -right-2 select-none text-[120px] font-extrabold leading-none tracking-tighter text-slate-50 transition duration-500 group-hover:text-[#C62E2E]/[0.06] rtl:-left-2 rtl:right-auto">
                            {{ $loop->iteration }}
                        </span>

                        <span class="relative grid size-12 place-items-center rounded-2xl bg-[#C62E2E]/[0.08] text-[#C62E2E] transition duration-500 group-hover:scale-110 group-hover:bg-[#C62E2E] group-hover:text-white">
                            {!! \App\Support\FeatureIcons::svg($step->icon, 'size-6') !!}
                        </span>

                        <p class="relative mt-4 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">
                            {{ __('steps-section.step_label') }} {{ $loop->iteration }}
                        </p>
                        <h3 class="relative mt-1.5 text-xl font-bold tracking-tight text-slate-900">{{ $step->title }}</h3>
                        @if (filled($step->text))
                            <p class="relative mt-2 text-[15px] leading-relaxed text-slate-600">{{ $step->text }}</p>
                        @endif

                        @if ($url)
                            <span class="relative mt-auto inline-flex items-center gap-1.5 pt-5 text-sm font-semibold text-[#C62E2E]">
                                <span class="border-b border-[#C62E2E]/30 pb-0.5 transition group-hover:border-[#C62E2E]">{{ $step->cta }}</span>
                                <span aria-hidden="true" class="transition-transform duration-300 group-hover:translate-x-1 rtl:rotate-180 rtl:group-hover:-translate-x-1">→</span>
                            </span>
                        @endif
                    </{{ $url ? 'a' : 'div' }}>
                </li>
            @endforeach
        </ol>

        {{-- telefon/tablet: kaç adım olduğunu gösteren noktalar --}}
        @if ($count > 1)
            <div class="mt-2 flex justify-center gap-1.5 lg:hidden" aria-hidden="true">
                @foreach ($steps as $step)
                    <span :class="active === {{ $loop->index }} ? 'w-6 bg-[#C62E2E]' : 'w-1.5 bg-slate-300'" class="h-1.5 rounded-full transition-all duration-300"></span>
                @endforeach
            </div>
        @endif
    </div>
</section>
