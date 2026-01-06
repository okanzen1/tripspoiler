<footer class="bg-white border-t border-slate-200 mt-10">
    <div class="max-w-6xl mx-auto px-4 py-8 text-sm text-slate-500">

        <div class="flex flex-col sm:flex-row justify-between gap-4">

            <p>© {{ date('Y') }} TripSpoiler</p>

            <div class="flex gap-4">
                <a href="{{ url('/about') }}" class="hover:text-[#C62E2E]">About</a>
                <a href="{{ url('/contact') }}" class="hover:text-[#C62E2E]">Contact</a>
                <a href="{{ url('/privacy') }}" class="hover:text-[#C62E2E]">Privacy</a>
            </div>

        </div>
    </div>
</footer>
