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

    /**
     * Sitede gösterilen ad, gizlilik için maskeli: adın ilk harfi + sabit sayıda yıldız (uzunluğu belli olmasın),
     * soyadın baş harfi ("Lukas Schneider" → "L**** S.", "Ahmet" → "A****"). Tam ad yalnızca panelde görünür.
     */
    public function getDisplayNameAttribute(): string
    {
        $parts = preg_split('/\s+/u', trim((string) $this->name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$parts) {
            return '';
        }
        $masked = mb_strtoupper(mb_substr($parts[0], 0, 1)) . '****';

        return count($parts) > 1 ? $masked . ' ' . mb_strtoupper(mb_substr(end($parts), 0, 1)) . '.' : $masked;
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
