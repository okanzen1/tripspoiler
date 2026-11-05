<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Anasayfadaki özellik kartı (panel > Site ayarları > Özellik kartları). İkon: App\Support\FeatureIcons.
 */
class Feature extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'text'];

    protected $casts = ['active' => 'boolean'];

    /**
     * Kartın ikonu: panelden yüklenen özel ikon (şekli maske olarak, renk currentColor) ya da hazır ikon.
     */
    public function iconHtml(string $class = 'size-6'): string
    {
        if ($this->icon_path) {
            $url = e(ActivityImage::mediaUrl($this->icon_path));

            return '<span class="inline-block ' . e($class) . '" aria-hidden="true" style="background-color: currentColor; -webkit-mask: url(\'' . $url . '\') center / contain no-repeat; mask: url(\'' . $url . '\') center / contain no-repeat"></span>';
        }

        return \App\Support\FeatureIcons::svg($this->icon, $class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }
}
