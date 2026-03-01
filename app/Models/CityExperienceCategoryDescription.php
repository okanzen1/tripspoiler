<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CityExperienceCategoryDescription extends Model
{
    use HasTranslations;

    protected $fillable = [
        'city_experience_category_id',
        'description',
    ];

    public $translatable = [
        'description',
    ];

    protected $casts = [
        'description' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(
            CityExperienceCategory::class,
            'city_experience_category_id'
        );
    }
}