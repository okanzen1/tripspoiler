<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'name',
        // 'description',
        'slug',
        'city_id',
        'museum_id',
        // 'sources',
        // 'source_ids',
        'affiliate_id',
        'affiliate_link',
        'sort_order',
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
        // 'sources' => 'array',
        // 'source_ids' => 'array',
        'sort_order' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(Image::class, 'source_id')
            ->where('source', 'venue')
            ->orderBy('sort_order');
    }
}
