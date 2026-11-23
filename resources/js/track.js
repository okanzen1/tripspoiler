// Analitik (panel > Analitik). Sunucu: TrackViewController, TrackClickController, TrackLeaveController.
//  - Sayfa görüntüleme: sayfa tarayıcıda gerçekten açılınca bildirilir (botların çoğu JavaScript çalıştırmaz).
//    Otomasyon tarayıcıları (navigator.webdriver) ve arka planda önceden yüklenen sayfalar sayılmaz.
//  - Dış bağlantıya tıklama: sunucu yalnızca partner bağlantılarını kaydeder.
//  - Sayfada kalma (sekme öndeyken geçen süre), en fazla nereye kadar kaydırıldığı ve sayfadaki tıklamalar (sıcaklık haritası):
//    sekme arka plana geçince / sayfadan çıkınca toplu gönderilir. Yazılan metin, form içeriği vb. hiç okunmaz;
//    sadece tıklanan yerin konumu ve bağlantının/düğmenin kendi yazısı.
// sendBeacon: sayfadan çıkılsa da gider, sayfayı yavaşlatmaz.

const heatmap = new URLSearchParams(location.search).has('ts-heatmap');
const framed = window.self !== window.top;

function send(endpoint, data) {
    const body = new FormData();
    Object.entries(data).forEach(([key, value]) => body.append(key, value ?? ''));
    if (!(navigator.sendBeacon && navigator.sendBeacon(endpoint, body))) {
        fetch(endpoint, { method: 'POST', body, keepalive: true, credentials: 'same-origin' }).catch(() => {});
    }
}

const randomKey = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    const bytes = crypto.getRandomValues(new Uint8Array(20));
    return Array.from(bytes, (b) => chars[b % chars.length]).join('');
};

const docHeight = () => Math.max(document.documentElement.scrollHeight, document.body?.scrollHeight ?? 0);

// Bu sayfa açılışının durumu (geri tuşuyla dönülünce yeniden başlar)
let page = null;

function view() {
    if (navigator.webdriver || heatmap || framed) return;
    if (document.querySelector('meta[name="ts-notrack"]')) return; // hata sayfası (404 vb.) sayılmaz
    page = { key: randomKey(), active: 0, since: document.hidden ? null : performance.now(), scroll: 0, clicks: [], sent: 0, opened: performance.now(), last: null };
    measureScroll();
    send('/t/view', { url: location.pathname + location.search, ref: document.referrer, wd: navigator.webdriver ? 1 : 0, pv: page.key });
}

// Önceden yüklenen (prerender) sayfa: kullanıcı gerçekten açınca say
if (document.prerendering) {
    document.addEventListener('prerenderingchange', view, { once: true });
} else {
    view();
}
// Geri/ileri tuşuyla önbellekten dönülen sayfa da bir görüntülemedir
window.addEventListener('pageshow', (event) => event.persisted && view());

function click(event) {
    if (event.type === 'auxclick' && event.button !== 1) return; // sadece orta tık (yeni sekme)
    const link = event.target.closest?.('a[href]');
    if (!link) return;

    let url;
    try { url = new URL(link.href, location.href); } catch { return; }
    if (!/^https?:$/.test(url.protocol) || url.hostname === location.hostname) return;

    send('/t/click', { url: link.href, path: location.pathname });
}

document.addEventListener('click', click, true);
document.addEventListener('auxclick', click, true);

// --- Kalma süresi, kaydırma, tıklama haritası ---

function measureScroll() {
    if (!page) return;
    const reached = Math.round(((window.scrollY + window.innerHeight) / Math.max(1, docHeight())) * 100);
    page.scroll = Math.max(page.scroll, Math.min(100, reached));
}

let scrollTimer;
window.addEventListener('scroll', () => {
    clearTimeout(scrollTimer);
    scrollTimer = setTimeout(measureScroll, 150);
}, { passive: true });

const INTERACTIVE = 'a[href], button, input, select, textarea, label, summary, [role="button"], [role="tab"], [role="link"], [onclick], [x-on\\:click], [\\@click], [tabindex]:not([tabindex="-1"])';

// Sabit (fixed/sticky) bir şeyin içindeki tıklama (ör. üst menü): sayfanın neresinde olursak olalım ekranın aynı yerinde durur
function pinned(element) {
    for (let node = element, depth = 0; node && node !== document.body && depth < 12; node = node.parentElement, depth++) {
        const position = getComputedStyle(node).position;
        if (position === 'fixed' || position === 'sticky') return true;
    }
    return false;
}

