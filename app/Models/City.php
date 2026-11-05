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

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * Sitede görünen şehirler: kendisi ve ülkesi aktif olanlar.
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('active', true)->whereHas('country', fn ($q) => $q->where('active', true));
    }
}
