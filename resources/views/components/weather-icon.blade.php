{{-- Hava durumu ikonu (App\Support\Weather::condition): güneş sarı, bulut gri, yağmur mavi, şimşek altın, kar açık mavi --}}
@props(['condition' => 'clear'])

@php
    $sun = '<circle cx="12" cy="12" r="4.2" fill="#FBBF24" stroke="#F59E0B"/><path stroke="#F59E0B" d="M12 2.5v2M12 19.5v2M4.6 4.6 6 6M18 18l1.4 1.4M2.5 12h2M19.5 12h2M4.6 19.4 6 18M18 6l1.4-1.4"/>';
    $smallSun = '<circle cx="8.5" cy="8.5" r="3.4" fill="#FBBF24" stroke="#F59E0B"/><path stroke="#F59E0B" d="M8.5 2.6v1.3M2.6 8.5h1.3M4.3 4.3l.9.9M12.7 4.3l-.9.9M4.3 12.7l.9-.9"/>';
    $cloud = '<path d="M7.5 19h9.8a3.7 3.7 0 0 0 .5-7.37A5.5 5.5 0 0 0 7.3 10.2 4.4 4.4 0 0 0 7.5 19z" fill="#E2E8F0" stroke="#94A3B8"/>';
    $cloudHigh = '<path d="M7.5 15.5h9.8a3.7 3.7 0 0 0 .5-7.37A5.5 5.5 0 0 0 7.3 6.7a4.4 4.4 0 0 0 .2 8.8z" fill="#E2E8F0" stroke="#94A3B8"/>';
    $drops = fn ($n) => collect([[8.5, 18], [12, 19.5], [15.5, 18], [10.2, 21.5], [13.8, 21.5]])->take($n)
        ->map(fn ($p) => '<path stroke="#0EA5E9" stroke-width="1.8" d="M' . $p[0] . ' ' . $p[1] . 'l-.9 2"/>')->join('');
    $flakes = '<path stroke="#7DD3FC" d="M9 18.5v3M7.7 20h2.6M15 18.5v3M13.7 20h2.6"/>';
    $icons = [
        'clear' => $sun,
        'fair' => $sun . '<path d="M11 20h7.2a2.6 2.6 0 0 0 .3-5.2 3.9 3.9 0 0 0-7.4.9A2.2 2.2 0 0 0 11 20z" fill="#F1F5F9" stroke="#94A3B8"/>',
        'partly' => $smallSun . '<path d="M8.5 20h9.6a3.4 3.4 0 0 0 .4-6.8 5 5 0 0 0-9.7-1.1A4 4 0 0 0 8.5 20z" fill="#E2E8F0" stroke="#94A3B8"/>',
        'cloudy' => '<path d="M5.5 15.5a3.4 3.4 0 0 1 1.9-6.3 4.7 4.7 0 0 1 8.9-1.4" fill="none" stroke="#CBD5E1"/>' . $cloud,
        'fog' => $cloudHigh . '<path stroke="#94A3B8" d="M4.5 18.5h15M6.5 21h11"/>',
        'light_rain' => $cloudHigh . $drops(2),
        'rain' => $cloudHigh . $drops(3),
        'heavy_rain' => $cloudHigh . $drops(5),
        'thunder' => $cloudHigh . '<path d="m12.8 15-2.6 4h3l-1.8 3.5" fill="none" stroke="#F59E0B" stroke-width="1.9"/>' . $drops(1),
        'sleet' => $cloudHigh . '<path stroke="#0EA5E9" stroke-width="1.8" d="M9 18l-.9 2"/>' . '<path stroke="#7DD3FC" d="M15 18.5v3M13.7 20h2.6"/>',
        'snow' => $cloudHigh . $flakes,
    ];
@endphp

<svg {{ $attributes->merge(['class' => 'shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$condition] ?? $icons['partly'] !!}</svg>
