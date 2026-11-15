{{--
    Dünya hava durumu (döviz kuru şeridinin sağında): 3 kutu (telefonda 1), her biri birkaç saniyede bir silindir gibi dönüp
    sonraki şehre geçer; kutular art arda, küçük gecikmeyle döner. App\Http\Controllers\WeatherTickerController döndürür.
--}}
@php $n = $items->count(); @endphp
<a href="{{ $url }}" class="flex h-8 items-center gap-1 pl-2 text-white/90 transition hover:text-white"
    x-data="{ i: 0, timer: null, init() { this.timer = setInterval(() => { if (!document.hidden) this.i++ }, 3600) }, destroy() { clearInterval(this.timer) } }"
    aria-label="{{ __('footer.weather') }}">
    @foreach ([0, 1, 2] as $slot)
        @php $offset = intdiv($n, 3) * $slot; @endphp
        <span @class(['relative h-8 w-[7.25rem] shrink-0 [perspective:240px]', 'hidden md:block' => $slot > 0, 'border-l border-white/10' => $slot > 0])>
            @foreach ($items as $k => $item)
                <span x-show="(i + {{ $offset }}) % {{ $n }} === {{ $k }}" @if ($k !== $offset % $n) style="display: none" @endif
                    x-transition:enter="transition duration-500 ease-out" x-transition:enter-start="opacity-0 [transform:rotateX(-90deg)_translateY(55%)]" x-transition:enter-end="opacity-100 [transform:rotateX(0deg)_translateY(0)]"
                    x-transition:leave="transition duration-500 ease-in" x-transition:leave-start="opacity-100 [transform:rotateX(0deg)_translateY(0)]" x-transition:leave-end="opacity-0 [transform:rotateX(90deg)_translateY(-55%)]"
                    class="absolute inset-0 flex items-center justify-center gap-1.5 whitespace-nowrap px-1.5 text-[12.5px] [backface-visibility:hidden]"
                    style="transition-delay: {{ $slot * 180 }}ms">
                    <span class="text-sm leading-none">{{ $item['flag'] }}</span>
                    <span class="max-w-[4.2rem] truncate font-medium text-white/75">{{ $item['name'] }}</span>
                    <x-weather-icon :condition="$item['condition']" :night="$item['night']" class="size-[18px]" />
                    <span class="font-bold tabular-nums">{{ $item['temp'] }}°</span>
                </span>
            @endforeach
        </span>
    @endforeach
</a>
