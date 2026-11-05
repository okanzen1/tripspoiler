<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Şehir rehberi alanı (Gezilecek yerler, Yeme içme…). Panelde yönetilir; sırası Sayfalar › Şehirler › Bölümler'de.
 */
class CityGuideTopic extends Model
{
    use HasTranslations;

    public $translatable = ['name'];
}
