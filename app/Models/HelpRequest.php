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
    protected $fillable = ['reference', 'name', 'email', 'answers', 'locale', 'status', 'newsletter'];

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
            $request->reference ??= self::newReference();
        });
    }

    /** Tekil talep numarası: TS- + 5 rakam (panelde de aynı biçim) */
    public static function newReference(): string
    {
        do {
            $ref = 'TS-' . random_int(10000, 99999);
        } while (static::where('reference', $ref)->exists());

        return $ref;
    }

    /** "TS-48217", "ts48217", "48217" → "TS-48217" */
    public static function normalizeReference(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value);

        return 'TS-' . $digits;
    }
}
