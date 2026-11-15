<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = [
        'country_id',
        'geoname_id',
        'name',
        'slug',
        'population',
        'latitude',
        'longitude',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    /** Bu şehirde yayınlanan yazılar (blog_city; genel yazılar hariç, onlar Blog::forCity ile eklenir) */
    public function blogs()
    {
        return $this->belongsToMany(Blog::class, 'blog_city');
    }

    /**
     * Sitede görünen şehirler: kendisi ve ülkesi aktif olanlar.
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('active', true)->whereHas('country', fn ($q) => $q->where('active', true));
    }
}
