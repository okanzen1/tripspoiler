@props(['cities', 'current' => null, 'url', 'loadingEvent' => null])
{{-- Şehir seçici (aktiviteler, blog): büyük, belirgin düğme; tıklayınca aranabilir liste (ülkeye göre gruplu, bayraklı).
     Telefonda alttan açılan tam ekran pencere. 50+ şehirde de kullanışlı. Klavye: ↑ ↓ Enter, Esc kapatır. --}}
@php
    // bayrak resmi (public/flags, flag-icons MIT): emoji bayraklar Windows'ta görünmüyor
    $flagUrl = fn ($c) => $c?->country?->code && is_file(public_path('flags/' . strtolower($c->country->code) . '.svg')) ? asset('flags/' . strtolower($c->country->code) . '.svg') : null;
    $items = $cities->map(fn ($c) => [
        'name' => $c->name,
        'country' => (string) $c->country?->name,
        'flag' => $flagUrl($c),
        'url' => $url($c),
        'current' => $current && $c->is($current),
        'search' => \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($c->name . ' ' . $c->country?->name)),
    ])->sortBy([['country', 'asc'], ['name', 'asc']])->values();
    $many = $cities->count() > 1;
@endphp
<div x-data="{
        open: false, q: '', i: 0, items: @js($items),
        get list() { const t = this.q.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/ı/g, 'i'); return t ? this.items.filter(c => c.search.includes(t)) : this.items },
        show() { this.open = true; this.q = ''; this.i = 0; this.$nextTick(() => this.$refs.q?.focus()); document.documentElement.classList.add('max-sm:overflow-hidden') },
        hide() { this.open = false; document.documentElement.classList.remove('max-sm:overflow-hidden') },
        go(c) { if (!c) return; if (c.current) return this.hide(); @if ($loadingEvent) $dispatch('{{ $loadingEvent }}'); @endif window.location.href = c.url },
    }" @keydown.escape.window="hide()" {{ $attributes->merge(['class' => 'relative w-full max-w-xl']) }}>
    <button type="button" @if ($many) @click="open ? hide() : show()" :aria-expanded="open" @endif
        @class(['group flex w-full items-center gap-4 rounded-[24px] bg-white/10 p-2.5 text-start ring-1 ring-white/25 backdrop-blur-md transition', 'hover:bg-white/15 hover:ring-white/45' => $many, 'cursor-default' => !$many])>
        @if ($currentFlag = $flagUrl($current))
            <span class="relative size-14 shrink-0 overflow-hidden rounded-2xl shadow-[0_12px_28px_-10px_rgba(0,0,0,0.8)] ring-2 ring-white/80">
                <img src="{{ $currentFlag }}" alt="{{ $current->country->name }}" class="size-full object-cover">
                <span class="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/25 to-transparent"></span>
            </span>
        @else
            <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-[#FF6B6E] to-[#C62E2E] text-white shadow-[0_12px_28px_-10px_rgba(198,46,46,0.9)]">
                <svg class="size-7" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.25a7.5 7.5 0 0 0-7.5 7.5c0 5.6 6.4 11.3 6.7 11.55a1.2 1.2 0 0 0 1.6 0c.3-.25 6.7-5.95 6.7-11.55A7.5 7.5 0 0 0 12 2.25Z" opacity=".35"/><circle cx="12" cy="9.75" r="3.25"/></svg>
            </span>
        @endif
        <span class="min-w-0 flex-1">
            <span class="block text-[11px] font-bold uppercase tracking-[0.16em] text-white/60">{{ __('activities-page.city') }}</span>
            <span class="mt-0.5 flex items-center gap-2 truncate text-xl font-extrabold text-white">
                {{ $current?->name }}@if ($current?->country)<span class="truncate text-base font-semibold text-white/60">· {{ $current->country->name }}</span>@endif
            </span>
        </span>
        @if ($many)
            <span class="relative inline-flex shrink-0 items-center gap-2 rounded-2xl bg-[#C62E2E] px-4 py-3 text-sm font-bold text-white shadow-[0_10px_24px_-10px_rgba(198,46,46,0.9)] transition group-hover:bg-[#A82424]">
                <span class="absolute -right-1 -top-1 flex size-3 rtl:-left-1 rtl:right-auto" aria-hidden="true">
                    <span class="absolute inline-flex size-full animate-ping rounded-full bg-[#FF6B6E] opacity-75 motion-reduce:animate-none"></span>
                    <span class="relative inline-flex size-3 rounded-full bg-[#FF6B6E] ring-2 ring-white"></span>
                </span>
                {{ __('activities-page.switch_city') }}
                <svg class="size-4 transition duration-300" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </span>
        @endif
    </button>

    @if ($many)
        {{-- telefonda arka plan karartması --}}
        <div x-show="open" x-cloak x-transition.opacity @click="hide()" class="fixed inset-0 z-[60] bg-slate-950/60 backdrop-blur-sm sm:hidden"></div>
        <div x-show="open" x-cloak @click.outside="hide()"
            x-transition:enter="transition duration-200 ease-out" x-transition:enter-start="opacity-0 translate-y-4 sm:-translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition duration-150 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4 sm:-translate-y-1"
            class="fixed inset-x-0 bottom-0 z-[70] flex max-h-[85vh] flex-col overflow-hidden rounded-t-[28px] bg-white text-slate-900 shadow-[0_-20px_60px_-20px_rgba(2,6,23,0.6)] sm:absolute sm:inset-x-0 sm:bottom-auto sm:top-full sm:z-50 sm:mt-2 sm:max-h-[26rem] sm:rounded-3xl sm:shadow-[0_30px_70px_-20px_rgba(2,6,23,0.6)] sm:ring-1 sm:ring-slate-200">
            <div class="mx-auto mt-2.5 h-1.5 w-12 rounded-full bg-slate-200 sm:hidden"></div>
            <div class="flex items-center justify-between px-5 pb-2 pt-3 sm:hidden">
                <p class="text-base font-extrabold">{{ __('activities-page.choose_city') }}</p>
                <button type="button" @click="hide()" class="grid size-9 place-items-center rounded-full bg-slate-100 text-slate-600" aria-label="✕">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
            <label class="m-3 mb-2 flex items-center gap-3 rounded-2xl bg-slate-100 px-4 py-3 ring-1 ring-transparent focus-within:bg-white focus-within:ring-[#C62E2E]/40">
                <svg class="size-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="10.5" cy="10.5" r="6"/><path d="m19.5 19.5-4.2-4.2"/></svg>
                <input x-ref="q" x-model="q" @input="i = 0" @keydown.down.prevent="i = Math.min(i + 1, list.length - 1)" @keydown.up.prevent="i = Math.max(i - 1, 0)" @keydown.enter.prevent="go(list[i])"
                    type="search" placeholder="{{ __('activities-page.search_city') }}" class="min-w-0 flex-1 bg-transparent text-[15px] font-medium outline-none placeholder:font-normal placeholder:text-slate-400 [&::-webkit-search-cancel-button]:hidden">
            </label>
            <ul class="flex-1 overflow-y-auto px-2 pb-3" role="listbox">
                <template x-for="(c, n) in list" :key="c.url">
                    <li>
                        <template x-if="n === 0 || list[n - 1].country !== c.country">
                            <p class="px-3 pb-1 pt-3 text-[11px] font-bold uppercase tracking-[0.16em] text-slate-400" x-text="c.country"></p>
                        </template>
                        <a :href="c.url" @click.prevent="go(c)" @mouseenter="i = n" role="option" :aria-selected="c.current"
                            :class="[c.current ? 'bg-[#FFF1F1] ring-1 ring-[#C62E2E]/25' : (i === n ? 'bg-slate-100' : '')]"
                            class="flex items-center gap-3.5 rounded-2xl px-3 py-2.5 transition">
                            <span class="grid h-8 w-11 shrink-0 place-items-center overflow-hidden rounded-lg bg-slate-100 shadow-sm ring-1 ring-slate-200">
                                <img x-show="c.flag" :src="c.flag" alt="" class="size-full object-cover">
                                <svg x-show="!c.flag" class="size-4 text-slate-400" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <span class="min-w-0 flex-1 truncate text-[15px] font-bold" x-text="c.name"></span>
                            <span x-show="c.current" class="inline-flex items-center gap-1 rounded-full bg-[#C62E2E] px-2.5 py-1 text-[11px] font-bold text-white">
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                {{ __('activities-page.city_selected') }}
                            </span>
                            <svg x-show="!c.current" class="size-4 shrink-0 text-slate-300 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                    </li>
                </template>
                <li x-show="!list.length" class="px-4 py-8 text-center text-sm text-slate-500">{{ __('activities-page.no_city') }}</li>
            </ul>
        </div>
    @endif
</div>
