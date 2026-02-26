<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'slug',
        'meta_title',
        'meta_description',
    ];

    public function contents()
    {
        return $this->hasMany(PageContent::class);
    }

    // şehir bazlı content helper
    public function contentForCity(int $cityId)
    {
        return $this->contents()
            ->where('city_id', $cityId)
            ->where('is_active', true)
            ->first();
    }
}
