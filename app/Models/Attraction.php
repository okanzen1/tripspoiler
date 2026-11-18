<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/**
 * Yer sayfası (panel: Yerler). Biletler = bağlı aktiviteler; passler ve kombolar en üstte.
 */
class Attraction extends Model
{
    use HasTranslations;

    public $translatable = ['name', 'summary', 'content', 'opening_hours', 'closed_days', 'getting_there', 'dress_code',
        'good_to_know', 'meta_title', 'meta_description'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function scopeVisible(Builder $query): void
    {
        $query->where('active', true)->whereHas('city', fn ($q) => $q->visible());
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function activities(): BelongsToMany
    {
        return $this->belongsToMany(Activity::class);
    }

    /** Bu dildeki "Gitmeden önce bilin" maddeleri */
    public function goodToKnow(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', (string) $this->good_to_know)), 'strlen'));
    }

    /** LocalUrl / dil değiştirici için: yer adresleri her dilde aynı */
    public function localSlug(?string $locale = null): string
    {
        return $this->slug;
    }

    public function imageUrl(?string $size = null): ?string
    {
        return $this->image_path ? ActivityImage::mediaUrl($this->image_path, $size) : null;
    }

    /** SEO başlığı: panelde yazılan ya da "{ad} Tickets & Visitor Guide {yıl}" (yıl her yıl kendiliğinden güncel) */
    public function seoTitle(): string
    {
        $title = trim((string) $this->meta_title);

        return $title !== '' ? \App\Support\SeoYear::add($title) : __('attraction-page.meta_title', ['name' => $this->name, 'year' => now()->year]);
    }
}
