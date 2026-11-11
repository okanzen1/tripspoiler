@extends('layouts.app')

@section('title', __('rates-page.meta_title'))
@section('meta_description', __('rates-page.meta_description'))

{{--
    Döviz kurları: koyu üst bölüm (not, başlık, ana kurlar, çevirici) ve altında kur tablosu (sekmeler, arama, baz para).
    Kurlar App\Support\ExchangeRates::page() (ücretsiz ExchangeRate-API, günde bir güncellenir; kaynak linki şart).
    Yazılar panelden (Sayfalar › Döviz kurları › Hero alanı ve Sayfa içeriği); boşsa dil dosyası.
--}}
@php
    $locale = app()->getLocale();
    $hero = \App\Support\PageHero::texts('rates', [
        'eyebrow' => __('rates-page.eyebrow'), 'title' => __('rates-page.title'), 'description' => __('rates-page.desc'),
    ]);
    $note = \App\Models\SectionText::texts('rates_note', $locale, [
        'title' => __('rates-page.note_title'), 'description' => __('rates-page.note_text'),
    ]);
    $data = \App\Support\ExchangeRates::page();
    $updated = $data ? \Illuminate\Support\Carbon::createFromTimestamp($data['updated'])->locale($locale)->isoFormat('D MMM YYYY, HH:mm') . ' UTC' : null;
    // Çeviricide ilk hedef: sitedeki bir ülkenin parası (ziyaretçininkinden farklı), yoksa EUR / USD
    $defaultTo = $data ? (collect($data['destinations'])->first(fn ($c) => $c !== $data['own']) ?? ($data['own'] === 'EUR' ? 'USD' : 'EUR')) : null;
    // Başlığın altındaki ana kurlar
    $majors = $data ? collect([['EUR', 'USD'], ['GBP', 'USD'], ['USD', 'JPY'], ...collect($data['destinations'])->reject(fn ($c) => in_array($c, ['USD', 'EUR'], true))->map(fn ($c) => ['USD', $c])->take(1)->all()])
        ->filter(fn ($p) => isset($data['rates'][$p[0]], $data['rates'][$p[1]]))->take(4)->values() : collect();
    // Tablonun baz parası: güçlü/yaygın bir paraysa ziyaretçininki, değilse USD (1 TRY = 0,0178 gibi okunmaz sayılar olmasın)
    $tableBase = $data && in_array($data['own'], ['USD', 'EUR', 'GBP', 'CHF'], true) ? $data['own'] : 'USD';
    $tabs = $data ? array_filter([
        // Popüler: önce sitedeki ülkelerin paraları (etiketli), sonra yaygın paralar
        'popular' => [__('rates-page.popular'), array_values(array_unique([...$data['destinations'], ...array_filter(\App\Support\ExchangeRates::POPULAR, fn ($c) => isset($data['rates'][$c]))]))],
        'all' => [__('rates-page.all'), array_keys($data['names'])],
    ], fn ($t) => count($t[1]) > 0) : [];
@endphp

