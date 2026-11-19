@extends('layouts.app')

@section('title', __('plan-page.meta_title'))
@section('meta_description', __('plan-page.meta_description'))

{{--
    Gezi planı (yardım talebi): koyu başlık alanı + adım adım form kartı (Alpine). Her ekranda tek konu;
    "Konaklama / yemek / özel gün" adımı yalnızca o yardımlar seçilince açılır. Gönderim arka planda (JSON),
    sonunda teşekkür ekranı. Kayıt şifreli (App\Models\HelpRequest); yazılar lang/*/plan-page.php, başlık panelden (Sayfalar).
--}}
@php
    $t = fn (string $key, array $r = []) => __('plan-page.' . $key, $r);
    $hero = \App\Support\PageHero::texts('plan', [
        'eyebrow' => $t('eyebrow'),
        'title' => $t('title'),
        'title_highlight' => $t('title_highlight'),
        'description' => $t('desc'),
    ]);
    $opts = fn (string $prefix, array $keys) => collect($keys)->mapWithKeys(fn ($k) => [$k => $t($prefix . '_' . $k)])->all();
    $P = \App\Support\PlanTrip::class;
    $icons = [
        'activities' => '<path d="M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1.5a2.5 2.5 0 0 0 0 5V16a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-1.5a2.5 2.5 0 0 0 0-5z"/><path d="M14 6v12" stroke-dasharray="2 2"/>',
        'sightseeing' => '<path d="M9 4 3.5 6v14L9 18l6 2 5.5-2V4L15 6z"/><path d="M9 4v14M15 6v14"/>',
        'stay' => '<path d="M3 18V8M3 14h18v4M21 14v-2a3 3 0 0 0-3-3h-7v5"/><circle cx="7" cy="11" r="1.6"/>',
        'transport' => '<rect x="5" y="3.5" width="14" height="13" rx="3"/><path d="M5 11h14M8.5 20l1.5-3.5M15.5 20 14 16.5"/><circle cx="8.5" cy="13.8" r=".6"/><circle cx="15.5" cy="13.8" r=".6"/>',
        'food' => '<path d="M7 3v8M5 3v4a2 2 0 0 0 4 0V3M7 11v10M17 3c-1.7 0-3 2.2-3 5s1.3 4 3 4v9"/>',
        'daytrips' => '<path d="M3 20h18M5 20l5-9 3 5 2-3 4 7"/><circle cx="17" cy="6" r="2"/>',
        'practical' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'occasion' => '<path d="M12 20s-7-4.4-7-10a4 4 0 0 1 7-2.6A4 4 0 0 1 19 10c0 5.6-7 10-7 10z"/>',
    ];
    $chip = 'inline-flex items-center gap-2 rounded-full px-4 py-2.5 text-sm font-semibold ring-1 transition select-none';
    $input = 'block w-full rounded-2xl border-0 bg-slate-50 px-4 py-3.5 text-[15px] text-slate-900 ring-1 ring-slate-200 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-900';
    $label = 'block text-sm font-bold text-slate-900';
    $config = [
        'url' => route('plan.store'),
        'csrf' => csrf_token(),
        'messages' => ['required' => $t('required'), 'error' => $t('error'), 'cities' => $t('required_cities'), 'needs' => $t('required_needs'), 'email' => $t('required_email'), 'consent' => $t('required_consent'), 'dates' => $t('required_dates')],
    ];
@endphp

@section('content')
    <section class="relative isolate overflow-x-clip bg-[#0F1420] text-white">
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:96px_96px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_80%)]"></div>
            <div class="absolute -left-40 top-1/4 size-[42rem] rounded-full bg-[radial-gradient(circle,rgba(198,46,46,0.22),transparent_65%)]"></div>
            <div class="absolute -right-32 bottom-0 size-[36rem] rounded-full bg-[radial-gradient(circle,rgba(56,189,248,0.10),transparent_65%)]"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pt-6 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('navbar.plan'), 'url' => route('plan')],
            ]" />

            <div class="mx-auto mt-10 max-w-3xl pb-40 text-center md:mt-14 md:pb-48">
                <p class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.25em] text-[#FF6B6E] ring-1 ring-white/15">
                    <span class="size-1.5 rounded-full bg-[#FF6B6E]"></span>{{ $hero['eyebrow'] }}
                </p>
                <h1 class="mt-6 text-[44px] font-extrabold leading-[0.95] tracking-[-0.045em] sm:text-6xl lg:text-7xl">
                    {{ $hero['title'] }}
                    @if (filled($hero['title_highlight']))<span class="text-[#FF6B6E]">{{ $hero['title_highlight'] }}</span>@endif
                </h1>
                <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/70">{{ $hero['description'] }}</p>
                <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-3 text-sm font-semibold text-white/80">
                    @foreach (['trust_free' => '<path d="M5 12.5l4.5 4.5L19 7.5"/>', 'trust_personal' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/>', 'trust_private' => '<rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>'] as $key => $svg)
                        <li class="inline-flex items-center gap-2"><span class="grid size-7 place-items-center rounded-full bg-white/10 ring-1 ring-white/15"><svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $svg !!}</svg></span>{{ $t($key) }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- FORM KARTI: başlık alanına taşar --}}
    <section class="relative bg-slate-50 pb-16 md:pb-24">
        <div class="mx-auto -mt-32 max-w-4xl px-4 md:-mt-36">
            <div x-data="planForm(@js($config))" class="overflow-hidden rounded-[32px] bg-white shadow-[0_40px_100px_-40px_rgba(15,23,42,0.45)] ring-1 ring-slate-200">

                {{-- İLERLEME --}}
                <div x-show="!done" class="border-b border-slate-100 px-6 pt-6 sm:px-10 sm:pt-8">
                    <div class="flex items-center justify-between text-xs font-bold uppercase tracking-[0.18em] text-slate-400">
                        <span x-text="steps[step].label"></span>
                        <span class="tabular-nums"><span x-text="step + 1"></span> / <span x-text="steps.length"></span></span>
                    </div>
                    <div class="mt-3 flex gap-1.5 pb-6" aria-hidden="true">
                        <template x-for="(s, i) in steps" :key="s.id">
                            <span class="h-1.5 flex-1 rounded-full transition-all duration-500" :class="i <= step ? 'bg-gradient-to-r from-[#C62E2E] to-[#FF6B6E]' : 'bg-slate-100'"></span>
                        </template>
                    </div>
                </div>

                <form x-show="!done" @submit.prevent="next()" class="px-6 py-8 sm:px-10 sm:py-10" novalidate>
                    <input type="text" name="website" x-model="f.website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                    {{-- 1. YOLCULUK --}}
                    <div x-show="current === 'trip'" x-transition.opacity.duration.300ms class="space-y-8">
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $t('q_city') }}</h2>
                            <p class="mt-1.5 text-slate-500">{{ $t('q_city_hint') }}</p>
                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                @foreach ($cities as $city)
                                    <button type="button" @click="toggle('cities', {{ $city->id }}, 3)"
                                        class="group relative flex items-center gap-3 rounded-2xl p-3 text-start ring-1 transition"
                                        :class="has('cities', {{ $city->id }}) ? 'bg-slate-900 text-white ring-slate-900 shadow-lg' : 'bg-white text-slate-900 ring-slate-200 hover:ring-slate-400'">
                                        <span class="grid size-10 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100">
                                            @if ($flag = ($city->country?->code && is_file(public_path('flags/' . strtolower($city->country->code) . '.svg')) ? asset('flags/' . strtolower($city->country->code) . '.svg') : null))<img src="{{ $flag }}" alt="" class="size-full object-cover">@else<svg class="size-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>@endif
                                        </span>
                                        <span class="min-w-0 font-bold">{{ $city->name }}</span>
                                        <span x-show="has('cities', {{ $city->id }})" class="absolute end-3 top-3 grid size-5 place-items-center rounded-full bg-[#FF6B6E] text-white"><svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg></span>
                                    </button>
                                @endforeach
                            </div>
                            <label class="mt-4 block">
                                <span class="sr-only">{{ $t('other_city') }}</span>
                                <input type="text" x-model="f.other_city" maxlength="80" placeholder="{{ $t('other_city_ph') }}" class="{{ $input }}">
                            </label>
                        </div>

                        <div>
                            <p class="{{ $label }}">{{ $t('q_dates') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ([1 => $t('dates_yes'), 0 => $t('dates_no')] as $v => $text)
                                    <button type="button" @click="f.dates_known = {{ $v }}" class="{{ $chip }}" :class="f.dates_known === {{ $v }} ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                            <div x-show="f.dates_known === 1" x-transition class="mt-4 grid gap-3 sm:grid-cols-2">
                                <label><span class="mb-1.5 block text-xs font-semibold text-slate-500">{{ $t('start') }}</span><input type="date" x-model="f.start_date" :min="today" class="{{ $input }}"></label>
                                <label><span class="mb-1.5 block text-xs font-semibold text-slate-500">{{ $t('end') }}</span><input type="date" x-model="f.end_date" :min="f.start_date || today" class="{{ $input }}"></label>
                            </div>
                            <div x-show="f.dates_known === 0" x-transition class="mt-4 grid gap-3 sm:grid-cols-2">
                                <label><span class="mb-1.5 block text-xs font-semibold text-slate-500">{{ $t('month') }}</span><input type="month" x-model="f.month" :min="today.slice(0, 7)" class="{{ $input }}"></label>
                                <label><span class="mb-1.5 block text-xs font-semibold text-slate-500">{{ $t('days') }}</span>
                                    <span class="flex items-center gap-2">
                                        <button type="button" @click="f.days = Math.max(1, (f.days || 1) - 1)" class="grid size-12 shrink-0 place-items-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-700 hover:bg-slate-200" aria-label="−">−</button>
                                        <input type="number" min="1" max="30" x-model.number="f.days" class="{{ $input }} text-center font-bold">
                                        <button type="button" @click="f.days = Math.min(30, (f.days || 0) + 1)" class="grid size-12 shrink-0 place-items-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-700 hover:bg-slate-200" aria-label="+">+</button>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <p class="{{ $label }}">{{ $t('q_first_time') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach (['yes' => $t('first_yes'), 'no' => $t('first_no')] as $v => $text)
                                    <button type="button" @click="f.first_time = '{{ $v }}'" class="{{ $chip }}" :class="f.first_time === '{{ $v }}' ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 2. KİM GİDİYOR --}}
                    <div x-show="current === 'people'" x-cloak x-transition.opacity.duration.300ms class="space-y-8">
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $t('q_group') }}</h2>
                            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-5">
                                @foreach ($opts('group', $P::GROUPS) as $v => $text)
                                    <button type="button" @click="f.group = '{{ $v }}'" class="rounded-2xl px-3 py-4 text-center text-sm font-bold ring-1 transition" :class="f.group === '{{ $v }}' ? 'bg-slate-900 text-white ring-slate-900 shadow-lg' : 'bg-white text-slate-800 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-3">
                            @foreach (['adults' => [$t('adults'), 1, 30], 'children' => [$t('children'), 0, 15]] as $field => [$text, $min, $max])
                                <label><span class="mb-1.5 block text-xs font-semibold text-slate-500">{{ $text }}</span>
                                    <span class="flex items-center gap-2">
                                        <button type="button" @click="f.{{ $field }} = Math.max({{ $min }}, (f.{{ $field }} || 0) - 1)" class="grid size-12 shrink-0 place-items-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-700 hover:bg-slate-200" aria-label="−">−</button>
                                        <input type="number" min="{{ $min }}" max="{{ $max }}" x-model.number="f.{{ $field }}" class="{{ $input }} text-center font-bold">
                                        <button type="button" @click="f.{{ $field }} = Math.min({{ $max }}, (f.{{ $field }} || 0) + 1)" class="grid size-12 shrink-0 place-items-center rounded-2xl bg-slate-100 text-xl font-bold text-slate-700 hover:bg-slate-200" aria-label="+">+</button>
                                    </span>
                                </label>
                            @endforeach
                            <label x-show="f.children > 0" x-transition><span class="mb-1.5 block text-xs font-semibold text-slate-500">{{ $t('child_ages') }}</span><input type="text" x-model="f.child_ages" maxlength="60" placeholder="{{ $t('child_ages_ph') }}" class="{{ $input }}"></label>
                        </div>
                        <div>
                            <p class="{{ $label }}">{{ $t('q_access') }} <span class="font-normal text-slate-400">({{ $t('optional') }})</span></p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ($opts('access', $P::ACCESS) as $v => $text)
                                    <button type="button" @click="toggle('access', '{{ $v }}')" class="{{ $chip }}" :class="has('access', '{{ $v }}') ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 3. NEYE YARDIM --}}
                    <div x-show="current === 'needs'" x-cloak x-transition.opacity.duration.300ms>
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $t('q_needs') }}</h2>
                        <p class="mt-1.5 text-slate-500">{{ $t('q_needs_hint') }}</p>
                        <div class="mt-6 grid gap-3 sm:grid-cols-2">
                            @foreach ($P::NEEDS as $v)
                                <button type="button" @click="toggle('needs', '{{ $v }}')" class="group flex items-start gap-4 rounded-2xl p-4 text-start ring-1 transition"
                                    :class="has('needs', '{{ $v }}') ? 'bg-[#C62E2E]/5 ring-2 ring-[#C62E2E]' : 'bg-white ring-slate-200 hover:ring-slate-400'">
                                    <span class="grid size-11 shrink-0 place-items-center rounded-xl transition" :class="has('needs', '{{ $v }}') ? 'bg-[#C62E2E] text-white' : 'bg-slate-100 text-slate-600'">
                                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons[$v] !!}</svg>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block font-bold text-slate-900">{{ $t('needs_' . $v) }}</span>
                                        <span class="mt-0.5 block text-sm leading-snug text-slate-500">{{ $t('needs_' . $v . '_d') }}</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 4. AYRINTILAR (konaklama / yemek / özel gün seçildiyse) --}}
                    <div x-show="current === 'details'" x-cloak x-transition.opacity.duration.300ms class="space-y-8">
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $t('q_details') }}</h2>
                        <div x-show="has('needs', 'stay')" class="space-y-6 rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-200 sm:p-6">
                            <p class="flex items-center gap-2 text-base font-extrabold text-slate-900"><svg class="size-5 text-[#C62E2E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['stay'] !!}</svg>{{ $t('q_stay') }}</p>
                            @foreach (['stay_vibe' => [$t('q_stay_vibe'), $P::STAY_VIBE, false], 'stay_budget' => [$t('q_stay_budget'), $P::STAY_BUDGET, false], 'stay_extras' => [$t('q_stay_extras'), $P::STAY_EXTRAS, true]] as $field => [$q, $keys, $multi])
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ $q }}</p>
                                    <div class="mt-2.5 flex flex-wrap gap-2.5">
                                        @foreach ($opts($field, $keys) as $v => $text)
                                            @if ($multi)
                                                <button type="button" @click="toggle('{{ $field }}', '{{ $v }}')" class="{{ $chip }}" :class="has('{{ $field }}', '{{ $v }}') ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                            @else
                                                <button type="button" @click="f.{{ $field }} = '{{ $v }}'" class="{{ $chip }}" :class="f.{{ $field }} === '{{ $v }}' ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                            <p class="text-xs text-slate-500">{{ $t('stay_note') }}</p>
                        </div>
                        <div x-show="has('needs', 'food')" class="rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-200 sm:p-6">
                            <p class="flex items-center gap-2 text-base font-extrabold text-slate-900"><svg class="size-5 text-[#C62E2E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['food'] !!}</svg>{{ $t('q_food') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ($opts('food', $P::FOOD) as $v => $text)
                                    <button type="button" @click="toggle('food', '{{ $v }}')" class="{{ $chip }}" :class="has('food', '{{ $v }}') ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div x-show="has('needs', 'occasion')" class="rounded-3xl bg-slate-50 p-5 ring-1 ring-slate-200 sm:p-6">
                            <p class="flex items-center gap-2 text-base font-extrabold text-slate-900"><svg class="size-5 text-[#C62E2E]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $icons['occasion'] !!}</svg>{{ $t('q_occasion') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ($opts('occasion', $P::OCCASION) as $v => $text)
                                    <button type="button" @click="f.occasion = '{{ $v }}'" class="{{ $chip }}" :class="f.occasion === '{{ $v }}' ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 5. TARZ --}}
                    <div x-show="current === 'style'" x-cloak x-transition.opacity.duration.300ms class="space-y-8">
                        <div>
                            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $t('q_pace') }}</h2>
                            <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                @foreach ($P::PACE as $v)
                                    <button type="button" @click="f.pace = '{{ $v }}'" class="rounded-2xl p-4 text-start ring-1 transition" :class="f.pace === '{{ $v }}' ? 'bg-slate-900 text-white ring-slate-900 shadow-lg' : 'bg-white text-slate-900 ring-slate-200 hover:ring-slate-400'">
                                        <span class="block font-bold">{{ $t('pace_' . $v) }}</span>
                                        <span class="mt-0.5 block text-sm opacity-70">{{ $t('pace_' . $v . '_d') }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="{{ $label }}">{{ $t('q_interests') }}</p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ($opts('interests', $P::INTERESTS) as $v => $text)
                                    <button type="button" @click="toggle('interests', '{{ $v }}')" class="{{ $chip }}" :class="has('interests', '{{ $v }}') ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="{{ $label }}">{{ $t('q_budget') }} <span class="font-normal text-slate-400">({{ $t('optional') }})</span></p>
                            <div class="mt-3 flex flex-wrap gap-2.5">
                                @foreach ($opts('budget', $P::BUDGET) as $v => $text)
                                    <button type="button" @click="f.budget = f.budget === '{{ $v }}' ? null : '{{ $v }}'" class="{{ $chip }}" :class="f.budget === '{{ $v }}' ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-700 ring-slate-200 hover:ring-slate-400'">{{ $text }}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- 6. İLETİŞİM --}}
                    <div x-show="current === 'contact'" x-cloak x-transition.opacity.duration.300ms class="space-y-6">
                        <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $t('q_contact') }}</h2>
                        <label class="block"><span class="{{ $label }}">{{ $t('q_note') }} <span class="font-normal text-slate-400">({{ $t('optional') }})</span></span>
                            <textarea x-model="f.note" rows="4" maxlength="2000" placeholder="{{ $t('note_ph') }}" class="{{ $input }} mt-2 resize-y"></textarea>
                        </label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label><span class="{{ $label }}">{{ $t('first_name') }}</span><input type="text" x-model="f.first_name" maxlength="60" autocomplete="given-name" class="{{ $input }} mt-2"></label>
                            <label><span class="{{ $label }}">{{ $t('last_name') }}</span><input type="text" x-model="f.last_name" maxlength="60" autocomplete="family-name" class="{{ $input }} mt-2"></label>
                        </div>
                        <label class="block"><span class="{{ $label }}">{{ $t('email') }}</span><input type="email" x-model="f.email" maxlength="180" autocomplete="email" class="{{ $input }} mt-2"></label>
                        <div class="space-y-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                            <label class="flex cursor-pointer items-start gap-3 text-sm text-slate-700"><input type="checkbox" x-model="f.consent" class="mt-0.5 size-4 shrink-0 accent-[#C62E2E]"><span>{!! $t('consent', ['link' => '<a href="' . route('privacy') . '" target="_blank" class="font-semibold text-slate-900 underline underline-offset-2">' . e($t('privacy')) . '</a>']) !!}</span></label>
                            <label class="flex cursor-pointer items-start gap-3 text-sm text-slate-700"><input type="checkbox" x-model="f.newsletter" class="mt-0.5 size-4 shrink-0 accent-[#C62E2E]"><span>{{ $t('newsletter') }}</span></label>
                        </div>
                        <p class="flex items-center gap-2 text-xs font-medium text-slate-500"><svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/></svg>{{ $t('encrypted_note') }}</p>
                    </div>

                    {{-- hata --}}
                    <p x-show="error" x-cloak x-text="error" role="alert" class="mt-6 rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 ring-1 ring-red-200"></p>

                    {{-- DÜĞMELER --}}
                    <div class="mt-10 flex items-center justify-between gap-3">
                        <button type="button" x-show="step > 0" @click="back()" class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-bold text-slate-600 ring-1 ring-slate-200 transition hover:bg-slate-50">
                            <span aria-hidden="true" class="rtl:rotate-180">←</span> {{ $t('back') }}
                        </button>
                        <span x-show="step === 0"></span>
                        <button type="submit" :disabled="sending" class="inline-flex items-center gap-2 rounded-full bg-[#C62E2E] px-7 py-3.5 text-sm font-bold text-white shadow-lg shadow-red-600/25 transition hover:bg-[#A82424] disabled:opacity-60">
                            <span x-text="sending ? @js($t('sending')) : (step === steps.length - 1 ? @js($t('submit')) : @js($t('next')))"></span>
                            <span aria-hidden="true" class="rtl:rotate-180" x-show="!sending">→</span>
                        </button>
                    </div>
                </form>

                {{-- TEŞEKKÜR --}}
                <div x-show="done" x-cloak x-transition.opacity.duration.500ms class="px-6 py-16 text-center sm:px-12 sm:py-20">
                    <span class="mx-auto grid size-20 place-items-center rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white shadow-xl shadow-emerald-500/30 ring-8 ring-emerald-50">
                        <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                    </span>
                    <h2 class="mt-8 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl" x-text="@js($t('done_title')).replace(':name', f.first_name)"></h2>
                    <p class="mx-auto mt-4 max-w-xl text-lg leading-relaxed text-slate-600" x-text="@js($t('done_text')).replace(':email', f.email)"></p>
                    <div class="mt-10 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('activities.index') }}" class="inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-[#C62E2E]">{{ $t('done_cta') }} <span aria-hidden="true" class="rtl:rotate-180">→</span></a>
                        <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 rounded-full px-6 py-3.5 text-sm font-bold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">{{ $t('done_cta_blog') }}</a>
                    </div>
                </div>
            </div>

            {{-- NASIL ÇALIŞIR --}}
            <ol class="mt-12 grid gap-4 sm:grid-cols-3">
                @foreach (['how_1', 'how_2', 'how_3'] as $i => $key)
                    <li class="rounded-3xl bg-white p-6 ring-1 ring-slate-200">
                        <span class="grid size-10 place-items-center rounded-2xl bg-slate-900 text-sm font-extrabold text-white">{{ $i + 1 }}</span>
                        <p class="mt-4 font-extrabold text-slate-900">{{ $t($key) }}</p>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $t($key . '_d') }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <x-page-sections page="plan" />

    <script>
        function planForm(config) {
            return {
                step: 0, sending: false, done: false, error: '',
                today: new Date().toISOString().slice(0, 10),
                f: { website: '', cities: [], other_city: '', dates_known: null, start_date: '', end_date: '', month: '', days: 3, first_time: null,
                    group: null, adults: 2, children: 0, child_ages: '', access: [], needs: [], stay_vibe: null, stay_budget: null, stay_extras: [],
                    food: [], occasion: null, pace: null, interests: [], budget: null, note: '', first_name: '', last_name: '', email: '', consent: false, newsletter: false },
                get steps() {
                    const s = [
                        { id: 'trip', label: @js($t('s_trip')) }, { id: 'people', label: @js($t('s_people')) }, { id: 'needs', label: @js($t('s_needs')) },
                    ];
                    if (['stay', 'food', 'occasion'].some((n) => this.f.needs.includes(n))) s.push({ id: 'details', label: @js($t('s_details')) });
                    s.push({ id: 'style', label: @js($t('s_style')) }, { id: 'contact', label: @js($t('s_contact')) });
                    return s;
                },
                get current() { return this.steps[Math.min(this.step, this.steps.length - 1)].id; },
                has(field, v) { return this.f[field].includes(v); },
                toggle(field, v, max = 99) {
                    const list = this.f[field];
                    const i = list.indexOf(v);
                    if (i > -1) list.splice(i, 1); else if (list.length < max) list.push(v);
                    this.error = '';
                },
                check() {
                    const m = config.messages;
                    if (this.current === 'trip') {
                        if (!this.f.cities.length) return m.cities;
                        if (this.f.dates_known === null || (this.f.dates_known === 1 && !this.f.start_date)) return m.dates;
                    }
                    if (this.current === 'people' && !this.f.group) return m.required;
                    if (this.current === 'needs' && !this.f.needs.length) return m.needs;
                    if (this.current === 'contact') {
                        if (!this.f.first_name.trim() || !this.f.last_name.trim()) return m.required;
                        if (!/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(this.f.email.trim())) return m.email;
                        if (!this.f.consent) return m.consent;
                    }
                    return '';
                },
                scrollTop() { this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' }); },
                back() { this.error = ''; this.step = Math.max(0, this.step - 1); this.scrollTop(); },
                next() {
                    this.error = this.check();
                    if (this.error) return;
                    if (this.step < this.steps.length - 1) { this.step++; this.scrollTop(); return; }
                    this.submit();
                },
                async submit() {
                    this.sending = true;
                    const body = { ...this.f, dates_known: this.f.dates_known ? 1 : 0, consent: this.f.consent ? 1 : 0, newsletter: this.f.newsletter ? 1 : 0 };
                    if (!this.f.dates_known) { body.start_date = null; body.end_date = null; } else { body.month = null; }
                    Object.keys(body).forEach((k) => (body[k] === '' || body[k] === null) && delete body[k]);
                    try {
                        const res = await fetch(config.url, { method: 'POST', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': config.csrf }, body: JSON.stringify(body) });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.ok) { this.done = true; this.scrollTop(); return; }
                        this.error = data.errors ? Object.values(data.errors).flat()[0] : config.messages.error;
                    } catch (e) {
                        this.error = config.messages.error;
                    } finally {
                        this.sending = false;
                    }
                },
            };
        }
    </script>
@endsection