function label(element) {
    const target = element.closest(INTERACTIVE) || element;
    if (target.matches('input, textarea, select')) {
        // yazılan değer asla alınmaz: sadece alanın adı
        return target.getAttribute('aria-label') || target.getAttribute('placeholder') || target.getAttribute('name') || target.tagName.toLowerCase();
    }
    const text = (target.getAttribute('aria-label') || target.innerText || target.getAttribute('title') || target.getAttribute('alt') || '').trim();
    if (text) return text.replace(/\s+/g, ' ').slice(0, 110);
    const image = target.querySelector?.('img[alt]');
    return image ? image.alt.slice(0, 110) : target.tagName.toLowerCase();
}

function record(event) {
    if (!page || event.button !== 0 || !event.isTrusted) return;
    const element = event.target instanceof Element ? event.target : event.target?.parentElement;
    if (!element || page.clicks.length >= 30) return;

    const interactive = element.closest(INTERACTIVE);
    const link = element.closest('a[href]');
    const fixed = pinned(element);
    const x = Math.round(event.clientX + (fixed ? 0 : window.scrollX));
    const y = Math.round(event.clientY + (fixed ? 0 : window.scrollY));
    const now = performance.now();

    // Sinirli tıklama: 1 saniye içinde aynı yere (30 px) 3. tıklama
    const last = page.last;
    const near = last && now - last.time < 1000 && Math.abs(last.x - x) < 30 && Math.abs(last.y - y) < 30;
    page.last = { x, y, time: now, count: near ? last.count + 1 : 1 };
    if (near && page.last.count === 3) {
        const first = page.clicks[page.clicks.length - 1];
        if (first) first.r = 1;
    }
    if (near && page.last.count > 1) return; // art arda tıklamalar tek nokta sayılır

    let href = '';
    if (link) {
        try {
            const url = new URL(link.href, location.href);
            href = url.hostname === location.hostname ? url.pathname : url.origin + url.pathname;
        } catch { /* geçersiz bağlantı */ }
    }

    page.clicks.push({
        x, y, w: window.innerWidth, p: docHeight(),
        t: label(element), h: href,
        a: Math.round((now - page.opened) / 1000),
        k: interactive ? 0 : 1,
    });
}

document.addEventListener('click', record, true);

function activeSeconds() {
    if (!page) return 0;
    const running = page.since !== null ? performance.now() - page.since : 0;
    return Math.round((page.active + running) / 1000);
}

function flush() {
    if (!page) return;
    measureScroll();
    const clicks = page.clicks.slice(page.sent);
    page.sent = page.clicks.length;
    send('/t/leave', { pv: page.key, d: activeSeconds(), s: page.scroll, h: window.innerHeight, c: clicks.length ? JSON.stringify(clicks) : '' });
}

document.addEventListener('visibilitychange', () => {
    if (!page) return;
    if (document.hidden) {
        if (page.since !== null) page.active += performance.now() - page.since;
        page.since = null;
        flush();
    } else {
        page.since = performance.now();
    }
});
window.addEventListener('pagehide', flush);

// --- Panel › Sıcaklık haritası: sayfa panelin içinde, ziyaretçinin ekran boyunda gösterilir (?ts-heatmap) ---
// Panel haritayı sayfanın üstüne çizer; sayfa kaydıkça konumu buradan bildirilir, panel de istediği yere kaydırabilir.
if (heatmap && framed) {
    document.documentElement.classList.add('ts-heatmap');
    const style = document.createElement('style');
    // çerez kutusu gibi üstte duran şeyler haritayı kapatmasın; açılış animasyonları beklemesin
    style.textContent = '[data-cookie-consent]{display:none!important}.ts-heatmap *{animation-duration:0s!important;animation-delay:0s!important;transition-duration:0s!important}';
    document.head.append(style);

    const report = () => window.parent.postMessage({ type: 'ts-heatmap', height: docHeight(), width: window.innerWidth, scroll: window.scrollY }, '*');
    let frame = 0;
    window.addEventListener('scroll', () => {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => window.parent.postMessage({ type: 'ts-heatmap', scroll: window.scrollY }, '*'));
    }, { passive: true });
    window.addEventListener('message', (event) => {
        if (event.source !== window.parent || event.data?.type !== 'ts-heatmap-scroll') return;
        window.scrollTo({ top: Number(event.data.y) || 0, behavior: event.data.smooth ? 'smooth' : 'instant' });
    });
    window.addEventListener('load', () => {
        // tembel yüklenen resimler hemen yüklensin ki sayfa boyu gerçek olsun
        document.querySelectorAll('img[loading="lazy"]').forEach((img) => { img.loading = 'eager'; });
        report();
        setTimeout(report, 800);
        setTimeout(report, 2500);
    });
    report();
    new ResizeObserver(report).observe(document.body);
    // haritada bağlantıya tıklanınca site içinde gezilmesin
    document.addEventListener('click', (event) => { event.preventDefault(); event.stopPropagation(); }, true);
}
