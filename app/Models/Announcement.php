<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Spatie\Translatable\HasTranslations;

/**
 * Sitenin en üstündeki kayan şeritte dönen duyuru (panelden yönetilir).
 */
class Announcement extends Model
{
    use HasTranslations;

    protected $fillable = ['text', 'link', 'active', 'sort_order'];

    public $translatable = ['text'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Site içi bağlantılar (/activities) geçerli dilin önekini alır.
     */
    public function url(): ?string
    {
        if (!$this->link) {
            return null;
        }

        return str_starts_with($this->link, '/')
            ? LaravelLocalization::localizeUrl($this->link)
            : $this->link;
    }

    public function isExternal(): bool
    {
        return (bool) $this->link && !str_starts_with($this->link, '/');
    }
}
