{{--
    Hero'nun altındaki özellik kartları (panel > Site ayarları > Özellik kartları).
    Kartlar sonsuz döngüde yavaşça kayar; üstüne gelince durur. Kısa listede ekran boş kalmasın diye tekrarlanır.
    "Hareketi azalt" açıksa kayma durur, kartlar elle kaydırılır.
--}}
@if ($features->isNotEmpty() && \App\Support\SiteSections::enabled('features'))
    @php
        $repeat = max(1, (int) ceil(8 / $features->count()));
        $loopItems = collect(array_fill(0, $repeat, $features))->flatten(1);
        $duration = max(30, $loopItems->count() * 6);
    @endphp

    <section class="features-marquee relative border-b border-slate-200/70 bg-slate-50 py-10 md:py-14" aria-label="TripSpoiler">
        {{-- kenarlarda yumuşak geçiş --}}
        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 w-10 bg-gradient-to-r from-slate-50 to-transparent md:w-28"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 z-10 w-10 bg-gradient-to-l from-slate-50 to-transparent md:w-28"></div>

        <div class="features-scroller overflow-hidden" dir="ltr">
            <div class="features-track flex w-max py-3" style="--features-duration: {{ $duration }}s">
                @foreach ([0, 1] as $copy)
                    <ul class="flex shrink-0 gap-4 pr-4 md:gap-5 md:pr-5" @if ($copy) aria-hidden="true" @endif>
                        @foreach ($loopItems as $feature)
                            <li @if ($copy || $loop->index >= $features->count()) aria-hidden="true" @endif
                                class="group relative w-[260px] shrink-0 overflow-hidden rounded-3xl bg-white p-6 ring-1 ring-slate-200/80
                                       shadow-[0_1px_2px_rgba(15,23,42,0.04),0_12px_32px_-20px_rgba(15,23,42,0.25)]
                                       transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                       hover:-translate-y-1.5 hover:ring-[#C62E2E]/30 hover:shadow-[0_24px_50px_-24px_rgba(198,46,46,0.45)] md:w-[290px]"
                                dir="{{ LaravelLocalization::getCurrentLocaleDirection() }}">
                                {{-- köşede silik büyük ikon --}}
                                <span class="pointer-events-none absolute -right-4 -top-4 text-slate-100 transition duration-500 group-hover:rotate-12 group-hover:text-[#C62E2E]/10">
                                    {!! $feature->iconHtml('size-28') !!}
                                </span>

                                <span class="relative grid size-12 place-items-center rounded-2xl bg-gradient-to-br from-[#E0393E] to-[#A82424] text-white
                                             shadow-lg shadow-[#C62E2E]/25 transition duration-500 group-hover:scale-110 group-hover:-rotate-6">
                                    {!! $feature->iconHtml('size-6') !!}
                                </span>
                                <p class="relative mt-5 text-[17px] font-bold leading-snug tracking-tight text-slate-900">{{ $feature->title }}</p>
                                @if (filled($feature->text))
                                    <p class="relative mt-2 text-sm leading-relaxed text-slate-500">{{ $feature->text }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    </section>
@endif
