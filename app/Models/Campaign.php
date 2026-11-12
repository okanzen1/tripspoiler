<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Translatable\HasTranslations;

/**
 * Kampanya / indirim kodu (panel: Kampanyalar). Sitede /campaigns; yayında yoksa bağlantılar gizlenir.
 */
class Campaign extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'description', 'badge', 'note'];

    protected $casts = ['active' => 'boolean', 'starts_at' => 'date', 'ends_at' => 'date'];

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    /** Açık ve tarih aralığında (bitiş günü dahil) */
    public function scopeVisible(Builder $query): void
    {
        $today = now()->toDateString();
        $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $today))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $today));
    }

    /** Yayındaki kampanya sayısı (istek başına bir kez sorgulanır; tablo yoksa 0) */
    public static function liveCount(): int
    {
        static $count = null;
        if ($count === null) {
            try {
                $count = static::visible()->count();
            } catch (\Throwable) {
                $count = 0; // panelde migrate henüz çalışmadıysa site bozulmasın
            }
        }

        return $count;
    }

    public function link(): ?string
    {
        if (!$this->url) {
            return null;
        }

        return str_starts_with($this->url, '/') ? LaravelLocalization::localizeUrl($this->url) : $this->url;
    }

    public function isExternal(): bool
    {
        return (bool) $this->url && !str_starts_with($this->url, '/');
    }
}
