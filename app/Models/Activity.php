<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
class Activity extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'city_id',
        'affiliate_id',
        'affiliate_link',
        'sort_order',
        'most_popular',
        'status',
    ];

    /**
     * Translatable alanlar
     */
    public $translatable = [
        'name',
        'description',
        'slug',
    ];

    /**
     * Castler
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
        'most_popular' => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(Image::class, 'source_id')
            ->where('source', 'activity')
            ->orderBy('sort_order');
    }
}
