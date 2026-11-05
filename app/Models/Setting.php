<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Panelin anahtar/değer ayarları (ör. hero_image). Site sadece okur.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Birden çok ayarı tek sorguda okur.
     *
     * @param  list<string>  $keys
     * @return array<string, ?string>
     */
    public static function many(array $keys): array
    {
        $values = static::whereIn('key', $keys)->pluck('value', 'key');

        return collect($keys)->mapWithKeys(fn ($key) => [$key => $values[$key] ?? null])->all();
    }
}
