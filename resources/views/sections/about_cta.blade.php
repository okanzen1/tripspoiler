{{-- Hakkımızda bölümü: yazılar panelden (Sayfalar › Hakkımızda › Sayfa içeriği), boşsa dil dosyası. Sırası/aç-kapa: Bölümler --}}
@php
    $t = fn ($key, $fallback) => \App\Models\SectionText::texts($key, app()->getLocale(), $fallback);
    $cta = $t('about_cta', ['title' => __('about-page.cta_title'), 'description' => __('about-page.cta_text')]);
@endphp
    {{-- ÇAĞRI --}}
    <section class="bg-white py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-4">
            <div class="relative isolate overflow-hidden rounded-[32px] bg-gradient-to-br from-[#C62E2E] to-[#8F1D1D] px-6 py-12 text-center text-white sm:px-12 md:py-16">
                <div class="pointer-events-none absolute -right-20 -top-20 -z-10 size-80 rounded-full bg-white/10 blur-3xl" aria-hidden="true"></div>
                <h2 class="text-3xl font-extrabold tracking-[-0.025em] sm:text-4xl">{{ $cta['title'] }}</h2>
                <p class="mx-auto mt-3 max-w-xl text-white/80">{{ $cta['description'] }}</p>
                <div class="mx-auto mt-8 grid max-w-md gap-3 sm:grid-cols-2">
                    <a href="{{ route('activities.index') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2 rounded-full bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-slate-100">
                        {{ __('about-page.cta_activities') }}
                        <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                    <a href="{{ route('contact') }}" class="group inline-flex min-h-[52px] items-center justify-center gap-2 rounded-full bg-white/10 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/20 transition hover:bg-white hover:text-slate-900">
                        {{ __('about-page.cta_contact') }}
                        <svg class="size-4 transition group-hover:translate-x-0.5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>
