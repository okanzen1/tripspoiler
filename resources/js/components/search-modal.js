document.addEventListener('DOMContentLoaded', () => {
    const modal   = document.querySelector('[data-search-modal]');
    const panel   = document.querySelector('[data-search-panel]');
    const openers = document.querySelectorAll('[data-search-open]');
    const closeBtn = document.querySelector('[data-search-close]');
    const input   = document.querySelector('[data-search-input]');
    const results = document.querySelector('[data-search-results]');
    const status  = document.querySelector('[data-search-status]');

    if (!modal || !panel || !input || !openers.length) return;

    let debounceTimer = null;
    let abortCtrl = null;
    let scrollY = 0;

    /* ---------- HELPERS ---------- */
    function showStatus(text) {
        status.textContent = text;
        status.classList.remove('hidden');
    }

    function clearResults() {
        results.innerHTML = '';
        results.classList.add('hidden');
    }

    /* ---------- OPEN ---------- */
    function open(e) {
        // scroll pozisyonunu kaydet
        scrollY = window.scrollY;

        // BODY LOCK (sağa-sola kayma fix)
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
        });

        // mobil klavye fix
        if (e?.type === 'click') {
            input.focus({ preventScroll: true });
        } else {
            setTimeout(() => input.focus(), 50);
        }
    }

    /* ---------- CLOSE ---------- */
    function close() {
        modal.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'scale-95', 'translate-y-4');

        // klavyeyi kapat
        input.blur();

        // fetch iptal
        if (abortCtrl) {
            abortCtrl.abort();
            abortCtrl = null;
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // BODY UNLOCK
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.left = '';
            document.body.style.right = '';
            document.body.style.width = '';

            // scroll geri koy
            window.scrollTo(0, scrollY);

            // reset
            input.value = '';
            clearResults();
            showStatus('Start typing to see results.');
        }, 200);
    }

    /* ---------- EVENTS ---------- */
    openers.forEach(el =>
        el.addEventListener('click', (e) => open(e))
    );

    closeBtn.addEventListener('click', close);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) close();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            close();
        }
    });

    /* ---------- LIVE SEARCH ---------- */
    input.addEventListener('input', () => {
        const q = input.value.trim();

        clearTimeout(debounceTimer);

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
            if (abortCtrl) abortCtrl.abort();
            abortCtrl = new AbortController();

            const res = await fetch(`/search?q=${encodeURIComponent(q)}`, {
                signal: abortCtrl.signal,
                headers: { Accept: 'application/json' }
            });

            if (!res.ok) throw new Error('Bad response');

            const data = await res.json();
            render(data);
        } catch (err) {
            if (err.name === 'AbortError') return;
            showStatus('Something went wrong.');
        }
    }

    function render(items) {
        if (!items.length) {
            clearResults();
            showStatus('No results found.');
            return;
        }

        status.classList.add('hidden');

        results.innerHTML = items.map(item => `
            <a href="${item.url}"
               class="flex px-6 py-4 border-b last:border-b-0 hover:bg-slate-50 transition">
                <div>
                    <div class="font-medium text-slate-900">
                        ${escapeHtml(item.title)}
                    </div>
                    <div class="text-sm text-slate-500">
                        ${escapeHtml(item.subtitle)}
                    </div>
                </div>
            </a>
        `).join('');

        results.classList.remove('hidden');
    }

    /* ---------- XSS GUARD ---------- */
    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
