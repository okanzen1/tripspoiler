<footer class="bg-white border-t border-slate-200">
    <div class="max-w-7xl mx-auto px-4 py-10 text-sm text-slate-500">

        <div
            class="flex flex-col items-center text-center md:flex-row md:items-start md:justify-between md:text-left gap-8">

            <!-- SOL BLOK -->
            <div class="flex flex-col items-center md:items-start gap-3">

                <p class="text-slate-600">
                    © {{ date('Y') }} TripSpoiler
                </p>

                <div class="flex flex-col items-center md:items-start gap-1 text-xs text-slate-400">

                    <span class="uppercase tracking-wide">
                        {{ __('footer.supported_by') }}
                    </span>

                    <a href="https://megapass.com" target="_blank" rel="noopener">
                        <img src="{{ asset('/images/partners/megapass.svg') }}" alt="Megapass"
                            class="h-7 opacity-80 hover:opacity-100 transition">
                    </a>

                </div>

            </div>

            <!-- SAĞ BLOK -->
            <div class="flex flex-col items-center md:items-end gap-3">

                <div class="flex justify-center md:justify-end gap-4">

                    <a href="{{ url('/about') }}"
                        class="{{ request()->is('about') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                        {{ __('footer.about') }}
                    </a>

                    <a href="{{ url('/contact') }}"
                        class="{{ request()->is('contact') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                        {{ __('footer.contact') }}
                    </a>

                    <a href="{{ url('/privacy') }}"
                        class="{{ request()->is('privacy') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                        {{ __('footer.privacy') }}
                    </a>

                </div>

                <a href="{{ route('reviews.index') }}"
                    class="{{ request()->is('reviews') ? 'text-[#C62E2E]' : 'hover:text-[#C62E2E]' }}">
                    {{ __('footer.reviews') }}
                </a>

            </div>

        </div>

    </div>
</footer>
