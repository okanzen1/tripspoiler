{{--
    Yorum yazma penceresi: "open-review" olayıyla açılır. body'ye taşınır (kayan/dönüşümlü bölümlerin içinde kalmasın).
    Gönderim: reviews.store (JSON). Yorumlar onaydan sonra sitede görünür.
--}}
<div x-data>
<template x-teleport="body">
<div x-data="reviewModal()" x-on:open-review.window="show()" @keydown.escape.window="close()" x-show="open" x-cloak
    class="fixed inset-0 z-[100] flex items-end justify-center sm:items-center sm:p-4" role="dialog" aria-modal="true" aria-label="{{ __('reviews.form.title') }}">

    {{-- arka plan --}}
    <div x-show="open" x-transition.opacity.duration.250ms @click="close()" class="absolute inset-0 bg-slate-950/55 backdrop-blur-md"></div>

    {{-- pencere --}}
    <div x-show="open"
        x-transition:enter="transition duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
        x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-4 sm:scale-[0.97]" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 translate-y-4"
        class="relative max-h-[92vh] w-full max-w-lg overflow-y-auto md:max-w-2xl lg:max-w-3xl rounded-t-[28px] bg-white shadow-[0_40px_120px_-20px_rgba(2,6,23,0.6)] sm:rounded-[28px]">

        {{-- ÜST BANT --}}
        <div class="relative overflow-hidden bg-slate-900 px-6 pb-6 pt-7 text-white sm:px-8">
            <div aria-hidden="true" class="pointer-events-none absolute -right-10 -top-16 size-48 rounded-full bg-[#C62E2E]/50 blur-3xl"></div>
            <button type="button" @click="close()" aria-label="{{ __('reviews-section.close') }}"
                class="absolute right-4 top-4 z-10 grid size-9 place-items-center rounded-full bg-white/10 text-white/80 ring-1 ring-white/15 transition hover:bg-white/20 hover:text-white rtl:left-4 rtl:right-auto">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
            </button>
            <div class="relative flex items-center gap-3">
                <span class="grid size-14 shrink-0 place-items-center rounded-2xl bg-white shadow-lg">
                    <img src="{{ asset('images/logo-mark.webp') }}" alt="TripSpoiler" class="h-11 w-auto">
                </span>
                <div>
                    <p class="text-xl font-extrabold tracking-tight">{{ __('reviews.form.title') }}</p>
                    <p class="mt-0.5 text-sm leading-snug text-white/70">{{ __('reviews-section.modal_subtitle') }}</p>
                </div>
            </div>
        </div>

        {{-- TEŞEKKÜR --}}
        <div x-show="success" x-transition.opacity class="px-6 py-12 text-center sm:px-8">
            <span class="relative mx-auto grid size-24 place-items-center">
                <span class="absolute inset-0 animate-ping rounded-full bg-emerald-400/20 [animation-iteration-count:2]"></span>
                <span class="relative grid size-24 place-items-center rounded-full bg-emerald-50 ring-8 ring-emerald-50/60">
                    <img src="{{ asset('images/logo-mark.webp') }}" alt="" class="h-14 w-auto">
                </span>
                <span class="absolute -bottom-1 -right-1 grid size-9 place-items-center rounded-full bg-emerald-500 text-white ring-4 ring-white">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7" /></svg>
                </span>
            </span>
            <p class="mt-6 text-2xl font-extrabold text-slate-900">{{ __('reviews-section.thanks_title') }}</p>
            <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500">{{ __('reviews-section.thanks_text') }}</p>
            <button type="button" @click="close()" class="mt-7 rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#C62E2E]">{{ __('reviews-section.close') }}</button>
        </div>

        {{-- FORM --}}
        <form x-show="!success" @submit.prevent="submit()" class="space-y-5 px-6 py-6 sm:px-8 sm:py-7" novalidate>
            <div class="hidden"><input type="text" x-model="website" tabindex="-1" autocomplete="off"></div>

            <div x-show="error" x-transition class="flex items-start gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-200">
                <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
                <span x-text="error"></span>
            </div>

            {{-- PUAN --}}
            <div class="rounded-2xl bg-slate-50 p-4 text-center ring-1 ring-slate-200/70">
                <p class="text-sm font-semibold text-slate-700">{{ __('reviews.form.rating') }}</p>
                <div class="mt-2 flex justify-center gap-1.5" dir="ltr" @mouseleave="hover = 0">
                    <template x-for="i in 5" :key="i">
                        <button type="button" @click="rating = i" @mouseenter="hover = i" :aria-label="labels[i]" :aria-pressed="rating === i"
                            class="rounded-lg p-1 transition duration-200 hover:scale-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-400">
                            <svg class="size-9 transition-colors duration-150" :class="(hover || rating) >= i ? 'text-amber-400 drop-shadow-[0_4px_10px_rgba(251,191,36,0.45)]' : 'text-slate-200'"
                                viewBox="0 0 24 24" fill="currentColor"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/></svg>
                        </button>
                    </template>
                </div>
                <p class="mt-1 h-5 text-sm font-semibold" :class="(hover || rating) ? 'text-amber-600' : 'text-slate-400'" x-text="labels[hover || rating] || @js(__('reviews-section.rating_hint'))"></p>
            </div>

            {{-- İSİM + E-POSTA --}}
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('reviews.form.name') }}</span>
                    <input type="text" x-model="name" required maxlength="120" autocomplete="name" placeholder="{{ __('reviews-section.name_placeholder') }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#C62E2E] focus:ring-4 focus:ring-[#C62E2E]/10">
                </label>
                <label class="block">
                    <span class="text-sm font-medium text-slate-700">{{ __('reviews.form.email') }}</span>
                    <input type="email" x-model="email" required maxlength="190" autocomplete="email" placeholder="{{ __('reviews-section.email_placeholder') }}"
                        class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#C62E2E] focus:ring-4 focus:ring-[#C62E2E]/10">
                </label>
            </div>

            {{-- YORUM --}}
            <label class="block">
                <span class="text-sm font-medium text-slate-700">{{ __('reviews.form.comment') }}</span>
                <textarea x-model="comment" rows="6" required maxlength="1000" placeholder="{{ __('reviews-section.comment_placeholder') }}"
                    class="mt-1.5 block w-full resize-none rounded-xl border border-slate-200 bg-white px-3.5 py-3 text-sm leading-relaxed outline-none transition placeholder:text-slate-400 focus:border-[#C62E2E] focus:ring-4 focus:ring-[#C62E2E]/10"></textarea>
                <span class="mt-1 flex justify-between text-xs">
                    <span :class="comment.trim().length && comment.trim().length < 10 ? 'text-amber-600' : 'text-slate-400'">{{ __('reviews-section.min_chars') }}</span>
                    <span class="text-slate-400"><span x-text="comment.length"></span> / 1000</span>
                </span>
            </label>

            <button type="submit" :disabled="loading"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#C62E2E] py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#C62E2E]/25 transition hover:bg-[#A82424] active:scale-[0.99] disabled:cursor-wait disabled:opacity-70">
                <svg x-show="loading" class="size-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                <span x-text="loading ? @js(__('reviews.form.submitting')) : @js(__('reviews.form.submit'))"></span>
            </button>

            <p class="flex items-center justify-center gap-1.5 text-xs text-slate-400">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                {{ __('reviews-section.email_note') }}
            </p>
        </form>
    </div>
