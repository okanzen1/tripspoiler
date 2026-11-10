{{--
    Footer'ın üstünde "Destekçilerimiz ve partnerlerimiz": panelde "partnerler şeridinde" işaretli, aktif ve logosu olan
    partnerler sonsuz döngüde kayar (tek partner varsa logosu tekrarlanır).
--}}
@php
    $enabled = \App\Support\SiteSections::enabled('partners') && \App\Support\PageSections::fixedEnabled('partners');
    $texts = \App\Models\SectionText::texts('partners', app()->getLocale(), [
        'eyebrow' => __('partners-strip.eyebrow'), 'title' => __('partners-strip.title'), 'title_highlight' => '',
    ]);
    $partners = \App\Models\Partner::where('active', true)->where('show_on_site', true)->whereNotNull('logo_path')->orderBy('sort_order')->orderBy('name')->get();
    // Tek partner olsa da şerit dolsun diye logolar tekrarlanır; iki kopya yan yana sonsuz kayar
    $moving = true;
    $loopItems = $partners->isEmpty() ? $partners : collect(array_fill(0, (int) ceil(10 / $partners->count()), $partners))->flatten(1);
@endphp

@if ($enabled && $partners->isNotEmpty())
    <section class="partners-strip relative overflow-hidden border-t border-slate-200/70 bg-white py-12 md:py-16" aria-label="{{ $texts['title'] }}">
        <div class="mx-auto max-w-7xl px-4">
            <div class="flex flex-col items-center gap-6 md:flex-row md:gap-10">
                <div class="shrink-0 text-center md:text-left rtl:md:text-right">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">{{ $texts['eyebrow'] }}</p>
                    <p class="mt-1.5 text-2xl font-extrabold leading-tight tracking-tight text-slate-900 md:max-w-[15rem]">{{ $texts['title'] }} <span class="text-[#C62E2E]">{{ $texts['title_highlight'] }}</span></p>
                </div>

                <div class="relative min-w-0 flex-1 overflow-hidden" dir="ltr">
                    @if ($moving)
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-16 bg-gradient-to-r from-white to-transparent"></div>
                        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-16 bg-gradient-to-l from-white to-transparent"></div>
                    @endif
                    <div @class(['flex items-center', 'partners-track w-max' => $moving, 'justify-center md:justify-start gap-6' => !$moving])
                        style="--partners-duration: {{ max(25, $loopItems->count() * 4) }}s">
                        @foreach ($moving ? [0, 1] : [0] as $copy)
                            <ul class="flex shrink-0 items-center gap-8 {{ $moving ? 'pr-8' : '' }}" @if ($copy) aria-hidden="true" @endif>
                                @foreach ($loopItems as $partner)
                                    <li>
                                        <{{ $partner->website ? 'a' : 'span' }} @if ($partner->website) href="{{ $partner->website }}" target="_blank" rel="noopener sponsored" @endif
                                            @if ($copy) tabindex="-1" @endif title="{{ $partner->name }}"
                                            class="group grid h-28 w-60 place-items-center rounded-3xl bg-slate-50 px-8 {{ $partner->website ? 'cursor-pointer' : '' }} ring-1 ring-slate-200/70 transition duration-300 hover:-translate-y-0.5 hover:bg-white hover:shadow-[0_14px_30px_-18px_rgba(15,23,42,0.35)]">
                                            <img src="{{ $partner->logoUrl() }}" alt="{{ $partner->name }}" loading="lazy" decoding="async"
                                                class="max-h-14 w-auto max-w-full object-contain transition duration-300 group-hover:scale-105">
                                        </{{ $partner->website ? 'a' : 'span' }}>
                                    </li>
                                @endforeach
                            </ul>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
