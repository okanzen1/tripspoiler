@props(['guide', 'city'])
{{-- Kısa bilgiler: şehir sayfasında rehberin başında tablo (panel: Şehir rehberi › şehir › Kısa bilgiler) --}}
@php
    $facts = $guide?->facts() ?? [];
    $half = (int) ceil(count($facts) / 2);
    $columns = count($facts) > 4 ? [array_slice($facts, 0, $half), array_slice($facts, $half)] : [$facts];
@endphp
@if ($facts)
    <section id="at-a-glance" aria-labelledby="at-a-glance-title" class="scroll-mt-32 bg-white pt-12 md:pt-16">
        <div class="mx-auto max-w-7xl px-4">
            <div class="rounded-[28px] bg-slate-50 p-5 ring-1 ring-slate-200/70 sm:p-8">
                <h2 id="at-a-glance-title" class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">
                    {{ __('cities-page.facts_title', ['city' => $city->name]) }}
                </h2>
                <div @class(['mt-6 grid gap-x-10 gap-y-0', 'lg:grid-cols-2' => count($columns) > 1])>
                    @foreach ($columns as $column)
                        <table class="w-full border-collapse text-start text-[15px]">
                            @if ($loop->first)
                                <caption class="sr-only">{{ __('cities-page.facts_title', ['city' => $city->name]) }}</caption>
                            @endif
                            <tbody>
                                @foreach ($column as $fact)
                                    <tr class="border-b border-slate-200/80 last:border-b-0 lg:last:border-b">
                                        <th scope="row" class="w-[44%] py-3.5 pe-4 text-start align-middle font-semibold text-slate-900 sm:w-2/5">
                                            <span class="flex items-center gap-2.5">
                                                <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-white text-[#C62E2E] shadow-sm ring-1 ring-slate-200/70">{!! \App\Support\FeatureIcons::svg($fact['icon'], 'size-4') !!}</span>
                                                {{ $fact['label'] }}
                                            </span>
                                        </th>
                                        <td class="py-3.5 align-middle leading-relaxed text-slate-600">{{ $fact['value'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endforeach
                </div>
                <p class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                    @if ($guide->facts_checked_at)
                        <span class="inline-flex items-center gap-1.5 font-semibold text-slate-600">
                            {!! \App\Support\FeatureIcons::svg('check', 'size-3.5') !!}
                            <time datetime="{{ $guide->facts_checked_at->toDateString() }}">{{ __('cities-page.facts_checked', ['date' => $guide->facts_checked_at->locale(app()->getLocale())->isoFormat('LL')]) }}</time>
                        </span>
                    @endif
                    <span>{{ __('cities-page.facts_note') }}</span>
                </p>
            </div>
        </div>
    </section>
@endif
