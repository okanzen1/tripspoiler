<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Blog abonesi. E-posta şifreli tutulur (panelle aynı APP_KEY); tekrar kontrolü email_hash ile.
 */
class BlogSubscriber extends Model
{
    protected $fillable = ['email', 'source_path', 'unsubscribed_at'];

    protected $casts = [
        'email' => 'encrypted',
        'unsubscribed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(fn (self $subscriber) => $subscriber->email_hash = self::hash($subscriber->email));
    }

    public static function hash(string $email): string
    {
        return (string) \App\Support\Privacy::hash($email);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('unsubscribed_at');
    }
}
