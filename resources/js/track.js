// Analitik (panel > Analitik). Sunucu: TrackViewController, TrackClickController.
//  - Sayfa görüntüleme: sayfa tarayıcıda gerçekten açılınca bildirilir (botların çoğu JavaScript çalıştırmaz).
//    Otomasyon tarayıcıları (navigator.webdriver) ve arka planda önceden yüklenen sayfalar sayılmaz.
//  - Dış bağlantıya tıklama: sunucu yalnızca partner bağlantılarını kaydeder.
// sendBeacon: sayfadan çıkılsa da gider, sayfayı yavaşlatmaz.

function send(endpoint, data) {
    const body = new FormData();
    Object.entries(data).forEach(([key, value]) => body.append(key, value ?? ''));
    if (!(navigator.sendBeacon && navigator.sendBeacon(endpoint, body))) {
        fetch(endpoint, { method: 'POST', body, keepalive: true, credentials: 'same-origin' }).catch(() => {});
    }
}

function view() {
    if (navigator.webdriver) return;
    send('/t/view', { url: location.pathname + location.search, ref: document.referrer, wd: navigator.webdriver ? 1 : 0 });
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
