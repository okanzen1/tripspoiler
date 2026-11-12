{{--
    "Bugün ne giysem?" önerileri (App\Support\Weather::tips), kırmızı ikonlu satırlar.
    dark: şehir sayfasındaki lacivert hava paneli; açık: hava durumu sayfası (beyaz kart içinde liste).
--}}
@props(['tips' => [], 'grid' => false, 'dark' => true])

@php
    $icons = [
        'tip_snow' => '<path d="M12 2v20M4.9 4.9l14.2 14.2M2 12h20M4.9 19.1 19.1 4.9"/><path d="m9 4 3 3 3-3M9 20l3-3 3 3"/>',
        'tip_umbrella' => '<path d="M12 3a9 9 0 0 1 9 9H3a9 9 0 0 1 9-9Z"/><path d="M12 12v7a2 2 0 0 0 4 0"/>',
        'tip_dry' => '<circle cx="6.5" cy="14" r="3.5"/><circle cx="17.5" cy="14" r="3.5"/><path d="M10 14h4M3 13l2-6h2M21 13l-2-6h-2"/>',
        'tip_windy' => '<path d="M3 8h10a3 3 0 1 0-3-3M3 12h15a3 3 0 1 1-3 3M3 16h7"/>',
        'tip_gale' => '<path d="M3 8h10a3 3 0 1 0-3-3M3 12h15a3 3 0 1 1-3 3M3 16h7"/><path d="M19 4v3M21 5.5h-3"/>',
        'tip_hot' => '<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
        'tip_cold' => '<path d="M14 14.8V4.5a2 2 0 0 0-4 0v10.3a4 4 0 1 0 4 0Z"/><path d="M12 10v6"/>',
        'tip_jacket' => '<path d="M8 3 4 6v15h5v-6M16 3l4 3v15h-5v-6M8 3l4 4 4-4M12 7v14"/>',
        'tip_mild' => '<path d="M8 3 3 6l2 4 3-1v12h8V9l3 1 2-4-5-3a4 4 0 0 1-8 0Z"/>',
    ];
@endphp

<ul {{ $attributes->class([$grid ? 'grid gap-3 md:grid-cols-2 xl:grid-cols-3' : ($dark ? 'space-y-3' : 'divide-y divide-[#C62E2E]/10')]) }}>
    @foreach ($tips as $tip)
        @php
            // "Yağmur bekleniyor: şemsiye…" → kalın başlık + açıklama
            $parts = preg_split('/\s*[:：]\s*/u', __('weather.' . $tip), 2);
        @endphp
        <li @class(['flex items-center gap-4', 'rounded-2xl bg-white/[0.06] p-4 ring-1 ring-white/10 sm:p-5' => $dark, 'py-4 first:pt-2 last:pb-0' => !$dark])>
            <span @class(['grid size-12 shrink-0 place-items-center rounded-xl', 'bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] text-white shadow-[0_12px_26px_-12px_rgba(198,46,46,0.8)]' => $dark, 'bg-white text-[#C62E2E] shadow-sm ring-1 ring-[#C62E2E]/15' => !$dark])>
                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$tip] ?? $icons['tip_mild'] !!}</svg>
            </span>
            <span class="min-w-0">
                @if (count($parts) === 2)
                    <span @class(['block text-base font-extrabold leading-snug', 'text-white sm:text-lg' => $dark, 'text-slate-900' => !$dark])>{{ $parts[0] }}</span>
                    <span @class(['mt-0.5 block text-sm leading-relaxed', 'text-white/65 sm:text-[15px]' => $dark, 'text-slate-500' => !$dark])>{{ \Illuminate\Support\Str::ucfirst($parts[1]) }}</span>
                @else
                    <span @class(['block text-[15px] font-semibold leading-relaxed', 'text-white' => $dark, 'text-slate-800' => !$dark])>{{ $parts[0] }}</span>
                @endif
            </span>
        </li>
    @endforeach
</ul>
