<section class="{{ $bgColor }} py-16">
    <div class="max-w-4xl mx-auto px-4">

        <h2 class="text-2xl md:text-3xl font-bold text-slate-900 text-center">
            Frequently asked questions
        </h2>

        <p class="text-slate-600 text-center mt-2 max-w-2xl mx-auto">
            Questions people often have about this experience
        </p>

        <div class="mt-10 space-y-4">

            @foreach ($faqs as $faq)
                <details class="border border-slate-200 rounded-2xl p-4 bg-white">
                    <summary class="font-semibold text-slate-900 cursor-pointer">
                        {{ $faq->getTranslation('question', $locale) }}
                    </summary>

                    <p class="text-slate-600 mt-2 text-sm leading-relaxed">
                        {{ $faq->getTranslation('answer', $locale) }}
                    </p>
                </details>
            @endforeach

        </div>

    </div>
</section>

{{-- JSON-LD FAQ Schema --}}
@if (isset($faqSchema))
    <script type="application/ld+json">
        {!! json_encode($faqSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
    </script>
@endif
