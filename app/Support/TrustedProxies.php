<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Ziyaretçinin gerçek IP'sini bize iletebilecek ara sunucular: Cloudflare + yerel/özel ağ (sunucudaki nginx, yük dengeleyici).
 * Yalnızca bunlardan gelen isteklerde Cloudflare / X-Forwarded-For başlığına güvenilir; siteye doğrudan gelen biri
 * başlığı uydurup IP'sini (hız sınırı, analitik ülke) değiştiremez.
 * Sunucunun önünde başka bir ara katman varsa .env: TRUSTED_PROXIES=1.2.3.4,5.6.7.0/24 (ya da * = hepsine güven, eski davranış).
 */
class TrustedProxies
{
    /** https://www.cloudflare.com/ips/ */
    private const CLOUDFLARE = [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18', '108.162.192.0/18',
        '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17', '162.158.0.0/15', '104.16.0.0/13',
        '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32', '2a06:98c0::/29', '2c0f:f248::/32',
    ];

    private const PRIVATE = ['127.0.0.0/8', '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '::1/128', 'fc00::/7'];

    /**
     * @return list<string>|string  '*' = hepsi
     */
    public static function all(): array|string
    {
        $extra = trim((string) env('TRUSTED_PROXIES', ''));
        if ($extra === '*') {
            return '*';
        }

        return [...self::CLOUDFLARE, ...self::PRIVATE, ...array_filter(array_map('trim', explode(',', $extra)))];
    }

    /** İsteği bize doğrudan bağlanan adres (REMOTE_ADDR) güvenilen bir ara sunucu mu */
    public static function isProxy(?string $remoteAddr): bool
    {
        $list = self::all();

        return $list === '*' || ($remoteAddr !== null && $remoteAddr !== '' && IpUtils::checkIp($remoteAddr, $list));
    }
}
