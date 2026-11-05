<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Şehir sayfasının üstündeki genel başlık ve açıklama.
 */
class CityGuide extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'description'];
}
