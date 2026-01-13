<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class City extends Model
{
    use HasTranslations;

    protected $fillable = [
        'country_id',
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
