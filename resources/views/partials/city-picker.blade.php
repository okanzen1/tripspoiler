{{--
    Koyu banner'larda şehir seçici (cam kutu + açılır liste). Şehir seçimi sayfalar arasında ortaktır (session).
    $cities, $current (City), $url (fn (City) => adres), $meta (fn (City) => alt satır), $label (kutudaki küçük başlık).
    Banner'da x-data="{ picker: false }" varsa menü açıkken banner üste çıkar (yapışkan çubukların arkasında kalmasın).
--}}
@php
    $flagOf = fn ($c) => $c?->country?->code ? implode('', array_map(fn ($ch) => mb_chr(0x1F1E6 + ord($ch) - 65), str_split(strtoupper($c->country->code)))) : '';
@endphp

<div x-data="{ open: false }" x-effect="if ('picker' in $data) picker = open" @click.outside="open = false" @keydown.escape.window="open = false" class="relative w-full">
    <button type="button" @click="open = !open" :aria-expanded="open"
        :class="open ? 'bg-white/[0.16] ring-white/40' : 'ring-white/15 hover:ring-white/30'"
        class="flex w-full items-center gap-3 rounded-2xl bg-white/[0.08] px-4 py-3 text-left text-white ring-1 backdrop-blur-md transition">
        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 text-xl leading-none ring-1 ring-white/15" aria-hidden="true">{{ $flagOf($current) ?: '📍' }}</span>
        <span class="min-w-0 flex-1">
            <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-white/50">{{ $label }}</span>
            <span class="block truncate text-sm font-bold">{{ $current->name }}</span>
        </span>
        <svg class="size-4 shrink-0 text-white/60 transition duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
    </button>
    <div x-show="open" x-cloak
        x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 -translate-y-2 scale-[0.98]" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-1"
        class="absolute left-0 top-full z-50 mt-2 w-full min-w-[18rem] origin-top overflow-hidden rounded-3xl bg-white text-slate-900 shadow-[0_30px_70px_-20px_rgba(2,6,23,0.6)] ring-1 ring-slate-200 rtl:left-auto rtl:right-0">
        <p class="border-b border-slate-100 px-5 py-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $label }} · {{ __('cities-page.cities_count', ['count' => $cities->count()]) }}</p>
        <div class="max-h-80 overflow-y-auto p-2">
            @foreach ($cities as $c)
                <a href="{{ $url($c) }}"
                    @class(['group flex items-center gap-3.5 rounded-2xl p-2.5 transition', 'bg-slate-50 ring-1 ring-slate-200' => $c->is($current), 'hover:bg-slate-50' => !$c->is($current)])>
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 text-2xl leading-none">{{ $flagOf($c) ?: '📍' }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[15px] font-bold">{{ $c->name }}</span>
                        <span class="block truncate text-xs text-slate-500">{{ $meta($c) }}</span>
                    </span>
                    @if ($c->is($current))
                        <span class="grid size-7 shrink-0 place-items-center rounded-full bg-[#C62E2E] text-white">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                        </span>
                    @else
                        <svg class="size-4 shrink-0 text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-slate-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    @endif
                </a>
            @endforeach
        </div>
    </div>
</div>
