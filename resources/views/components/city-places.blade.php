@props(['city'])
{{-- Şehir sayfası: gezilecek yerler (panel: Yerler) + pass / kombo karşılaştırma tablosu (hangi pass hangi yeri içeriyor) --}}
@php
    $places = \App\Models\Attraction::visible()->where('city_id', $city->id)->with(['activities' => fn ($q) => $q->visible()])
        ->orderBy('sort_order')->orderBy('id')->get();
    $columns = $places->take(6);
    $bundles = \App\Models\Activity::visible()->where('city_id', $city->id)->whereIn('type', ['pass', 'package'])
        ->with('attractions:id')->orderByRaw("CASE WHEN type = 'pass' THEN 0 ELSE 1 END")->orderBy('sort_order')->get()
        ->filter(fn ($a) => $a->attractions->whereIn('id', $columns->pluck('id'))->isNotEmpty())->values();
@endphp
@if ($places->isNotEmpty())
    <section id="places" class="scroll-mt-32 bg-white pt-12 md:pt-16">
        <div class="mx-auto max-w-7xl px-4">
            <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.places_title', ['city' => $city->name]) }}</h2>
            <ul class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                @foreach ($places as $place)
                    <li>
                        <a href="{{ route('attractions.show', $place->slug) }}" class="group relative isolate flex h-40 flex-col justify-end overflow-hidden rounded-3xl bg-slate-800 p-4 text-white sm:h-52 sm:p-5">
                            @if ($place->image_path)<img src="{{ $place->imageUrl('sm') }}" alt="{{ $place->name }}" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover transition duration-700 group-hover:scale-105">@endif
                            <span class="absolute inset-0 -z-10 bg-gradient-to-t from-slate-950/90 via-slate-950/35 to-transparent"></span>
                            <span class="text-base font-extrabold leading-tight sm:text-lg">{{ $place->name }}</span>
                            <span class="mt-1 text-xs font-semibold text-white/75">{{ __('attraction-page.see_tickets') }} →</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($bundles->count() >= 2)
                <div class="mt-12">
                    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.compare_title', ['city' => $city->name]) }}</h2>
                    <p class="mt-2 text-slate-500">{{ __('attraction-page.compare_text') }}</p>
                    <div class="relative mt-5 overflow-x-auto rounded-3xl ring-1 ring-slate-200">
                        <table class="w-full min-w-[40rem] border-collapse text-sm">
                            <thead>
                                <tr class="bg-slate-900 text-white">
                                    <th scope="col" class="px-4 py-3.5 text-start font-bold">{{ __('attraction-page.compare_product') }}</th>
                                    @foreach ($columns as $col)
                                        <th scope="col" class="px-3 py-3.5 text-center font-bold">{{ $col->name }}</th>
                                    @endforeach
                                    <th scope="col" class="px-3 py-3.5"><span class="sr-only">{{ __('attraction-page.compare_view') }}</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bundles as $bundle)
                                    <tr class="border-t border-slate-200 even:bg-slate-50">
                                        <th scope="row" class="max-w-[16rem] px-4 py-3.5 text-start font-semibold text-slate-900">{{ $bundle->name }}</th>
                                        @foreach ($columns as $col)
                                            <td class="px-3 py-3.5 text-center">
                                                @if ($bundle->attractions->contains('id', $col->id))
                                                    <span class="inline-grid size-7 place-items-center rounded-full bg-emerald-500 text-white" aria-label="✓"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                                                @else
                                                    <span class="text-slate-300" aria-label="—">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-3.5 text-end"><a href="{{ route('activities.show', $bundle->localSlug()) }}" class="whitespace-nowrap font-bold text-[#C62E2E] hover:underline">{{ __('attraction-page.compare_view') }} →</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
