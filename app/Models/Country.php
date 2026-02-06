<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    public $translatable = [
        'name',
        'slug',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

}
