<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use App\Models\Blog;

class Activity extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'city_id',
        'affiliate_id',
        'affiliate_link',
        'sort_order',
        'most_popular',
        'status',
        'duration',
        'audio_guide',
    ];

    /**
     * Translatable alanlar
     */
    public $translatable = [
        'name',
        'description',
        'slug',
        'meta_title',
        'meta_description',
        'duration',
    ];

    /**
     * Castler
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
        'most_popular' => 'boolean',
        'audio_guide' => 'boolean',
    ];

    public function images()
    {
        return $this->hasMany(Image::class, 'source_id')
            ->where('source', 'activity')
            ->orderBy('sort_order');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function blogs()
    {
        return $this->belongsToMany(
            Blog::class,
                'activity_blog',
                'activity_id',
                'blog_id'
            );
    }
}
