<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Panelden yüklenen aktivite görseli. Dosyalar "media" diskinde, küçük kopyası "-sm" ekli.
 */
class ActivityImage extends Model
{
    protected $fillable = ['activity_id', 'path', 'width', 'height', 'sort_order'];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * @param  'sm'|null  $size  liste kartları için küçük kopya
     */
    public function url(?string $size = null): ?string
    {
        return static::mediaUrl($this->path, $size);
    }

    public static function mediaUrl(?string $path, ?string $size = null): ?string
    {
        if (!$path) {
            return null;
        }

        if ($size) {
            $path = preg_replace('/\.webp$/', "-{$size}.webp", $path);
        }

        return Storage::disk('media')->url($path);
    }
}
