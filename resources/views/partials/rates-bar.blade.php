{{--
    Duyuru şeridinin altındaki döviz kuru şeridi: küresel çiftler (EUR/USD, GBP/USD…) ve sitedeki ülkelerin paraları.
    Duyuru şeridi gibi kayar, üstüne gelince durur. Kapatılırsa bu ziyaret boyunca gizli kalır (sessionStorage).
    Kurlar App\Support\ExchangeRates'ten (ücretsiz, günde bir güncellenir). Panelden kapatılabilir (Bölümler › Döviz kurları).
--}}
@if ($rates && $rates['items'])
    @php
        $repeat = (int) ceil(10 / count($rates['items']));
        $items = collect(array_fill(0, $repeat, $rates['items']))->flatten(1);
        $duration = max(30, $items->count() * 4);
        $key = 'ts-rates-hidden';
    @endphp

    <script>
        (() => {
            let hidden = false;
            try { hidden = !!sessionStorage.getItem(@json($key)); } catch (e) {}
            document.documentElement.style.setProperty('--rates-h', hidden ? '0px' : '32px');
            if (hidden) document.documentElement.classList.add('rates-dismissed');
        })();
    </script>
    <div x-data="{
            open: true,
            init() { try { this.open = !sessionStorage.getItem(@js($key)) } catch (e) {} },
            dismiss() {
                this.open = false;
                document.documentElement.style.setProperty('--rates-h', '0px');
                try { sessionStorage.setItem(@js($key), 1) } catch (e) {}
            },
        }" x-show="open"
        class="rates-bar relative border-t border-white/10 bg-slate-950 text-white"
        role="region" aria-label="{{ __('rates-bar.label') }}">

        <div class="flex h-8 items-stretch">
            {{-- solda sabit: ne olduğunu anlatan kısa yazı --}}
            <a href="{{ route('rates') }}" class="relative z-10 hidden shrink-0 items-center gap-2 bg-slate-950 pl-4 pr-4 text-[12.5px] font-medium text-white/70 transition hover:text-white md:flex">
                <span class="grid size-5 place-items-center rounded-full bg-[#E04848]/15 text-[#FF8A8C]">
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 7h11l-3-3M17 17H6l3 3"/></svg>
                </span>
                {{ __('rates-bar.intro') }}
                <span class="ml-2 h-4 w-px bg-white/15" aria-hidden="true"></span>
            </a>

            {{-- kayan kurlar --}}
            <div class="min-w-0 flex-1 overflow-hidden [mask-image:linear-gradient(90deg,transparent,#000_24px,#000_calc(100%-24px),transparent)]" dir="ltr">
                <div class="announcement-track flex h-full w-max" style="--announcement-duration: {{ $duration }}s">
                    @foreach ([0, 1] as $copy)
                        <ul class="flex shrink-0 items-center" @if ($copy) aria-hidden="true" @endif>
                            @foreach ($items as $item)
                                <li class="flex items-center gap-2.5 whitespace-nowrap px-5 text-[12.5px] tabular-nums">
                                    @if ($item['country'])
                                        <span class="text-sm leading-none" aria-hidden="true">{{ $item['flag'] }}</span>
                                        <span @class(['font-semibold', 'text-white' => $item['current'], 'text-white/75' => !$item['current']])>{{ $item['country'] }}</span>
                                    @endif
                                    @foreach ($item['parts'] as $part)
                                        @unless ($loop->first)<span class="text-white/25" aria-hidden="true">·</span>@endunless
                                        <span class="font-medium text-white/90">{{ $part }}</span>
                                    @endforeach
                                    @if ($item['change'] !== null && $item['change'] != 0)
                                        <span @class(['text-[11px] font-semibold', 'text-emerald-400' => $item['change'] > 0, 'text-[#FF8A8C]' => $item['change'] < 0])>
                                            {{ $item['change'] > 0 ? '▲' : '▼' }} {{ number_format(abs($item['change']), 2) }}%
                                        </span>
                                    @endif
                                    <span class="ml-2.5 text-[#E04848]/70" aria-hidden="true">✦</span>
                                </li>
                            @endforeach
                        </ul>
                    @endforeach
                </div>
            </div>

            {{-- sağda: tüm kurlar + kapat --}}
            <a href="{{ route('rates') }}" class="relative z-10 flex shrink-0 items-center gap-1 bg-slate-950 pl-3 pr-2 text-[12px] font-bold text-[#FF6B6E] transition hover:text-white">
                <span class="hidden md:inline">{{ __('rates-bar.all') }}</span>
                <span class="md:hidden">{{ __('rates-bar.label') }}</span>
                <span aria-hidden="true" class="rtl:rotate-180">→</span>
            </a>
            {{-- ExchangeRate-API kullanım şartı: kurların gösterildiği her sayfada kaynak linki --}}
            <a href="https://www.exchangerate-api.com" target="_blank" rel="noopener nofollow" title="Rates By Exchange Rate API"
                class="relative z-10 flex shrink-0 items-center bg-slate-950 pl-2 pr-1 text-[11px] text-white/35 transition hover:text-white/70">
                {{ __('rates-bar.source') }}
            </a>
            <button type="button" @click="dismiss()" aria-label="{{ __('announcement-bar.close') }}"
                class="relative z-10 flex w-10 shrink-0 items-center justify-center bg-slate-950 text-white/60 transition hover:text-white">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </div>
@endif
