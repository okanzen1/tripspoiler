{{-- Hakkımızda bölümü: yazılar panelden (Sayfalar › Hakkımızda › Sayfa içeriği), boşsa dil dosyası. Sırası/aç-kapa: Bölümler --}}
@php
    $t = fn ($key, $fallback) => \App\Models\SectionText::texts($key, app()->getLocale(), $fallback);
    $story = $t('about_story', ['eyebrow' => __('about-page.story_eyebrow'), 'title' => __('about-page.story_title'),
        'description' => __('about-page.story_1') . "\n\n" . __('about-page.story_2'), 'title_highlight' => __('about-page.story_quote')]);
    $paragraphs = array_filter(preg_split('/\n\s*\n/', (string) $story['description']));
@endphp
    {{-- HİKÂYE --}}
    <section class="bg-white py-16 md:py-24">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-12 lg:gap-14">
            <div class="lg:col-span-5">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]"><span class="h-px w-6 bg-[#C62E2E]"></span>{{ $story['eyebrow'] }}</p>
                <h2 class="mt-4 text-3xl font-extrabold leading-[1.1] tracking-[-0.025em] text-slate-900 sm:text-[42px]">{{ $story['title'] }}</h2>
            </div>
            <div class="space-y-6 text-[17px] leading-relaxed text-slate-600 lg:col-span-7">
                @foreach ($paragraphs as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
                <blockquote class="relative rounded-3xl bg-slate-50 p-6 text-xl font-bold leading-snug tracking-[-0.01em] text-slate-900 ring-1 ring-slate-200 sm:p-8">
                    <span class="absolute -top-4 left-6 grid size-9 place-items-center rounded-full bg-[#C62E2E] text-xl text-white rtl:left-auto rtl:right-6" aria-hidden="true">“</span>
                    {{ $story['title_highlight'] }}
                </blockquote>
            </div>
        </div>
    </section>