@section('content')
    @if ($data)
    <div x-data="{
            rates: @js($data['rates']),
            prev: @js($data['previous']),
            names: @js($data['names']),
            locale: @js($locale),
            amount: '100',
            from: @js($data['own']),
            to: @js($defaultTo),
            base: @js($tableBase),
            tab: @js(array_key_first($tabs)),
            tabs: @js(array_map(fn ($t) => $t[1], $tabs)),
            dest: @js($data['destinations']),
            q: '',
            init() {
                // seçili sekmede gösterilecek para kalmadıysa (ör. tek hedef baz parayla aynı) sonrakine geç
                this.$watch('base', () => this.fixTab());
                this.fixTab();
            },
            fixTab() {
                if ((this.tabs[this.tab] || []).some((c) => c !== this.base)) return;
                this.tab = Object.keys(this.tabs).find((k) => this.tabs[k].some((c) => c !== this.base)) || this.tab;
            },
            num(v, digits) {
                return new Intl.NumberFormat(this.locale, { minimumFractionDigits: digits, maximumFractionDigits: digits }).format(v);
            },
            digits(v) { const a = Math.abs(v); return a >= 1000 ? 2 : (a >= 1 ? 4 : 6) },
            money(v, cur) {
                const d = Math.abs(v) >= 1 ? 2 : 4;
                try { return new Intl.NumberFormat(this.locale, { style: 'currency', currency: cur, currencyDisplay: 'narrowSymbol', minimumFractionDigits: d, maximumFractionDigits: d }).format(v) }
                catch (e) { return this.num(v, d) + ' ' + cur }
            },
            get value() { const a = parseFloat(String(this.amount).replace(/\s/g, '').replace(',', '.')); return isFinite(a) ? a : null },
            get result() { return this.value === null ? null : this.value * this.rates[this.to] / this.rates[this.from] },
            pair(a, b) { return this.rates[b] / this.rates[a] },
            change(a, b) {
                if (!this.prev || !this.prev[a] || !this.prev[b]) return null;
                const now = this.rates[b] / this.rates[a], old = this.prev[b] / this.prev[a];
                return old ? (now - old) / old * 100 : null;
            },
            swap() { [this.from, this.to] = [this.to, this.from] },
            pick(code) { this.from = this.base; this.to = code; document.getElementById('converter').scrollIntoView({ behavior: 'smooth', block: 'center' }) },
            get rows() {
                const q = this.q.trim().toLowerCase();
                const list = q ? Object.keys(this.names).filter((c) => c.toLowerCase().includes(q) || this.names[c][0].toLowerCase().includes(q)) : (this.tabs[this.tab] || []);
                return list.filter((c) => c !== this.base);
            },
        }">

        {{-- ÜST: koyu zemin --}}
        <section class="relative isolate overflow-hidden bg-slate-950 text-white">
            <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
                <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(rgba(255,255,255,.6)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,.6)_1px,transparent_1px)] [background-size:48px_48px] [mask-image:radial-gradient(ellipse_at_top,#000_20%,transparent_70%)]"></div>
                <div class="absolute -right-32 -top-40 size-[40rem] rounded-full bg-[#C62E2E]/25 blur-[140px]"></div>
                <div class="absolute -bottom-40 -left-32 size-[32rem] rounded-full bg-sky-500/10 blur-[140px]"></div>
            </div>

            <div class="mx-auto max-w-7xl px-4 pb-14 pt-6 md:pb-20 md:pt-8">
                <x-breadcrumbs dark :items="[
                    ['label' => __('activity-show.home'), 'url' => route('home')],
                    ['label' => __('footer.rates'), 'url' => route('rates')],
                ]" />

                {{-- NOT: kurlar anlık değil (en üstte) --}}
                <div class="mt-6 flex items-start gap-3 rounded-2xl bg-amber-400/10 px-4 py-3 ring-1 ring-amber-300/25">
                    <svg class="mt-0.5 size-5 shrink-0 text-amber-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                    <p class="text-sm leading-relaxed text-amber-50/85">
                        <strong class="font-semibold text-amber-200">{{ $note['title'] }}.</strong>
                        {{ $note['description'] }}
                    </p>
                </div>

                <div class="mt-10 grid items-center gap-10 lg:mt-14 lg:grid-cols-12 lg:gap-14">
                    <div class="min-w-0 lg:col-span-6">
                        <p class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-[#FF8A8C]">
                            <span class="h-px w-8 bg-[#FF8A8C]"></span>{{ $hero['eyebrow'] }}
                        </p>
                        <h1 class="mt-4 text-[40px] font-extrabold leading-[1.02] tracking-[-0.04em] sm:text-6xl">{{ $hero['title'] }}</h1>
                        <p class="mt-5 max-w-xl text-base leading-relaxed text-white/65 md:text-lg">{{ $hero['description'] }}</p>

                        {{-- ana kurlar --}}
                        <dl class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-2 xl:grid-cols-4">
                            @foreach ($majors as [$a, $b])
                                <div class="rounded-2xl bg-white/[0.06] px-4 py-3 ring-1 ring-white/10 backdrop-blur">
                                    <dt class="text-[11px] font-bold tracking-wider text-white/50">{{ $a }}/{{ $b }}</dt>
                                    <dd class="mt-1 text-lg font-bold tabular-nums" x-text="num(pair('{{ $a }}', '{{ $b }}'), pair('{{ $a }}', '{{ $b }}') >= 100 ? 2 : 4)"></dd>
                                    <template x-if="change('{{ $a }}', '{{ $b }}') !== null">
                                        <dd class="text-[11px] font-semibold tabular-nums" :class="change('{{ $a }}', '{{ $b }}') >= 0 ? 'text-emerald-400' : 'text-[#FF8A8C]'"
                                            x-text="(change('{{ $a }}', '{{ $b }}') >= 0 ? '▲ ' : '▼ ') + num(Math.abs(change('{{ $a }}', '{{ $b }}')), 2) + '%'"></dd>
                                    </template>
                                </div>
                            @endforeach
                        </dl>

                        <p class="mt-6 inline-flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-white/45">
                            <span class="relative flex size-2"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-emerald-400"></span></span>
                            {{ __('rates-page.updated') }}: {{ $updated }}
                            <span aria-hidden="true">·</span>
                            <a href="https://www.exchangerate-api.com" target="_blank" rel="noopener nofollow" class="underline-offset-4 hover:text-white/80 hover:underline">Rates By Exchange Rate API</a>
                        </p>
                    </div>

                    {{-- ÇEVİRİCİ --}}
                    <div class="min-w-0 lg:col-span-6">
                        <div id="converter" class="scroll-mt-28 rounded-[28px] bg-white p-5 text-slate-900 shadow-[0_40px_80px_-30px_rgba(0,0,0,0.6)] ring-1 ring-white/20 sm:p-7">
                            {{-- gönderilen --}}
                            <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 focus-within:ring-2 focus-within:ring-[#C62E2E]">
                                <label for="rate-amount" class="text-[11px] font-bold uppercase tracking-[0.14em] text-slate-400">{{ __('rates-page.amount') }}</label>
                                <div class="mt-1 flex items-center gap-3">
                                    <input id="rate-amount" type="text" inputmode="decimal" x-model="amount" autocomplete="off"
                                        class="min-w-0 flex-1 border-0 bg-transparent p-0 text-3xl font-extrabold tabular-nums tracking-tight focus:ring-0">
                                    <select x-model="from" aria-label="{{ __('rates-page.from') }}"
                                        class="w-28 shrink-0 rounded-xl border-0 bg-white py-2 pl-3 pr-8 text-sm font-bold shadow-sm ring-1 ring-slate-200 focus:ring-2 focus:ring-[#C62E2E] sm:w-32">
                                        @foreach ($data['names'] as $code => [$name])
                                            <option value="{{ $code }}" title="{{ $name }}" @selected($code === $data['own'])>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="mt-1 truncate text-xs text-slate-500" x-text="names[from][0]"></p>
                            </div>

                            {{-- yer değiştir --}}
                            <div class="relative z-10 -my-3 flex justify-center">
                                <button type="button" @click="swap()" aria-label="{{ __('rates-page.swap') }}" title="{{ __('rates-page.swap') }}"
                                    class="grid size-11 place-items-center rounded-full bg-slate-900 text-white shadow-lg ring-4 ring-white transition hover:rotate-180 hover:bg-[#C62E2E]">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 4v15m0 0-3.5-3.5M8 19l3.5-3.5M16 20V5m0 0-3.5 3.5M16 5l3.5 3.5"/></svg>
                                </button>
                            </div>

                            {{-- alınan --}}
                            <div class="rounded-2xl bg-slate-900 p-4 text-white">
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-white/45">{{ __('rates-page.to') }}</p>
                                <div class="mt-1 flex items-center gap-3">
                                    <p class="min-w-0 flex-1 truncate text-3xl font-extrabold tabular-nums tracking-tight" x-text="result === null ? '—' : num(result, Math.abs(result) >= 1 ? 2 : 4)"></p>
                                    <select x-model="to" aria-label="{{ __('rates-page.to') }}"
                                        class="w-28 shrink-0 rounded-xl border-0 bg-white/10 py-2 pl-3 pr-8 text-sm font-bold text-white ring-1 ring-white/15 focus:ring-2 focus:ring-[#FF8A8C] sm:w-32 [&>option]:text-slate-900">
                                        @foreach ($data['names'] as $code => [$name])
                                            <option value="{{ $code }}" title="{{ $name }}" @selected($code === $defaultTo)>{{ $code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <p class="mt-1 truncate text-xs text-white/50" x-text="names[to][0]"></p>
                            </div>

                            {{-- hızlı tutarlar + birim kur --}}
                            <div class="mt-4 flex flex-wrap gap-2">
                                @foreach ([10, 50, 100, 500, 1000] as $quick)
                                    <button type="button" @click="amount = '{{ $quick }}'"
                                        :class="value === {{ $quick }} ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-600 ring-slate-200 hover:ring-slate-400'"
                                        class="rounded-full px-3.5 py-1.5 text-xs font-bold tabular-nums ring-1 transition">{{ number_format($quick, 0, '', ' ') }}</button>
                                @endforeach
                            </div>
                            <div class="mt-4 flex flex-wrap justify-between gap-x-4 gap-y-1 border-t border-slate-100 pt-4 text-xs font-medium tabular-nums text-slate-500">
                                <span x-text="'1 ' + from + ' = ' + num(pair(from, to), digits(pair(from, to))) + ' ' + to"></span>
                                <span x-text="'1 ' + to + ' = ' + num(pair(to, from), digits(pair(to, from))) + ' ' + from"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- KUR TABLOSU --}}
        <section class="bg-white py-14 md:py-20">
            <div class="mx-auto max-w-7xl px-4">
                <div class="flex flex-wrap items-end justify-between gap-5">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-[-0.03em] text-slate-900 sm:text-4xl">{{ __('rates-page.table_title') }}</h2>
                        <p class="mt-2 max-w-xl text-slate-600">{{ __('rates-page.table_desc') }}</p>
                    </div>
                </div>

                <div class="mt-8 overflow-hidden rounded-3xl ring-1 ring-slate-200">
                    {{-- araç çubuğu: sekmeler, baz para, arama --}}
                    <div class="flex flex-col gap-3 border-b border-slate-200 bg-slate-50 p-3 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex gap-1 overflow-x-auto rounded-2xl bg-white p-1 ring-1 ring-slate-200 [scrollbar-width:none]" role="tablist">
                            @foreach ($tabs as $key => [$label, $codes])
                                <button type="button" role="tab" @click="tab = '{{ $key }}'; q = ''" :aria-selected="tab === '{{ $key }}' && !q"
                                    :class="tab === '{{ $key }}' && !q ? 'bg-slate-900 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100'"
                                    class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-semibold transition">
                                    {{ $label }} <span class="ml-1 text-xs opacity-60">{{ count($codes) }}</span>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <label class="flex items-center gap-2 rounded-2xl bg-white pl-3 ring-1 ring-slate-200">
                                <span class="whitespace-nowrap text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ __('rates-page.base') }}</span>
                                <select x-model="base" class="w-full border-0 bg-transparent py-2.5 pl-1 pr-8 text-sm font-bold text-slate-900 focus:ring-0 sm:w-56">
                                    @foreach ($data['names'] as $code => [$name])
                                        <option value="{{ $code }}" @selected($code === $tableBase)>{{ $code }} · {{ $name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="relative">
                                <span class="sr-only">{{ __('rates-page.search') }}</span>
                                <svg class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                <input type="search" x-model="q" placeholder="{{ __('rates-page.search') }}" autocomplete="off"
                                    class="block w-full rounded-2xl border-0 bg-white py-2.5 pl-10 pr-3 text-sm ring-1 ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-[#C62E2E] sm:w-60">
                            </label>
                        </div>
                    </div>

                    {{-- tablo --}}
                    <div class="max-h-[640px] overflow-y-auto">
                        <table class="w-full text-left text-sm rtl:text-right">
                            <thead class="sticky top-0 z-10 bg-white/95 text-[11px] font-bold uppercase tracking-[0.12em] text-slate-400 backdrop-blur">
                                <tr class="border-b border-slate-200">
                                    <th scope="col" class="px-4 py-3 sm:px-6">{{ __('rates-page.currency') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right rtl:text-left" x-text="'1 ' + base"></th>
                                    <th scope="col" class="hidden px-4 py-3 text-right md:table-cell rtl:text-left" x-text="'= ' + base"></th>
                                    <th scope="col" x-show="prev" class="hidden px-4 py-3 text-right sm:table-cell rtl:text-left">{{ __('rates-page.change') }}</th>
                                    <th scope="col" class="w-px px-4 py-3 sm:px-6"><span class="sr-only">{{ __('rates-page.convert') }}</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="code in rows" :key="code">
                                    <tr class="group transition hover:bg-slate-50">
                                        <td class="px-4 py-3 sm:px-6">
                                            <div class="flex items-center gap-3">
                                                <span class="grid h-9 w-12 shrink-0 place-items-center rounded-lg bg-slate-900 text-[11px] font-extrabold tracking-wide text-white" x-text="code"></span>
                                                <span class="min-w-0">
                                                    <span class="flex items-center gap-2">
                                                        <span class="truncate font-semibold text-slate-900" x-text="names[code][0]"></span>
                                                        <span x-show="dest.includes(code)" class="shrink-0 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-[#C62E2E] ring-1 ring-red-100">{{ __('rates-page.destination_badge') }}</span>
                                                    </span>
                                                    <span class="block text-xs text-slate-400" x-text="names[code][1]"></span>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right font-bold tabular-nums text-slate-900 rtl:text-left"
                                            x-text="num(pair(base, code), digits(pair(base, code))) + ' ' + code"></td>
                                        <td class="hidden whitespace-nowrap px-4 py-3 text-right tabular-nums text-slate-500 md:table-cell rtl:text-left"
                                            x-text="'1 ' + code + ' = ' + num(pair(code, base), digits(pair(code, base)))"></td>
                                        <td x-show="prev" class="hidden whitespace-nowrap px-4 py-3 text-right text-xs font-semibold tabular-nums sm:table-cell rtl:text-left"
                                            :class="change(base, code) === null ? 'text-slate-300' : (change(base, code) >= 0 ? 'text-emerald-600' : 'text-[#C62E2E]')"
                                            x-text="change(base, code) === null ? '—' : (change(base, code) >= 0 ? '▲ ' : '▼ ') + num(Math.abs(change(base, code)), 2) + '%'"></td>
                                        <td class="px-4 py-3 text-right sm:px-6 rtl:text-left">
                                            <button type="button" @click="pick(code)"
                                                class="rounded-full px-3 py-1.5 text-xs font-bold text-slate-500 ring-1 ring-slate-200 transition hover:bg-slate-900 hover:text-white hover:ring-slate-900 group-hover:text-slate-900">
                                                {{ __('rates-page.convert') }}
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        <p x-show="!rows.length" x-cloak class="px-6 py-14 text-center text-slate-500">{{ __('rates-page.no_results') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @else
        <section class="bg-white py-24">
            <div class="mx-auto max-w-xl px-4 text-center">
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">{{ $hero['title'] }}</h1>
                <p class="mt-4 text-slate-500">{{ __('rates-page.unavailable') }}</p>
            </div>
        </section>
    @endif

    <x-page-sections page="rates" />
@endsection
