{{--
    Çerez onayı (KVKK / GDPR). Karar "ts_consent" çerezinde (all | necessary), 1 yıl; kutu tekrar sorulmaz.
     - Kabul edilene kadar Google Analytics yüklenmez (layouts/app.blade.php › tsLoadAnalytics) ve kendi sayacımız
       tarayıcıya kimlik çerezi koymaz (App\Support\Tracker::consented); ziyaret yine çerezsiz sayılır, IP şifreli.
     - "Sadece gerekli": Google Analytics çerezleri silinir; sayaç çerezlerini sunucu bir sonraki sayfada siler.
     - Sitenin altındaki "Çerez ayarları" kutuyu tekrar açar (window event "cookie-settings").
--}}
<div x-data="{
        open: false,
        init() {
            this.open = !document.cookie.split('; ').some((c) => c.startsWith('ts_consent='));
            window.addEventListener('cookie-settings', () => this.open = true);
        },
        choose(value) {
            const secure = location.protocol === 'https:' ? '; Secure' : '';
            document.cookie = 'ts_consent=' + value + '; Max-Age=31536000; Path=/; SameSite=Lax' + secure;
            if (value === 'all') {
                window.tsLoadAnalytics?.();
            } else {
                // Google Analytics çerezleri (_ga, _ga_XXXX, _gid): hem bu alan adı hem üst alan adı için sil
                const host = location.hostname.replace(/^www\./, '');
                document.cookie.split('; ').map((c) => c.split('=')[0]).filter((n) => /^_ga|^_gid|^_gat/.test(n)).forEach((name) => {
                    ['', '; Domain=' + host, '; Domain=.' + host].forEach((domain) => {
                        document.cookie = name + '=; Max-Age=0; Path=/' + domain;
                    });
                });
            }
            this.open = false;
        },
    }" x-cloak x-show="open" x-transition.opacity.duration.200ms
    role="dialog" aria-modal="false" aria-labelledby="cookie-consent-title"
    class="fixed inset-x-4 bottom-4 z-[80] sm:inset-x-auto sm:bottom-6 sm:left-6 sm:max-w-md rtl:sm:left-auto rtl:sm:right-6">
    <div class="rounded-3xl bg-white p-5 shadow-[0_24px_60px_-20px_rgba(15,23,42,0.45)] ring-1 ring-slate-200 sm:p-6">
        <p id="cookie-consent-title" class="flex items-center gap-2 text-base font-extrabold tracking-tight text-slate-900">
            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-amber-100 text-amber-700" aria-hidden="true">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a4 4 0 0 0 4.8 4.8 4 4 0 0 0 5 5z"/><path d="M8.5 13.5h.01M12 17h.01M15.5 14.5h.01M9 9h.01"/></svg>
            </span>
            {{ __('cookies.title') }}
        </p>
        <p class="mt-2.5 text-sm leading-relaxed text-slate-600">
            {{ __('cookies.text') }}
            <a href="{{ route('privacy') }}#cookies" class="font-semibold text-slate-900 underline decoration-slate-300 underline-offset-2 hover:decoration-slate-900">{{ __('cookies.privacy') }}</a>
        </p>
        <div class="mt-4 flex flex-col-reverse gap-2 sm:flex-row">
            <button type="button" @click="choose('necessary')"
                class="flex-1 rounded-full px-4 py-2.5 text-sm font-semibold text-slate-700 ring-1 ring-slate-200 transition hover:bg-slate-50">{{ __('cookies.necessary') }}</button>
            <button type="button" @click="choose('all')"
                class="flex-1 rounded-full bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">{{ __('cookies.accept') }}</button>
        </div>
    </div>
</div>
