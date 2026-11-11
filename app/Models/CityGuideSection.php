<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * Bir şehrin bir rehber alanına yazdığı içerik (başlık, editör HTML'i, kapak resmi).
 */
class CityGuideSection extends Model
{
    use HasTranslations;

    public $translatable = ['title', 'content'];

    protected $casts = ['active' => 'boolean'];

    public function topic()
    {
        return $this->belongsTo(CityGuideTopic::class, 'topic_id');
    }

    /** Sitede gösterilecek kadar dolu mu (başlık + metin) */
    public function isWritten(): bool
    {
        return filled($this->title) && filled(trim(strip_tags((string) $this->content, '<img>')));
    }

    /**
     * Sitede basılacak yazı: en baştaki başlık (h1–h3) atılır, çünkü bölüm başlığı zaten
     * solda gösteriliyor; aksi halde iki büyük başlık alt alta çıkıyor.
     */
    public function bodyHtml(): string
    {
        $html = (string) $this->content;
        $stripped = preg_replace(
            '~^\s*(?:<p>(?:\s|&nbsp;|<br\s*/?>)*</p>\s*)*<h[1-3]\b[^>]*>.*?</h[1-3]>~isu',
            '',
            $html,
            1
        );

        return $stripped ?? $html;
    }

    /** Tahmini okuma süresi (dakika) */
    public function readingMinutes(): int
    {
        return max(1, (int) ceil(preg_match_all('/\S+/u', strip_tags((string) $this->content)) / 200));
    }

    public function imageUrl(?string $size = null): ?string
    {
        return ActivityImage::mediaUrl($this->image_path, $size);
    }
}
