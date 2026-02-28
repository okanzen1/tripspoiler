<div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
    <h3 class="text-lg font-semibold text-slate-900">
        Don’t miss new posts
    </h3>

    <p class="text-sm text-slate-600 mt-2">
        Get an occasional email when we publish new TripSpoiler articles.
    </p>

    <form id="newsletter-form" class="mt-4 space-y-3">
        @csrf

        {{-- Honeypot --}}
        <input type="text" name="website" class="hidden">

        <input type="email" name="email" placeholder="you@example.com" required
            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-[#C62E2E]">

        <button type="submit" id="newsletter-btn"
            class="w-full bg-[#C62E2E] text-white rounded-xl py-2.5 font-semibold text-sm
                hover:bg-red-700 transition
                cursor-pointer">
            Notify me about new posts
        </button>

        <div id="newsletter-message" class="hidden text-sm rounded-xl px-4 py-3"></div>
    </form>
</div>

<script>
    document.getElementById('newsletter-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = this;
        const button = document.getElementById('newsletter-btn');
        const messageBox = document.getElementById('newsletter-message');
        const formData = new FormData(form);

        button.disabled = true;
        messageBox.className = 'hidden';

        try {
            const response = await fetch("{{ route('newsletter.subscribe') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                throw data;
            }

            messageBox.textContent = data.message;
            messageBox.className =
                'mt-2 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700';

            form.reset();

        } catch (error) {
            messageBox.textContent =
                error.message || 'Something went wrong. Please try again.';

            messageBox.className =
                'mt-2 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-600';
        } finally {
            button.disabled = false;
        }
    });
</script>
