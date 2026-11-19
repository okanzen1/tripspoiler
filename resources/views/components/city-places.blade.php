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
                {{-- Karşılaştırma tablosu: satır = pass / kombo (resim, tür, en çok yer rozeti), sütun = yer (küçük resmiyle).
                     En çok yeri kapsayan satır vurgulu. Telefonda ürün sütunu sabit kalır, yerler yana kayar. --}}
                @php
                    $total = $columns->count();
                    $covered = $bundles->mapWithKeys(fn ($b) => [$b->id => $b->attractions->whereIn('id', $columns->pluck('id'))->count()]);
                    $best = $covered->max();
                    $rows = $bundles->sortByDesc(fn ($b) => $covered[$b->id])->values();
                @endphp
                <div class="mt-14">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-[-0.02em] text-slate-900 sm:text-3xl">{{ __('attraction-page.compare_title', ['city' => $city->name]) }}</h2>
                            <p class="mt-2 text-slate-500">{{ __('attraction-page.compare_text') }}</p>
                        </div>
                        <p class="flex items-center gap-4 text-xs font-semibold text-slate-500">
                            <span class="inline-flex items-center gap-1.5"><span class="grid size-4 place-items-center rounded-full bg-emerald-500 text-white"><svg class="size-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>{{ __('attraction-page.compare_yes') }}</span>
                            <span class="inline-flex items-center gap-1.5"><span class="h-0.5 w-3 rounded-full bg-slate-300"></span>{{ __('attraction-page.compare_no') }}</span>
                        </p>
                    </div>

                    {{-- Telefon: tablo yerine kart; yana kaydırma yok. Her kartta kapsam çubuğu ve yerler tek tek (dahil / değil) --}}
                    <ul class="mt-6 space-y-4 md:hidden">
                        @foreach ($rows as $bundle)
                            @php
                                $count = $covered[$bundle->id];
                                $isPass = ($bundle->type instanceof \BackedEnum ? $bundle->type->value : $bundle->type) === 'pass';
                                $isBest = $count === $best && $best > 1;
                                $url = route('activities.show', $bundle->localSlug());
                            @endphp
                            <li @class(['relative overflow-hidden rounded-3xl bg-white p-4 shadow-[0_14px_40px_-22px_rgba(15,23,42,0.4)] ring-1',
                                'ring-amber-300' => $isBest, 'ring-slate-200' => !$isBest])>
                                @if ($isBest)<span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500" aria-hidden="true"></span>@endif
                                <a href="{{ $url }}" class="flex items-start gap-3.5">
                                    <span class="size-16 shrink-0 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                                        @if ($img = $bundle->cover?->url('sm'))<img src="{{ $img }}" alt="" loading="lazy" class="size-full object-cover">@endif
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span class="flex flex-wrap items-center gap-1.5">
                                            <span @class(['rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide', 'bg-[#C62E2E] text-white' => $isPass, 'bg-slate-900 text-white' => !$isPass])>
                                                {{ $isPass ? __('most-popular-activities.category_pass') : __('most-popular-activities.category_package') }}
                                            </span>
                                            @if ($isBest)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-extrabold text-slate-900">
                                                    <svg class="size-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"/></svg>
                                                    {{ __('attraction-page.compare_best') }}
                                                </span>
                                            @endif
                                        </span>
                                        <span class="mt-1.5 block text-[15px] font-extrabold leading-snug text-slate-900">{{ $bundle->name }}</span>
                                    </span>
                                </a>

                                {{-- kapsam: 2/6 + çubuk --}}
                                <div class="mt-4 flex items-center gap-3">
                                    <span class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: {{ $total ? round($count / $total * 100) : 0 }}%"></span></span>
                                    <span class="shrink-0 text-sm font-extrabold tabular-nums text-slate-900">{{ $count }}<span class="text-xs font-bold text-slate-400">/{{ $total }}</span></span>
                                </div>

                                {{-- yerler: dahil olanlar yeşil, olmayanlar soluk --}}
                                <ul class="mt-3 grid grid-cols-2 gap-2">
                                    @foreach ($columns as $col)
                                        @php $in = $bundle->attractions->contains('id', $col->id); @endphp
                                        <li @class(['flex min-w-0 items-center gap-2 rounded-xl px-2.5 py-2 text-[12px] font-semibold leading-tight',
                                            'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-100' => $in, 'bg-slate-50 text-slate-400' => !$in])>
                                            @if ($in)
                                                <span class="grid size-5 shrink-0 place-items-center rounded-full bg-emerald-500 text-white"><svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                                            @else
                                                <span class="grid size-5 shrink-0 place-items-center"><span class="h-0.5 w-3 rounded-full bg-slate-300"></span></span>
                                            @endif
                                            <span class="min-w-0 break-words">{{ $col->name }}</span>
                                            <span class="sr-only">{{ $in ? __('attraction-page.compare_yes') : __('attraction-page.compare_no') }}</span>
                                        </li>
                                    @endforeach
                                </ul>

                                <a href="{{ $url }}" @class(['mt-4 flex w-full items-center justify-center gap-1.5 rounded-2xl px-4 py-3 text-sm font-bold transition',
                                    'bg-[#C62E2E] text-white shadow-lg shadow-red-600/25 active:bg-[#A82424]' => $isBest,
                                    'bg-slate-900 text-white active:bg-[#C62E2E]' => !$isBest])>
                                    {{ __('attraction-page.compare_view') }} <span aria-hidden="true" class="rtl:rotate-180">→</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Bilgisayar / tablet: tablo --}}
                    <div class="relative mt-6 hidden md:block overflow-hidden rounded-[28px] bg-white shadow-[0_20px_60px_-25px_rgba(15,23,42,0.35)] ring-1 ring-slate-200">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[50rem] border-separate border-spacing-0 text-sm">
                                <thead>
                                    <tr class="bg-gradient-to-r from-slate-950 via-slate-900 to-slate-800 text-white">
                                        <th scope="col" class="sticky start-0 z-20 min-w-[19rem] w-[22rem] bg-slate-950 px-5 py-4 text-start align-bottom text-[11px] font-bold uppercase tracking-[0.18em] text-white/60">{{ __('attraction-page.compare_product') }}</th>
                                        @foreach ($columns as $col)
                                            <th scope="col" class="px-3 py-4 text-center align-bottom">
                                                <span class="mx-auto block size-11 overflow-hidden rounded-full bg-white/10 ring-2 ring-white/25">
                                                    @if ($col->image_path)<img src="{{ $col->imageUrl('sm') }}" alt="" loading="lazy" class="size-full object-cover">@endif
                                                </span>
                                                <span class="mt-2 block text-[12px] font-bold leading-tight">{{ $col->name }}</span>
                                            </th>
                                        @endforeach
                                        <th scope="col" class="px-5 py-4 text-end align-bottom text-[11px] font-bold uppercase tracking-[0.18em] text-white/60">{{ __('attraction-page.compare_score') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rows as $bundle)
                                        @php
                                            $count = $covered[$bundle->id];
                                            $isPass = ($bundle->type instanceof \BackedEnum ? $bundle->type->value : $bundle->type) === 'pass';
                                            $isBest = $count === $best && $best > 1;
                                            $url = route('activities.show', $bundle->localSlug());
                                        @endphp
                                        <tr @class(['group transition', 'bg-amber-50/70' => $isBest, 'hover:bg-slate-50' => !$isBest])>
                                            <th scope="row" @class(['sticky start-0 z-10 border-t border-slate-100 px-5 py-4 text-start font-normal', 'bg-amber-50' => $isBest, 'bg-white group-hover:bg-slate-50' => !$isBest])>
                                                @if ($isBest)<span class="absolute inset-y-0 start-0 w-1 bg-gradient-to-b from-amber-400 to-amber-500" aria-hidden="true"></span>@endif
                                                <a href="{{ $url }}" class="flex items-center gap-3.5">
                                                    <span class="size-14 shrink-0 overflow-hidden rounded-2xl bg-slate-100 ring-1 ring-slate-200">
                                                        @if ($img = $bundle->cover?->url('sm'))<img src="{{ $img }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-110">@endif
                                                    </span>
                                                    <span class="min-w-0">
                                                        <span class="flex flex-wrap items-center gap-1.5">
                                                            <span @class(['rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wide', 'bg-[#C62E2E] text-white' => $isPass, 'bg-slate-900 text-white' => !$isPass])>
                                                                {{ $isPass ? __('most-popular-activities.category_pass') : __('most-popular-activities.category_package') }}
                                                            </span>
                                                            @if ($isBest)
                                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-400 px-2 py-0.5 text-[10px] font-extrabold text-slate-900">
                                                                    <svg class="size-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.5l2.9 6.1 6.6.8-4.9 4.6 1.3 6.6L12 17.3l-5.9 3.3 1.3-6.6-4.9-4.6 6.6-.8z"/></svg>
                                                                    {{ __('attraction-page.compare_best') }}
                                                                </span>
                                                            @endif
                                                        </span>
                                                        <span class="mt-1.5 line-clamp-2 text-[14px] font-extrabold leading-snug text-slate-900 group-hover:text-[#C62E2E]">{{ $bundle->name }}</span>
                                                    </span>
                                                </a>
                                            </th>
                                            @foreach ($columns as $col)
                                                <td class="border-t border-slate-100 px-3 py-4 text-center">
                                                    @if ($bundle->attractions->contains('id', $col->id))
                                                        <span class="inline-grid size-8 place-items-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-md shadow-emerald-500/30 ring-4 ring-emerald-50" aria-label="✓"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                                                    @else
                                                        <span class="inline-block h-0.5 w-4 rounded-full bg-slate-200" aria-label="—"></span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="border-t border-slate-100 px-5 py-4">
                                                <div class="flex items-center justify-end gap-4">
                                                    <span class="text-end">
                                                        <span class="block text-lg font-extrabold tabular-nums leading-none text-slate-900">{{ $count }}<span class="text-sm font-bold text-slate-400">/{{ $total }}</span></span>
                                                        <span class="mt-1.5 block h-1.5 w-16 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: {{ $total ? round($count / $total * 100) : 0 }}%"></span></span>
                                                    </span>
                                                    <a href="{{ $url }}" @class(['inline-flex shrink-0 items-center gap-1.5 whitespace-nowrap rounded-full px-4 py-2.5 text-[13px] font-bold transition',
                                                        'bg-[#C62E2E] text-white shadow-lg shadow-red-600/25 hover:bg-[#A82424]' => $isBest,
                                                        'bg-slate-900 text-white hover:bg-[#C62E2E]' => !$isBest])>
                                                        {{ __('attraction-page.compare_view') }}
                                                        <span aria-hidden="true" class="transition-transform group-hover:translate-x-0.5 rtl:rotate-180">→</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endif
