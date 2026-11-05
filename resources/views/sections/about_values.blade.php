{{-- Hakkımızda bölümü: yazılar panelden (Sayfalar › Hakkımızda › Sayfa içeriği), boşsa dil dosyası. Sırası/aç-kapa: Bölümler --}}
@php
    $t = fn ($key, $fallback) => \App\Models\SectionText::texts($key, app()->getLocale(), $fallback);
    $valuesHead = $t('about_values', ['eyebrow' => __('about-page.values_eyebrow'), 'title' => __('about-page.values_title')]);
    $values = collect([['heart', 'from-[#FF6B6E] to-[#C62E2E]'], ['zap', 'from-amber-300 to-orange-500'], ['clock', 'from-emerald-300 to-teal-600'], ['shield', 'from-sky-300 to-indigo-600']])
        ->map(fn ($v, $i) => [$v[0], ...array_values($t('about_value_' . ($i + 1), ['title' => __('about-page.v' . ($i + 1) . '_t'), 'description' => __('about-page.v' . ($i + 1) . '_d')])), $v[1]]);
@endphp
    {{-- İLKELER --}}
    <section class="bg-white py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-4">
            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]"><span class="h-px w-6 bg-[#C62E2E]"></span>{{ $valuesHead['eyebrow'] }}</p>
            <h2 class="mt-4 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-[42px]">{{ $valuesHead['title'] }}</h2>
            <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($values as [$icon, $valueTitle, $valueText, $grad])
                    <div class="group rounded-3xl p-6 ring-1 ring-slate-200 transition hover:-translate-y-1 hover:shadow-[0_24px_50px_-30px_rgba(15,23,42,0.4)]">
                        <span class="grid size-12 place-items-center rounded-2xl bg-gradient-to-br {{ $grad }} text-white shadow-[0_10px_24px_-12px_rgba(0,0,0,0.5)]">{!! \App\Support\FeatureIcons::svg($icon, 'size-6') !!}</span>
                        <p class="mt-5 text-lg font-extrabold text-slate-900">{{ $valueTitle }}</p>
                        <p class="mt-2 text-[15px] leading-relaxed text-slate-600">{{ $valueText }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
