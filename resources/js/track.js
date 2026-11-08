// Analitik: dış bağlantıya tıklanınca sunucuya arka planda bildir (sayfadan çıkılsa da gider: sendBeacon).
// Sunucu yalnızca partner bağlantılarını kaydeder (app/Http/Controllers/TrackClickController.php).
const endpoint = '/t/click';

function report(event) {
    if (event.type === 'auxclick' && event.button !== 1) return; // sadece orta tık (yeni sekme)
    const link = event.target.closest?.('a[href]');
    if (!link) return;

    let url;
    try { url = new URL(link.href, location.href); } catch { return; }
    if (!/^https?:$/.test(url.protocol) || url.hostname === location.hostname) return;

    const body = new FormData();
    body.append('url', link.href);
    body.append('path', location.pathname);
    if (!(navigator.sendBeacon && navigator.sendBeacon(endpoint, body))) {
        fetch(endpoint, { method: 'POST', body, keepalive: true, credentials: 'same-origin' }).catch(() => {});
    }
}

document.addEventListener('click', report, true);
document.addEventListener('auxclick', report, true);
