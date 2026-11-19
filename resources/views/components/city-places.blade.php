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
                {{-- Karşılaştırma: her pass / kombo bir kart; kaç yeri kapsadığı çubukla, yerler tek tek ✓ / — ile.
                     En çok yeri kapsayan(lar) "En çok yer" rozeti alır. Telefonda alt alta, yana kaydırma yok. --}}
                @php
                    $total = $columns->count();
                    $covered = $bundles->mapWithKeys(fn ($b) => [$b->id => $b->attractions->whereIn('id', $columns->pluck('id'))->count()]);
                    $best = $covered->max();
                @endphp
                <div class="mt-14">
                    <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.compare_title', ['city' => $city->name]) }}</h2>
                    <p class="mt-2 text-slate-500">{{ __('attraction-page.compare_text') }}</p>
                    <ul class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($bundles->sortByDesc(fn ($b) => $covered[$b->id])->values() as $bundle)
                            @php $count = $covered[$bundle->id]; $isPass = ($bundle->type instanceof \BackedEnum ? $bundle->type->value : $bundle->type) === 'pass'; @endphp
                            <li>
                                <a href="{{ route('activities.show', $bundle->localSlug()) }}"
                                    class="group flex h-full flex-col overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-slate-200 transition duration-300 hover:-translate-y-1 hover:shadow-xl hover:ring-slate-300">
                                    <div class="relative h-36 overflow-hidden bg-slate-800">
                                        @if ($url = $bundle->cover?->url('sm'))
                                            <img src="{{ $url }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-700 group-hover:scale-105">
                                        @endif
                                        <span class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-950/10 to-transparent"></span>
                                        <span @class(['absolute start-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-extrabold uppercase tracking-wide shadow-sm',
                                            'bg-[#C62E2E] text-white' => $isPass, 'bg-white text-slate-900' => !$isPass])>
                                            {{ $isPass ? __('most-popular-activities.category_pass') : __('most-popular-activities.category_package') }}
                                        </span>
                                        @if ($count === $best && $best > 1)
                                            <span class="absolute end-3 top-3 inline-flex items-center gap-1 rounded-full bg-amber-400 px-2.5 py-1 text-[11px] font-extrabold text-slate-900 shadow-sm">
                                                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"/></svg>
                                                {{ __('attraction-page.compare_best') }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-1 flex-col p-5">
                                        <h3 class="line-clamp-2 text-base font-extrabold leading-snug text-slate-900">{{ $bundle->name }}</h3>

                                        <div class="mt-4">
                                            <div class="flex items-center justify-between text-xs font-bold">
                                                <span class="text-slate-700">{{ __('attraction-page.compare_includes', ['count' => $count, 'total' => $total]) }}</span>
                                                <span class="tabular-nums text-emerald-600">{{ $count }}/{{ $total }}</span>
                                            </div>
                                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                                <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: {{ $total ? round($count / $total * 100) : 0 }}%"></div>
                                            </div>
                                        </div>

                                        <ul class="mt-4 mb-5 space-y-2">
                                            @foreach ($columns as $col)
                                                @php $has = $bundle->attractions->contains('id', $col->id); @endphp
                                                <li class="flex items-center gap-2.5 text-sm">
                                                    @if ($has)
                                                        <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-500 text-white"><svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                                                        <span class="font-semibold text-slate-800">{{ $col->name }}</span>
                                                    @else
                                                        <span class="grid size-5 shrink-0 place-items-center rounded-full bg-slate-100 text-slate-400"><svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M6 12h12"/></svg></span>
                                                        <span class="text-slate-400">{{ $col->name }}</span>
                                                    @endif
                                                    <span class="sr-only">{{ $has ? '✓' : '—' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>

                                        <span class="mt-auto inline-flex items-center justify-center gap-1.5 rounded-2xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition group-hover:bg-[#C62E2E]">
                                            {{ __('attraction-page.compare_view') }}
                                            <span aria-hidden="true" class="transition-transform group-hover:translate-x-1 rtl:rotate-180">→</span>
                                        </span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>
@endif
