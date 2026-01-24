document.addEventListener('DOMContentLoaded', () => {
    const modal    = document.querySelector('[data-search-modal]');
    const panel    = document.querySelector('[data-search-panel]');
    const openers  = document.querySelectorAll('[data-search-open]');
    const closeBtn = document.querySelector('[data-search-close]');
    const input    = document.querySelector('[data-search-input]');
    const results  = document.querySelector('[data-search-results]');
    const status   = document.querySelector('[data-search-status]');

    if (!modal || !panel || !input || !openers.length) return;

    let debounceTimer = null;
    let abortCtrl = null;

    /* ---------- UI HELPERS ---------- */
    function showStatus(text) {
        if (!status) return;
        status.textContent = text;
        status.classList.remove('hidden');
    }

    function clearResults() {
        if (!results) return;
        results.innerHTML = '';
        results.classList.add('hidden');
    }

    /* ---------- OPEN / CLOSE ---------- */
    function openSearch(e) {
        // zaten açıksa tekrar açma (touchend + click çakışmasın)
        if (!modal.classList.contains('hidden')) return;

        if (e) e.preventDefault?.();

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'scale-95', 'translate-y-4');
        });

        // iOS Chrome için en güvenlisi: kısa gecikme ile focus
        setTimeout(() => {
            input.focus();
        }, 120);
    }

    function closeSearch() {
        modal.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'scale-95', 'translate-y-4');

        input.blur();

        if (abortCtrl) {
            abortCtrl.abort();
            abortCtrl = null;
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            input.value = '';
            clearResults();
            showStatus('Start typing to see results.');
        }, 200);
    }

    /* ---------- OPEN EVENTS (iOS SAFE) ---------- */
    openers.forEach(el => {
        // modern (iOS dahil) en stabil
        el.addEventListener('pointerup', openSearch);

        // eski iOS / bazı durumlar için ekstra garanti
        el.addEventListener('touchend', (e) => {
            e.preventDefault();
            openSearch(e);
        }, { passive: false });

        // desktop fallback
        el.addEventListener('click', openSearch);
    });

    if (closeBtn) closeBtn.addEventListener('click', closeSearch);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeSearch();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeSearch();
        }
    });

    /* ---------- LIVE SEARCH (basit) ---------- */
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
        if (!results) return;

        if (!items || !items.length) {
            clearResults();
            showStatus('No results found.');
            return;
        }

        if (status) status.classList.add('hidden');

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

    function escapeHtml(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // başlangıç
    showStatus('Start typing to see results.');
});
