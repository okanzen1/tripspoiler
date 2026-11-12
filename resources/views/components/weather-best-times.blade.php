{{--
    Günün en iyi saatleri (App\Support\Weather::bestTimes): fotoğraf, spor / yürüyüş, dışarıda kahve, yağmurun başlayacağı saat.
    dark: şehir sayfasındaki lacivert panel; açık: hava durumu sayfası (tek beyaz şerit, bölmeli).
--}}
@props(['city', 'forecast', 'dark' => true])

@php
    $times = \App\Support\Weather::bestTimes($city, $forecast);
    $look = [
        'photo' => ['from-amber-300 to-amber-500 text-slate-950', '<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>'],
        'sport' => ['from-[#FF6B6E] to-[#C62E2E] text-white', '<circle cx="12" cy="14" r="7"/><path d="M12 14v-3.5M10 2.5h4M18.5 7.5 20 6"/>'],
        'coffee' => ['from-amber-300 to-amber-500 text-slate-950', '<path d="M4 9h12v5a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5V9ZM16 10h1.5a2.5 2.5 0 0 1 0 5H16"/><path d="M8 3v2.5M12 3v2.5"/>'],
        'dinner' => ['from-[#FF6B6E] to-[#C62E2E] text-white', '<path d="M7 3v8a2 2 0 0 0 2 2v8M5 3v5M9 3v5M17 21V3c-2 1.5-3 4-3 7v3h3"/>'],
        'rain' => ['from-[#FF6B6E] to-[#C62E2E] text-white', '<path d="M7 15a4 4 0 0 1-.6-8A5.5 5.5 0 0 1 17 8a3.5 3.5 0 0 1 .5 7"/><path d="M9 18l-1 2.5M13 18l-1 2.5M17 18l-1 2.5"/>'],
    ];
    $mm = fn ($v) => rtrim(rtrim(number_format($v, 1, ',', ''), '0'), ',');
@endphp

@if ($times && !$dark)
    @php
        // kart zemini + ikon kutusu (sitenin renkleri: altın, kırmızı, lacivert)
        $tone = [
            'photo' => ['bg-gradient-to-br from-amber-100 via-amber-50 to-white', 'bg-amber-400 text-slate-950', 'text-amber-500'],
            'sport' => ['bg-gradient-to-br from-[#C62E2E]/[0.12] via-[#C62E2E]/[0.04] to-white', 'bg-[#C62E2E] text-white', 'text-[#C62E2E]'],
            'coffee' => ['bg-gradient-to-br from-orange-100 via-amber-50 to-white', 'bg-gradient-to-br from-amber-400 to-orange-500 text-white', 'text-orange-500'],
            'dinner' => ['bg-slate-950 text-white', 'bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] text-white', 'text-white'],
            'rain' => ['bg-gradient-to-br from-sky-100 via-sky-50 to-white', 'bg-sky-500 text-white', 'text-sky-500'],
        ];
    @endphp
    <div {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-4 sm:grid-cols-2 ' . ([3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][count($times)] ?? '')]) }}>
        @foreach ($times as $item)
            @php
                $icon = $look[$item['kind']][1];
                [$bg, $tile, $faint] = $tone[$item['kind']];
                $dim = $item['kind'] === 'dinner';
                $note = match ($item['kind']) {
                    'photo' => __('weather.best_photo_note', ['time' => $item['sunset']]),
                    'rain' => __('weather.best_rain_note', ['mm' => $mm($item['rain'])]),
                    default => $item['temp'] . '° · ' . __('weather.c_' . $item['condition']),
                };
            @endphp
            <div class="relative isolate overflow-hidden rounded-3xl p-5 ring-1 ring-slate-200/70 sm:p-6 {{ $bg }}">
                {{-- köşede büyük soluk ikon --}}
                <svg class="pointer-events-none absolute -bottom-5 -right-5 -z-10 size-28 opacity-[0.12] rtl:-left-5 rtl:right-auto {{ $faint }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                <p class="flex items-center gap-2.5">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl shadow-sm {{ $tile }}">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                    </span>
                    <span @class(['text-xs font-bold leading-snug', 'text-white/70' => $dim, 'text-slate-600' => !$dim])>{{ __('weather.best_' . $item['kind']) }}</span>
                </p>
                <p class="mt-5 flex items-baseline gap-2">
                    <span @class(['text-4xl font-extrabold tracking-tight tabular-nums', 'text-white' => $dim, 'text-slate-900' => !$dim])>{{ $item['time'] }}</span>
                    @if ($item['tomorrow'])
                        <span @class(['rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider', 'bg-white/10 text-amber-300' => $dim, 'bg-white/80 text-slate-500 ring-1 ring-slate-200' => !$dim])>{{ __('weather.tomorrow') }}</span>
                    @endif
                </p>
                <p @class(['mt-1 text-sm', 'text-white/60' => $dim, 'text-slate-500' => !$dim])>{{ $note }}</p>
            </div>
        @endforeach
    </div>
@elseif ($times)
    <div {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-3 sm:grid-cols-2 ' . [3 => 'xl:grid-cols-3', 4 => 'xl:grid-cols-4'][count($times)] ?? '']) }}>
        @foreach ($times as $item)
            @php
                [$tile, $icon] = $look[$item['kind']];
                $label = __('weather.best_' . $item['kind']);
                $note = match ($item['kind']) {
                    'photo' => __('weather.best_photo_note', ['time' => $item['sunset']]),
                    'rain' => __('weather.best_rain_note', ['mm' => $mm($item['rain'])]),
                    default => $item['temp'] . '° · ' . __('weather.c_' . $item['condition']),
                };
            @endphp
            <div class="flex items-center gap-4 rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/10">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-gradient-to-br {{ $tile }} shadow-lg">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icon !!}</svg>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-[10px] font-bold uppercase leading-snug tracking-[0.12em] text-white/50">{{ $label }}</span>
                    <span class="mt-0.5 block text-xs text-white/65">{{ $note }}</span>
                </span>
                <span class="shrink-0 text-right rtl:text-left">
                    @if ($item['tomorrow'])
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-300">{{ __('weather.tomorrow') }}</span>
                    @endif
                    <span class="block text-2xl font-extrabold tracking-tight text-white tabular-nums">{{ $item['time'] }}</span>
                </span>
            </div>
        @endforeach
    </div>
@endif
