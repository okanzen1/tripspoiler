document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-search-modal]');
    const panel = document.querySelector('[data-search-panel]');
    const openers = document.querySelectorAll('[data-search-open]');
    const closeBtn = document.querySelector('[data-search-close]');
    const input = document.querySelector('[data-search-input]');
    const results = document.querySelector('[data-search-results]');
    const status = document.querySelector('[data-search-status]');

    if (!modal || !panel || !input || !openers.length) return;

    let debounceTimer = null;
    let abortCtrl = null;

    /* ---------- UI HELPERS ---------- */
    function showStatus(text) {
        status.textContent = text;
        status.classList.remove('hidden');
    }

    function hideStatus() {
        status.classList.add('hidden');
    }

    function clearResults() {
        results.innerHTML = '';
        results.classList.add('hidden');
    }

    /* ---------- OPEN ---------- */
    function open() {
        document.body.classList.add('overflow-hidden');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
        });

        setTimeout(() => input.focus(), 0);
    }

    /* ---------- CLOSE ---------- */
    function close() {
        modal.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'scale-95', 'translate-y-4');
        document.body.classList.remove('overflow-hidden');

        // cancel in-flight request
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = null;

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // reset EVERYTHING on close (burada input temizlemek OK)
            input.value = '';
            clearResults();
            showStatus('Start typing to see results.');
        }, 200);
    }

    /* ---------- EVENTS ---------- */
    openers.forEach(el => el.addEventListener('click', open));
    closeBtn.addEventListener('click', close);
    modal.addEventListener('click', (e) => { if (e.target === modal) close(); });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) close();
    });

    /* ---------- LIVE SEARCH ---------- */
    input.addEventListener('input', () => {
        const q = input.value.trim();

        clearTimeout(debounceTimer);

        // 🔥 BURASI KRİTİK: input'u temizleme yok!
        if (q.length < 2) {
            clearResults();
            showStatus('Type at least 2 characters.');
            return;
        }

        showStatus('Searching...');
        clearResults();

        debounceTimer = setTimeout(() => fetchResults(q), 300);
    });

    async function fetchResults(q) {
        try {
            // cancel previous
            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const res = await fetch(`/search?q=${encodeURIComponent(q)}`, {
                signal: abortCtrl.signal,
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error('Bad response');

            const data = await res.json();
            render(data);
        } catch (err) {
            // abort ise sessiz geç
            if (err?.name === 'AbortError') return;
            showStatus('Something went wrong.');
        }
    }

    function render(items) {
        if (!items.length) {
            results.innerHTML = '';
            results.classList.add('hidden');
            status.textContent = 'No results found.';
            status.classList.remove('hidden');
            return;
        }

        status.classList.add('hidden');

        results.innerHTML = items.map(item => `
        <a href="${item.url}"
           class="flex items-center justify-between px-6 py-4
                  border-b last:border-b-0 hover:bg-slate-50 transition">
            <div>
                <div class="font-medium text-slate-900">${item.title}</div>
                <div class="text-sm text-slate-500">${item.subtitle}</div>
            </div>
        </a>
    `).join('');

        results.classList.remove('hidden');
    }


    // basit XSS koruması
    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
});
