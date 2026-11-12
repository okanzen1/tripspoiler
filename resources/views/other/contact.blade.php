@extends('layouts.app')

@section('title', __('contact.meta_title'))
@section('meta_description', __('contact.meta_description'))

{{--
    İletişim: koyu mimari banner (solda başlık + bilgi, sağda banner'a taşan beyaz form kartı).
    Konu çipleri; "Rezervasyon" seçilince partner uyarısı (bilet satışını biz yapmıyoruz). Yazılar panelden (Hero alanı).
--}}
@php
    $hero = \App\Support\PageHero::texts('contact', [
        'eyebrow' => __('contact-page.eyebrow'),
        'title' => __('contact-page.title'),
        'title_highlight' => '',
        'description' => __('contact-page.desc'),
    ]);
    // Alt satır başlıkla bir cümle: başlık panelden geliyorsa alt satır da panelden (boşsa görünmez),
    // ikisi de boşsa dil dosyasındaki eşleşen çift ("Let’s talk" + "about your trip")
    if ($hero['title'] === __('contact-page.title') && blank($hero['title_highlight'])) {
        $hero['title_highlight'] = __('contact-page.title_highlight');
    }
    $topics = [
        'general' => ['message', __('contact-page.topic_general')],
        'booking' => ['ticket', __('contact-page.topic_booking')],
        'partner' => ['users', __('contact-page.topic_partner')],
        'bug' => ['zap', __('contact-page.topic_bug')],
    ];
    $socials = [
        ['Instagram', 'https://instagram.com/tripspoilerofficial', '<path d="M7 2C4.24 2 2 4.24 2 7v10c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5V7c0-2.76-2.24-5-5-5H7zm10 2a3 3 0 013 3v10a3 3 0 01-3 3H7a3 3 0 01-3-3V7a3 3 0 013-3h10z"/><path d="M12 7.5A4.5 4.5 0 1016.5 12 4.51 4.51 0 0012 7.5zm0 7.2A2.7 2.7 0 1114.7 12 2.7 2.7 0 0112 14.7z"/><circle cx="17.5" cy="6.5" r="1"/>'],
        ['TikTok', 'https://tiktok.com/@tripspoilerofficial', '<path d="M21 8.3a6.6 6.6 0 01-3.8-1.2v7.1a6.4 6.4 0 11-6.4-6.4c.4 0 .8 0 1.2.1v3.3a3.1 3.1 0 102.2 3v-12h3.2a6.6 6.6 0 003.6 3.1z"/>'],
    ];
    $email = 'info@tripspoiler.com';
    $input = 'block w-full rounded-2xl border-0 bg-slate-50 px-4 py-3.5 text-[15px] text-slate-900 ring-1 ring-slate-200 outline-none transition placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-slate-900';
    $sent = session('contact_sent');
@endphp

@section('content')
    <section class="relative isolate overflow-x-clip bg-[#0F1420] text-white">
        {{-- mimari ızgara --}}
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div class="absolute inset-0 opacity-[0.07] [background-image:linear-gradient(to_right,white_1px,transparent_1px),linear-gradient(to_bottom,white_1px,transparent_1px)] [background-size:96px_96px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_80%)]"></div>
            <div class="absolute -left-40 top-1/3 size-[34rem] rounded-full bg-[#C62E2E]/15 blur-[130px]"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 pt-6 md:pt-8">
            <x-breadcrumbs dark :items="[
                ['label' => __('activity-show.home'), 'url' => route('home')],
                ['label' => __('footer.contact'), 'url' => route('contact')],
            ]" />

            <div class="mt-12 grid items-start gap-12 pb-16 md:mt-16 md:pb-24 lg:grid-cols-12 lg:gap-14">
                {{-- SOL --}}
                <div class="min-w-0 lg:col-span-5">
                    <div class="flex items-center gap-4 text-[11px] font-bold uppercase tracking-[0.3em] text-white/45">
                        <span class="text-[#FF6B6E]">{{ $hero['eyebrow'] }}</span>
                        <span class="h-px flex-1 bg-white/15"></span>
                    </div>
                    <h1 class="mt-8 text-[52px] font-extrabold leading-[0.92] tracking-[-0.05em] sm:text-7xl lg:text-[84px]">
                        {{ $hero['title'] }}
                        @if (filled($hero['title_highlight']))
                            <span class="mt-3 block text-2xl font-medium tracking-[-0.01em] text-white/55 sm:text-3xl">{{ $hero['title_highlight'] }}</span>
                        @endif
                    </h1>
                    <p class="mt-8 max-w-md border-l border-[#FF6B6E]/60 pl-5 text-[15px] leading-relaxed text-white/65 rtl:border-l-0 rtl:border-r rtl:pl-0 rtl:pr-5">{{ $hero['description'] }}</p>

                    {{-- bilgiler: renkli kutular --}}
                    <div class="mt-10 grid gap-3 sm:grid-cols-2">
                        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-500/20 to-emerald-500/[0.04] p-5 ring-1 ring-emerald-400/25">
                            <span class="pointer-events-none absolute -right-6 -top-6 size-24 rounded-full bg-emerald-400/20 blur-2xl"></span>
                            <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-emerald-300 to-emerald-600 text-white shadow-[0_10px_24px_-10px_rgba(16,185,129,0.8)]">{!! \App\Support\FeatureIcons::svg('clock', 'size-5') !!}</span>
                            <p class="mt-4 text-xs font-semibold text-emerald-200/80">{{ __('contact-page.reply_title') }}</p>
                            <p class="mt-1 text-sm font-semibold leading-snug">{{ __('contact-page.reply_text') }}</p>
                        </div>
                        <a href="mailto:{{ $email }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-500/20 to-indigo-500/[0.05] p-5 ring-1 ring-sky-400/25 transition hover:ring-sky-300/50">
                            <span class="pointer-events-none absolute -right-6 -top-6 size-24 rounded-full bg-sky-400/20 blur-2xl"></span>
                            <span class="grid size-10 place-items-center rounded-xl bg-gradient-to-br from-sky-300 to-indigo-600 text-white shadow-[0_10px_24px_-10px_rgba(56,189,248,0.8)]">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m4 7 8 6 8-6"/></svg>
                            </span>
                            <p class="mt-4 text-xs font-semibold text-sky-200/80">{{ __('contact-page.email_title') }}</p>
                            <p class="mt-1 text-sm font-semibold underline decoration-white/30 underline-offset-4 transition group-hover:decoration-sky-300">{{ $email }}</p>
                        </a>
                        <div class="rounded-3xl bg-white/[0.06] p-5 ring-1 ring-white/10 sm:col-span-2">
                            <p class="text-xs font-semibold text-white/50">{{ __('contact-page.social_title') }}</p>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                @foreach ($socials as [$name, $href, $path])
                                    <a href="{{ $href }}" target="_blank" rel="noopener"
                                        @class(['group inline-flex items-center justify-center gap-2.5 rounded-2xl px-4 py-3 text-sm font-bold text-white transition hover:-translate-y-0.5',
                                            'bg-gradient-to-br from-[#FEDA75] via-[#D62976] to-[#4F5BD5] shadow-[0_12px_28px_-12px_rgba(214,41,118,0.8)]' => $name === 'Instagram',
                                            'bg-black shadow-[0_12px_28px_-12px_rgba(0,0,0,0.9)] ring-1 ring-white/15 [text-shadow:-1px_0_#25F4EE,1px_0_#FE2C55]' => $name === 'TikTok'])>
                                        <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">{!! $path !!}</svg>{{ $name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SAĞ: form kartı (banner'ın altına taşar) --}}
                <div class="lg:col-span-7">
                    <div class="overflow-hidden rounded-[28px] bg-white text-slate-900 shadow-[0_40px_90px_-45px_rgba(0,0,0,0.85)] ring-1 ring-white/10"
                        x-data="{
                            topic: @js(old('topic', 'general')), sending: false, sent: @js((bool) $sent), errors: {}, failed: false,
                            // sayfa yenilenmeden gönder; doğrulama hataları alanların altında
                            async submit(form) {
                                this.sending = true; this.errors = {}; this.failed = false;
                                try {
                                    const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                                    if (res.status === 422) { this.errors = (await res.json()).errors ?? {}; return; }
                                    if (!res.ok) throw new Error();
                                    this.sent = true; form.reset();
                                    this.$el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                } catch { this.failed = true; } finally { this.sending = false; }
                            },
                        }">
                        {{-- kart başlığı --}}
                        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-6 py-4 sm:px-8">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#C62E2E] text-white">{!! \App\Support\FeatureIcons::svg('message', 'size-5') !!}</span>
                            <div class="min-w-0">
                                <p class="font-extrabold tracking-[-0.01em]">{{ __('contact-page.form_title') }}</p>
                                <p class="truncate text-xs text-slate-500">{{ __('contact-page.form_text') }}</p>
                            </div>
                        </div>
                        <div class="p-6 sm:p-8">
                            <div x-show="sent" x-cloak x-transition.opacity.duration.300ms class="py-10 text-center">
                                <span class="mx-auto grid size-16 place-items-center rounded-full bg-emerald-500 text-white shadow-[0_14px_30px_-12px_rgba(16,185,129,0.8)]">
                                    <svg class="size-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                </span>
                                <p class="mt-6 text-2xl font-extrabold tracking-tight">{{ __('contact-page.success_title') }}</p>
                                <p class="mx-auto mt-2 max-w-sm text-slate-500">{{ __('contact-page.success_text') }}</p>
                                <button type="button" @click="sent = false" class="mt-8 inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800">{{ __('contact-page.send_another') }}</button>
                            </div>
                            <form x-show="!sent" method="POST" action="{{ route('contact.submit') }}" class="space-y-5" @submit.prevent="submit($el)">
                                @csrf
                                {{-- bot tuzağı --}}
                                <div class="hidden" aria-hidden="true"><input type="text" name="website" value="" tabindex="-1" autocomplete="off"></div>
                                <input type="hidden" name="topic" :value="topic">

                                {{-- konu --}}
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">{{ __('contact-page.topic') }}</p>
                                    <div class="mt-2.5 grid grid-cols-2 gap-2">
                                        @foreach ($topics as $key => [$icon, $label])
                                            <button type="button" @click="topic = '{{ $key }}'"
                                                :class="topic === '{{ $key }}' ? 'bg-slate-900 text-white ring-slate-900' : 'bg-white text-slate-600 ring-slate-200 hover:ring-slate-400'"
                                                class="flex items-center gap-2.5 rounded-xl px-3.5 py-3 text-left text-sm font-semibold ring-1 transition">
                                                <span :class="topic === '{{ $key }}' ? 'text-[#FF8A8C]' : 'text-slate-400'" class="shrink-0">{!! \App\Support\FeatureIcons::svg($icon, 'size-[18px]') !!}</span>
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                    {{-- rezervasyon: partner uyarısı --}}
                                    <div x-show="topic === 'booking'" x-cloak x-transition.opacity.duration.200ms
                                        class="mt-3 flex gap-3 rounded-2xl bg-amber-50 p-4 text-sm leading-relaxed text-amber-900 ring-1 ring-amber-200">
                                        {!! \App\Support\FeatureIcons::svg('info', 'mt-0.5 size-5 shrink-0 text-amber-600') !!}
                                        <p>{{ __('contact-page.booking_note') }}</p>
                                    </div>
                                </div>

                                <div class="grid gap-5 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">{{ __('contact-page.name') }}</span>
                                        <input type="text" name="name" required maxlength="120" value="{{ old('name') }}" autocomplete="name" class="{{ $input }} mt-2">
                                        @error('name') <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                                        <span x-show="errors.name" x-cloak x-text="errors.name?.[0]" class="mt-1 block text-xs font-medium text-red-600"></span>
                                    </label>
                                    <label class="block">
                                        <span class="text-sm font-semibold text-slate-700">{{ __('contact-page.email') }}</span>
                                        <input type="email" name="email" required maxlength="180" value="{{ old('email') }}" autocomplete="email" class="{{ $input }} mt-2">
                                        @error('email') <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                                        <span x-show="errors.email" x-cloak x-text="errors.email?.[0]" class="mt-1 block text-xs font-medium text-red-600"></span>
                                    </label>
                                </div>
                                <label class="block">
                                    <span class="text-sm font-semibold text-slate-700">{{ __('contact-page.message') }}</span>
                                    <textarea name="message" rows="6" required maxlength="3000" placeholder="{{ __('contact-page.message_placeholder') }}" class="{{ $input }} mt-2 resize-y leading-relaxed">{{ old('message') }}</textarea>
                                    @error('message') <span class="mt-1 block text-xs font-medium text-red-600">{{ $message }}</span> @enderror
                                        <span x-show="errors.message" x-cloak x-text="errors.message?.[0]" class="mt-1 block text-xs font-medium text-red-600"></span>
                                </label>

                                <div class="flex flex-col-reverse items-start justify-between gap-4 pt-1 sm:flex-row sm:items-center">
                                    <p class="flex items-center gap-2 text-xs text-slate-400">{!! \App\Support\FeatureIcons::svg('shield', 'size-4') !!}{{ __('contact-page.privacy') }}</p>
                                    <button type="submit" :disabled="sending"
                                        class="group inline-flex min-h-[52px] w-full items-center justify-center gap-2 rounded-full bg-[#C62E2E] px-7 py-3 text-sm font-bold text-white transition hover:bg-[#A82424] disabled:opacity-70 sm:w-auto">
                                        <span x-text="sending ? @js(__('contact-page.sending')) : @js(__('contact-page.send'))">{{ __('contact-page.send') }}</span>
                                        <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                    </button>
                                </div>
                                <p x-show="failed" x-cloak class="rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700 ring-1 ring-red-200">{{ __('contact-page.error') }}</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- yapısal veri: İletişim sayfası (şirketin iletişim bilgileri layout'taki Organization'da) --}}
    @jsonld(\App\Support\Schema::webPage('ContactPage', __('contact.meta_title'), __('contact.meta_description'), ['mainEntity' => \App\Support\Schema::organizationRef()]))

    <x-page-sections page="contact" />
@endsection
