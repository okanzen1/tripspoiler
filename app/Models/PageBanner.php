<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sayfa banner resmi (panelde Sayfalar › Aktiviteler / Şehirler › Banner resimleri).
 * Sıra: şehrin resmi → sayfanın genel resmi → anasayfa hero'su → varsayılan. Telefon resmi yoksa masaüstü kullanılır.
 */
class PageBanner extends Model
{
    /**
     * focus: şehrin kendi kadrajı (panelde şehir "Düzenle" paneli; şehrin resmi olmasa da genel resmi o şehirde böyle kırpar).
     * null ise sayfa kadrajı kullanılır (PageHero::visual).
     *
     * @return array{desktop: string, mobile: string|null, focus: int|null}
     */
    public static function urls(string $page, ?int $cityId): array
    {
        $banners = static::where('page_key', $page)
            ->where(fn ($q) => $q->whereNull('city_id')->when($cityId, fn ($q) => $q->orWhere('city_id', $cityId)))
            ->get();
        $own = $cityId ? $banners->firstWhere('city_id', $cityId) : null;
        $general = $banners->firstWhere('city_id', null);

        $desktop = $own?->image ?: $general?->image;
        // Telefon: şehrin telefon resmi; şehrin kendi masaüstü resmi varsa onu, yoksa genel telefon resmi
        $mobile = $own?->image_mobile ?: ($own?->image ? null : $general?->image_mobile);

        $desktopUrl = $desktop
            ? ActivityImage::mediaUrl($desktop, 'md')
            : (($home = Setting::many(['hero_image'])['hero_image']) ? ActivityImage::mediaUrl($home, 'md') : asset('images/hero-banner.webp'));

        return [
            'desktop' => $desktopUrl,
            'mobile' => $mobile ? ActivityImage::mediaUrl($mobile) : null,
            'focus' => $own?->focus !== null ? max(0, min(100, (int) $own->focus)) : null,
        ];
    }
}
