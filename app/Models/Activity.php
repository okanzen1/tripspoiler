<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Activity extends Model
{
    use HasTranslations;

    protected $fillable = [
        'city_id',
        'partner_id',
        'name',
        'slug',
        'type',
        'affiliate_url',
        'partner_product_id',
        'duration',
        'audio_guide',
        'description',
        'meta_title',
        'meta_description',
        'is_popular',
        'active',
        'sort_order',
    ];

    /**
     * Translatable alanlar (slug her dilde aynı)
     */
    public $translatable = [
        'name',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_popular' => 'boolean',
        'audio_guide' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function images()
    {
        return $this->hasMany(ActivityImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function cover()
    {
        return $this->hasOne(ActivityImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class)->where('active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class)->where('approved', true)->latest();
    }

    public function blogs()
    {
        return $this->belongsToMany(Blog::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('active', true);
    }

    /**
     * Sitede görünenler: kendisi, şehri ve ülkesi aktif olanlar.
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('active', true)->whereHas('city', fn ($q) => $q->visible());
    }
}
