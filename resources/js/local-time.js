// Yerel saat yardımcıları (yerel saat bölümü ve /time sayfası). Dış servis yok: tarayıcının saat dilimi verisi (Intl).
const cache = {};

function formatter(tz) {
    return (cache[tz] ??= new Intl.DateTimeFormat('en-US', {
        timeZone: tz, hourCycle: 'h23', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit',
    }));
}

// Saat diliminin o andaki UTC farkı (dakika): İstanbul → 180
function offset(tz, date = new Date()) {
    const p = Object.fromEntries(formatter(tz).formatToParts(date).map((x) => [x.type, x.value]));
    const asUtc = Date.UTC(+p.year, +p.month - 1, +p.day, +p.hour % 24, +p.minute, +p.second);
    return Math.round((asUtc - Math.floor(date.getTime() / 1000) * 1000) / 60000);
}

// O saat dilimindeki tarih/saat parçaları
function parts(tz, date = new Date()) {
    const p = Object.fromEntries(formatter(tz).formatToParts(date).map((x) => [x.type, x.value]));
    return { y: +p.year, mo: +p.month, d: +p.day, h: +p.hour % 24, mi: +p.minute, s: +p.second };
}

// "tz saat diliminde y-m-d h:mi" anının gerçek zamanı (Date)
function zoned(tz, y, mo, d, h, mi) {
    let guess = Date.UTC(y, mo - 1, d, h, mi);
    for (let i = 0; i < 2; i++) guess = Date.UTC(y, mo - 1, d, h, mi) - offset(tz, new Date(guess)) * 60000;
    return new Date(guess);
}

const pad = (n) => String(n).padStart(2, '0');

// Fark metni: 150 dk → "2 sa 30 dk" (birimler sayfanın dilinden)
function diffText(minutes, units) {
    const m = Math.abs(minutes);
    const h = Math.floor(m / 60);
    const r = m % 60;
    return [h ? `${h} ${units.h}` : '', r ? `${r} ${units.m}` : ''].filter(Boolean).join(' ');
}

window.tsTime = {
    offset, parts, zoned, pad, diffText,
    visitorZone: () => Intl.DateTimeFormat().resolvedOptions().timeZone || 'UTC',
    format(date, tz, locale, options) {
        return new Intl.DateTimeFormat(locale, { timeZone: tz, ...options }).format(date);
    },
};
