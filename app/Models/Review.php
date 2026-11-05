<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Ziyaretçi yorumu. activity_id boşsa sitenin geneli hakkında. E-posta şifreli tutulur.
 */
class Review extends Model
{
    protected $fillable = [
        'activity_id',
        'name',
        'email',
        'rating',
        'comment',
        'approved',
    ];

    protected $casts = [
        'email' => 'encrypted',
        'rating' => 'integer',
        'approved' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $review) {
            $review->email_hash = filled($review->email) ? hash('sha256', mb_strtolower(trim($review->email))) : null;
        });
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function scopeApproved(Builder $query): void
    {
        $query->where('approved', true);
    }
}
