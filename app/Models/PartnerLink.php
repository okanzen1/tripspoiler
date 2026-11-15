<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Aktivite listesinin sonundaki "Aradığını bulamadın mı?" kartı: partnerin şehir sayfasına doğrudan bağlantı (detay sayfası yok).
 * Panelden yönetilir (Aktiviteler › Partner bağlantıları). city_id boş = bütün şehirler.
 */
class PartnerLink extends Model
{
    protected $casts = ['active' => 'boolean'];

    /** Gidilecek adres: panelde yazılan bağlantı, boşsa partnerin kendi sitesi */
    public function targetUrl(): ?string
    {
        return $this->url ?: $this->partner?->website;
    }

    public function imageUrl(?string $size = null): ?string
    {
        return ActivityImage::mediaUrl($this->image_path, $size);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /** Bir şehrin aktivite listesinde çıkacak bağlantılar: o şehrinkiler + bütün şehirlerinkiler, yayındaki partnerlerle */
    public static function forCity(?int $cityId)
    {
        try {
            return static::query()->with('partner')->where('active', true)
                ->where(fn ($q) => $q->whereNull('city_id')->when($cityId, fn ($q) => $q->orWhere('city_id', $cityId)))
                ->whereHas('partner', fn ($q) => $q->where('active', true))
                ->orderBy('sort_order')->orderBy('id')->get()
                ->filter(fn ($link) => filled($link->targetUrl()))->values(); // gidecek adresi olmayan kart çıkmaz
        } catch (\Throwable) {
            return collect(); // panelde migrate henüz çalışmadıysa (tablo yok) sayfa bozulmasın
        }
    }
}
