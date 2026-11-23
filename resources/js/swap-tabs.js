// Sekme gibi çalışan bağlantılar (ör. blog: Gezi Rehberleri / Seyahat Haberleri): sayfa yenilenmeden yeni sayfanın
// [data-swap-root] alanı getirilip yerine konur, adres çubuğu güncellenir. Beklerken liste kendi yükleniyor görünümüne geçer
// ([data-swap-loading] Alpine bileşeninin loading'i: etiket süzgecindeki çizgi + iskelet kartlar); yoksa sekmede dönen işaret.
// Bir sorun olursa normal sayfa geçişine düşer.
const SPIN = '<svg class="ml-1.5 inline size-3.5 animate-spin align-[-2px]" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" class="opacity-25"/><path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>';

let busy = false;

async function swap(url, { push = true, link = null } = {}) {
    const root = document.querySelector('[data-swap-root]');
    if (!root || busy) return;
    busy = true;
    const scrollTarget = link?.closest('#all-guides');
    const list = root.querySelector('[data-swap-loading]');
    const state = list && window.Alpine ? window.Alpine.$data(list) : null;
    if (state && 'loading' in state) {
        clearTimeout(state.timer);
        state.loading = true;
    } else if (link) {
        link.insertAdjacentHTML('beforeend', SPIN);
    }
    // seçili sekme hemen değişsin (yanıt gelmeden)
    if (link) {
        const href = link.getAttribute('href');
        root.querySelectorAll('a[data-swap-link]').forEach((a) => {
            const on = a.getAttribute('href') === href;
            const other = [...a.parentElement.children].find((b) => b !== a);
            if (on && other && other.getAttribute('aria-current') === 'page') {
                [a.className, other.className] = [other.className, a.className];
                other.removeAttribute('aria-current');
                a.setAttribute('aria-current', 'page');
            }
        });
    }
    const started = performance.now();
    try {
        const res = await fetch(url, { headers: { Accept: 'text/html' }, credentials: 'same-origin' });
        if (!res.ok) throw new Error(String(res.status));
        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
        const next = doc.querySelector('[data-swap-root]');
        if (!next) throw new Error('no root');
        // çok hızlı gelirse de yükleniyor kısa bir an görünsün (göz kırpması gibi olmasın)
        const wait = 380 - (performance.now() - started);
        if (wait > 0) await new Promise((resolve) => setTimeout(resolve, wait));
        root.replaceWith(document.importNode(next, true));
        document.title = doc.title;
        for (const selector of ['meta[name="description"]', 'link[rel="canonical"]']) {
            const fresh = doc.querySelector(selector);
            const current = document.querySelector(selector);
            if (fresh && current) current.replaceWith(fresh.cloneNode(true));
        }
        if (push) history.pushState({ swap: true }, '', url);
        // alttaki sekmeye basıldıysa liste görünür kalsın
        if (scrollTarget) document.getElementById('all-guides')?.scrollIntoView({ block: 'start' });
    } catch {
        location.href = url;
    } finally {
        busy = false;
    }
}

document.addEventListener('click', (event) => {
    const link = event.target.closest?.('a[data-swap-link]');
    if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0) return;
    event.preventDefault();
    if (link.getAttribute('aria-current') === 'page') return;
    swap(link.href, { link });
});

window.addEventListener('popstate', () => {
    if (document.querySelector('[data-swap-root]')) swap(location.href, { push: false });
});
