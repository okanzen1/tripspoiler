<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{

    protected $fillable = [
        'title',
        'excerpt',
        'slug',
        'meta_title',
        'meta_description',
        'city_id',
        'source',
        'source_id',
        'sort_order',
        'status',
        'click_count',
    ];

    public $translatable = [
        'title',
        'excerpt',
        'slug',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function contents()
    {
        return $this->hasMany(BlogContent::class);
    }

}
