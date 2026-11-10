{{--
    SSS: solda başlık ve iletişim kutusu, sağda akordeon (ilk soru açık, bir seferde bir soru).
    Sorular App\View\Components\Faq (panel > SSS); Google için JSON-LD aşağıda.
    Başlık ve iletişim kutusu panelden (SSS › Bölüm yazıları: section_texts "faq" ve "faq_contact"); boşsa dil dosyası.
--}}
@php
    $locale = app()->getLocale();
    $head = \App\Models\SectionText::texts('faq', $locale, [
        'eyebrow' => __('faq.eyebrow'), 'title' => __('faq.title'), 'title_highlight' => __('faq.title_highlight'), 'description' => __('faq.desc'),
    ]);
    $box = \App\Models\SectionText::texts('faq_contact', $locale, [
        'title' => __('faq.contact_title'), 'description' => __('faq.contact_text'), 'eyebrow' => __('faq.contact_cta'),
    ]);
@endphp
<section class="{{ $bgColor }} relative py-16 md:py-24">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 lg:grid-cols-12 lg:gap-14">

        {{-- SOL --}}
        <div class="lg:col-span-4">
            <div class="lg:sticky lg:top-28">
                <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.2em] text-[#C62E2E]">
                    <span class="h-px w-6 bg-[#C62E2E]"></span>{{ $head['eyebrow'] }}
                </p>
                <h2 class="mt-3 text-3xl font-extrabold leading-[1.08] tracking-[-0.025em] text-slate-900 sm:text-4xl lg:text-[44px]">
                    {{ $head['title'] }} <span class="text-[#C62E2E]">{{ $head['title_highlight'] }}</span>
                </h2>
                <p class="mt-4 text-base leading-relaxed text-slate-600">{{ $head['description'] }}</p>

                <div class="mt-8 flex items-center gap-4 rounded-3xl bg-slate-900 p-5 text-white">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white">
                        <img src="{{ asset('images/logo-mark.webp') }}" alt="" class="h-9 w-auto">
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-bold">{{ $box['title'] }}</span>
                        <span class="block text-sm text-white/70">{{ $box['description'] }}</span>
                    </span>
                </div>
                <a href="{{ route('contact') }}" class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-[#C62E2E] px-5 py-3.5 text-sm font-semibold text-white shadow-lg shadow-[#C62E2E]/20 transition hover:bg-[#A82424]">
                    {{ $box['eyebrow'] }} <span aria-hidden="true" class="rtl:rotate-180">→</span>
                </a>
            </div>
        </div>

        {{-- SAĞ: AKORDEON --}}
        <div class="lg:col-span-8" x-data="{ active: 0 }">
            <ul class="space-y-3">
                @foreach ($faqs as $faq)
                    <li class="overflow-hidden rounded-2xl bg-white ring-1 transition duration-300"
                        :class="active === {{ $loop->index }} ? 'ring-[#C62E2E]/30 shadow-[0_18px_40px_-24px_rgba(198,46,46,0.45)]' : 'ring-slate-200/80 hover:ring-slate-300'">
                        <h3>
                            <button type="button" @click="active = active === {{ $loop->index }} ? null : {{ $loop->index }}"
                                :aria-expanded="active === {{ $loop->index }}" aria-controls="faq-{{ $faq->id }}"
                                class="flex w-full items-center gap-4 px-5 py-4 text-left sm:px-6 sm:py-5 rtl:text-right">
                                <span class="grid size-8 shrink-0 place-items-center rounded-full text-xs font-extrabold transition"
                                    :class="active === {{ $loop->index }} ? 'bg-[#C62E2E] text-white' : 'bg-slate-100 text-slate-500'">{{ sprintf('%02d', $loop->iteration) }}</span>
                                <span class="flex-1 text-base font-semibold text-slate-900 sm:text-[17px]">{{ $faq->getTranslation('question', $locale) }}</span>
                                <span class="grid size-8 shrink-0 place-items-center rounded-full ring-1 ring-slate-200 transition duration-300"
                                    :class="active === {{ $loop->index }} ? 'rotate-45 bg-slate-900 text-white ring-slate-900' : 'text-slate-500'">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M12 5v14M5 12h14" /></svg>
                                </span>
                            </button>
                        </h3>
                        {{-- yükseklik geçişi: grid satırı 0fr ↔ 1fr --}}
                        <div id="faq-{{ $faq->id }}" class="grid transition-[grid-template-rows] duration-300 ease-out"
                            :class="active === {{ $loop->index }} ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                            @class(['grid-rows-[1fr]' => $loop->first, 'grid-rows-[0fr]' => !$loop->first])>
                            <div class="overflow-hidden">
                                <p class="px-5 pb-5 text-[15px] leading-relaxed text-slate-600 sm:pl-[4.5rem] sm:pr-16 sm:pb-6 rtl:sm:pl-16 rtl:sm:pr-[4.5rem]">
                                    {{ $faq->getTranslation('answer', $locale) }}
                                </p>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- JSON-LD FAQ Schema --}}
@if (isset($faqSchema))
    @jsonld($faqSchema)
@endif
