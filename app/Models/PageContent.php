<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Models\Traits\HasImages;

class PageContent extends Model
{
    use HasTranslations;

    protected $fillable = [
        'page_id',
        'city_id',
        'meta_title',
        'meta_description',
        'h1',
        'content',
        'is_active',
    ];

    public $translatable = [
        'meta_title',
        'meta_description',
        'h1',
        'content',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function experienceCategories()
    {
        return $this->hasMany(CityExperienceCategory::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function getImageSource(): string
    {
        return 'page_content';
    }
}
