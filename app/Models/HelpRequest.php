<?php

namespace App\Models;

use App\Support\Privacy;
use Illuminate\Database\Eloquent\Model;

/**
 * "Plan your trip" yardım talebi. Ad, e-posta ve bütün cevaplar şifreli (site ve panel aynı APP_KEY);
 * email_hash: aynı kişinin talepleri şifreyi açmadan eşleşir. Panel okur ve yanıtlar (tripspoiler-admin).
 */
class HelpRequest extends Model
{
    protected $fillable = ['name', 'email', 'answers', 'locale', 'status', 'newsletter'];

    protected $casts = [
        'name' => 'encrypted',
        'email' => 'encrypted',
        'answers' => 'encrypted:array',
        'newsletter' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $request) {
            $request->email_hash = Privacy::hash($request->email);
        });
    }
}
