<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Translatable\HasTranslations;

/**
 * "Nasıl çalışır" adımı (panel > Site ayarları > Nasıl çalışır). İkon: App\Support\FeatureIcons.
 */
class Step extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'text', 'cta'];

    protected $casts = ['active' => 'boolean'];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /** Site içi adres (/blog) geçerli dilin önekini alır */
    public function url(): ?string
    {
        if (!$this->link) {
            return null;
        }

        return str_starts_with($this->link, '/') ? LaravelLocalization::localizeUrl($this->link) : $this->link;
    }

    public function isExternal(): bool
    {
        return (bool) $this->link && !str_starts_with($this->link, '/');
    }
}
