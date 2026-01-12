<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasTranslations;

    protected $fillable = [
        'question',
        'answer',
        'source',
        'source_id',
        'sort_order',
        'status',
    ];

    /**
     * Translatable alanlar
     */
    public $translatable = [
        'question',
        'answer',
    ];

    /**
     * Castler
     */
    protected $casts = [
        'status' => 'boolean',
        'sort_order' => 'integer',
    ];
}
