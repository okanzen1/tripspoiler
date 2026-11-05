{{-- Hakkımızda bölümü: yazılar panelden (Sayfalar › Hakkımızda › Sayfa içeriği), boşsa dil dosyası. Sırası/aç-kapa: Bölümler --}}
@php
    $t = fn ($key, $fallback) => \App\Models\SectionText::texts($key, app()->getLocale(), $fallback);
    $how = $t('about_how', ['eyebrow' => __('about-page.how_eyebrow'), 'title' => __('about-page.how_title')]);
    $steps = collect([['book', 1], ['check', 2], ['ticket', 3]])->map(fn ($s) => [$s[0], ...array_values($t('about_how_' . $s[1], [
        'title' => __('about-page.how_' . $s[1] . '_t'), 'description' => __('about-page.how_' . $s[1] . '_d')]))]);
@endphp
    {{-- NASIL ÇALIŞIR --}}
    <section class="bg-slate-50 py-16 md:py-24">
        <div class="mx-auto max-w-7xl px-4">
            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]"><span class="h-px w-6 bg-[#C62E2E]"></span>{{ $how['eyebrow'] }}</p>
            <h2 class="mt-4 text-3xl font-extrabold tracking-[-0.025em] text-slate-900 sm:text-[42px]">{{ $how['title'] }}</h2>
            <ol class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ($steps as [$icon, $stepTitle, $stepText])
                    <li class="relative rounded-3xl bg-white p-7 ring-1 ring-slate-200/80 shadow-[0_14px_36px_-24px_rgba(15,23,42,0.3)]">
                        <span class="absolute right-6 top-5 text-5xl font-extrabold tracking-tight text-slate-100 rtl:left-6 rtl:right-auto">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="relative grid size-12 place-items-center rounded-2xl bg-slate-900 text-white">{!! \App\Support\FeatureIcons::svg($icon, 'size-6') !!}</span>
                        <p class="relative mt-5 text-lg font-extrabold text-slate-900">{{ $stepTitle }}</p>
                        <p class="relative mt-2 text-[15px] leading-relaxed text-slate-600">{{ $stepText }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
