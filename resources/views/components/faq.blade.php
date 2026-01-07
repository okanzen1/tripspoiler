<section class="bg-white py-16">
    <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-2xl md:text-3xl font-bold text-slate-900 text-center">
            Frequently asked questions
        </h2>

        <p class="text-slate-600 text-center mt-2 max-w-2xl mx-auto">
            Questions people often have about TripSpoiler
        </p>

        <div class="mt-10 space-y-4">

            @foreach($faqs as $faq)
                <details class="border border-slate-200 rounded-2xl p-4">
                    <summary class="font-semibold text-slate-900 cursor-pointer">
                        {{ $faq['question'] }}
                    </summary>

                    <p class="text-slate-600 mt-2 text-sm leading-relaxed">
                        {{ $faq['answer'] }}
                    </p>
                </details>

            @endforeach

        </div>

    </div>
</section>
