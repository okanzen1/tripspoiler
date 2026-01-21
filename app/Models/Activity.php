<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'city_id',
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
        'sort_order' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(Image::class, 'source_id')
            ->where('source', 'venue')
            ->orderBy('sort_order');
    }
}
