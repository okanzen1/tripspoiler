@props(['texts' => null])

{{--
    Bülten kutusu (koyu). Yazılar panelden (Blog bölümü > Bülten kutusu): title, description, eyebrow = buton yazısı.
    Gönderim: newsletter.subscribe (JSON).
--}}
@php
    $texts ??= ['title' => __('newsletter-subscribe.title'), 'description' => __('newsletter-subscribe.desc'), 'eyebrow' => __('newsletter-subscribe.button')];
    $formId = 'newsletter-' . \Illuminate\Support\Str::random(6);
@endphp

<div class="relative h-full overflow-hidden rounded-3xl bg-slate-900 p-6 text-white sm:p-7">
    <div aria-hidden="true" class="pointer-events-none absolute -right-16 -top-16 size-48 rounded-full bg-[#C62E2E]/40 blur-3xl"></div>

    <div class="relative flex items-start gap-3">
        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/15">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
        </span>
        <div>
            <p class="text-lg font-bold leading-snug">{{ $texts['title'] }}</p>
            @if (filled($texts['description']))
                <p class="mt-1 text-sm leading-relaxed text-white/70">{{ $texts['description'] }}</p>
            @endif
        </div>
    </div>

    <form id="{{ $formId }}" class="relative mt-5" novalidate>
        @csrf
        <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off">
        <div class="flex flex-col gap-2 sm:flex-row">
            <label class="sr-only" for="{{ $formId }}-email">E-mail</label>
            <input id="{{ $formId }}-email" type="email" name="email" required placeholder="{{ __('newsletter-subscribe.email_placeholder') }}"
                class="min-w-0 flex-1 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white outline-none transition placeholder:text-white/45 focus:border-white/40 focus:bg-white/15">
            <button type="submit" class="shrink-0 rounded-xl bg-[#C62E2E] px-5 py-3 text-sm font-semibold text-white transition hover:bg-[#A82424] disabled:opacity-60">
                {{ $texts['eyebrow'] }}
            </button>
        </div>
        <p class="mt-2.5 text-xs text-white/50" data-note>{{ __('newsletter-subscribe.note') }}</p>
        <p class="mt-2.5 hidden rounded-xl px-3.5 py-2.5 text-sm" data-message role="status"></p>
    </form>
</div>

<script>
    (() => {
        const form = document.getElementById(@json($formId));
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const button = form.querySelector('button');
            const message = form.querySelector('[data-message]');
            button.disabled = true;
            try {
                const response = await fetch(@json(route('newsletter.subscribe')), {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value, Accept: 'application/json' },
                    body: new FormData(form),
                });
                const data = await response.json();
                if (!response.ok) throw data;
                message.textContent = data.message;
                message.className = 'mt-2.5 rounded-xl bg-emerald-500/15 px-3.5 py-2.5 text-sm text-emerald-200';
                form.reset();
            } catch (error) {
                message.textContent = error.errors?.email?.[0] || error.message || @json(__('newsletter-subscribe.error'));
                message.className = 'mt-2.5 rounded-xl bg-red-500/15 px-3.5 py-2.5 text-sm text-red-200';
            } finally {
                form.querySelector('[data-note]').classList.add('hidden');
                button.disabled = false;
            }
        });
    })();
</script>
