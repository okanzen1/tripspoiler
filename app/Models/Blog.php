<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Models\Activity;


class Blog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'title',
        'excerpt',
        'slug',
        'meta_title',
        'meta_description',
        'themes',
        'city_id',
        'source',
        'source_id',
        'sort_order',
        'status',
        'click_count',
    ];

    /**
     * Translatable alanlar
     */
    public $translatable = [
        'title',
        'excerpt',
        'themes',
        'slug',
        'meta_title',
        'meta_description',
    ];

    /**
     * Castler
     */
    protected $casts = [
        'status' => 'boolean',
        'themes' => 'array',
        'click_count' => 'integer',
        'sort_order' => 'integer',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Relations
     */
    public function contents()
    {
        return $this->hasMany(BlogContent::class)
            ->where('status', true)
            ->orderBy('sort_order');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'source_id')
            ->where('source', 'blog')
            ->orderBy('sort_order');
    }


    public function activities()
    {
        return $this->belongsToMany(
            Activity::class,
                'activity_blog',
                'blog_id',
                'activity_id'
            );
    }
}