</div>
</template>
</div>

@once
    @push('scripts')
        <script>
            function reviewModal() {
                return {
                    open: false, success: false, loading: false, error: '',
                    name: '', email: '', comment: '', rating: 0, hover: 0, website: '',
                    labels: { 1: @js(__('reviews-section.r1')), 2: @js(__('reviews-section.r2')), 3: @js(__('reviews-section.r3')), 4: @js(__('reviews-section.r4')), 5: @js(__('reviews-section.r5')) },

                    show() {
                        Object.assign(this, { open: true, success: false, error: '', name: '', email: '', comment: '', rating: 0, hover: 0 });
                        document.documentElement.classList.add('overlay-open');
                        document.body.style.overflow = 'hidden';
                    },
                    close() {
                        if (!this.open) return;
                        this.open = false;
                        document.documentElement.classList.remove('overlay-open');
                        document.body.style.overflow = '';
                    },

                    async submit() {
                        if (!this.rating) return this.error = @js(__('reviews.errors.rating_required'));
                        if (this.comment.trim().length < 10) return this.error = @js(__('reviews-section.min_chars'));
                        this.error = '';
                        this.loading = true;
                        try {
                            const res = await fetch(@js(route('reviews.store')), {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()), Accept: 'application/json' },
                                body: JSON.stringify({
                                    name: this.name, email: this.email, comment: this.comment, rating: this.rating, website: this.website,
                                    source: @js($source ?? 'home'), source_id: @js($sourceId ?? null),
                                }),
                            });
                            const data = await res.json();
                            if (data.success) {
                                this.success = true;
                            } else {
                                // Doğrulama hatası: ilk alan mesajı
                                this.error = Object.values(data.errors ?? {})[0]?.[0] ?? data.message ?? @js(__('reviews.errors.something_wrong'));
                            }
                        } catch (e) {
                            this.error = @js(__('reviews.errors.server_error'));
                        } finally {
                            this.loading = false;
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
