<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-15">
            <h2 class="text-3xl md:text-4xl font-semibold text-slate-900 tracking-tight">
                {{ __('steps-section.title') }}
            </h2>

            <p class="mt-4 text-slate-500 text-lg max-w-2xl mx-auto leading-relaxed">
                {!! nl2br(__('steps-section.desc')) !!}
            </p>
        </div>

        <div id="stepsWrapper"
            class="md:grid md:grid-cols-3 md:gap-16
                   flex md:flex-none
                   gap-8
                   overflow-x-auto md:overflow-visible
                   snap-x snap-mandatory
                   scroll-smooth">

            @foreach ([
        [
            'icon' => 'eye',
            'step' => '01',
            'title' => __('steps-section.step1_title'),
            'text' => __('steps-section.step1_text'),
        ],
        [
            'icon' => 'compass',
            'step' => '02',
            'title' => __('steps-section.step2_title'),
            'text' => __('steps-section.step2_text'),
        ],
        [
            'icon' => 'check',
            'step' => '03',
            'title' => __('steps-section.step3_title'),
            'text' => __('steps-section.step3_text'),
        ],
    ] as $item)
                <div
                    class="step-item
                            min-w-[85%]
                            md:min-w-0
                            snap-start
                            space-y-5">

                    <div class="w-10 h-10 rounded-xl bg-[#C62E2E]/8 flex items-center justify-center">
                        @if ($item['icon'] === 'eye')
                            <svg class="w-4 h-4 text-[#C62E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        @elseif($item['icon'] === 'compass')
                            <svg class="w-4 h-4 text-[#C62E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6 2-5 4-1z" />
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-[#C62E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </div>

                    <div class="text-xs tracking-widest uppercase text-slate-400">
                        {{ __('steps-section.step_label') }} {{ $item['step'] }}
                    </div>

                    <h3 class="text-lg font-medium text-slate-900">
                        {{ $item['title'] }}
                    </h3>

                    <p class="text-sm text-slate-600 leading-relaxed">
                        {{ $item['text'] }}
                    </p>
                </div>
            @endforeach
        </div>

        <div id="stepIndicators" class="flex md:hidden justify-center gap-2 mt-6">
            <div class="indicator w-2 h-2 rounded-full bg-[#C62E2E]"></div>
            <div class="indicator w-2 h-2 rounded-full bg-slate-300"></div>
            <div class="indicator w-2 h-2 rounded-full bg-slate-300"></div>
        </div>

    </div>
</section>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const wrapper = document.getElementById("stepsWrapper");
            const indicators = document.querySelectorAll(".indicator");
            const items = document.querySelectorAll(".step-item");

            if (!wrapper) return;

            wrapper.addEventListener("scroll", () => {
                const index = Math.round(wrapper.scrollLeft / items[0].offsetWidth);

                indicators.forEach((dot, i) => {
                    if (i === index) {
                        dot.classList.remove("bg-slate-300");
                        dot.classList.add("bg-[#C62E2E]");
                    } else {
                        dot.classList.remove("bg-[#C62E2E]");
                        dot.classList.add("bg-slate-300");
                    }
                });
            });

        });
    </script>
@endpush