<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Bir aktivitenin ya da blog yazısının SSS'i; ikisi de boşsa genel SSS.
 */
class Faq extends Model
{
    use HasTranslations;

    protected $fillable = [
        'activity_id',
        'blog_id',
        'city_id',
        'page_key',
        'question',
        'answer',
        'sort_order',
        'active',
    ];

    public $translatable = [
        'question',
        'answer',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeGeneral(Builder $query): void
    {
        $query->whereNull('activity_id')->whereNull('blog_id')->whereNull('city_id')->whereNull('page_key');
    }
}
