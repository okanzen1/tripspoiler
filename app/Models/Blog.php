<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Blog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'city_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'tags',
        'cover_path',
        'meta_title',
        'meta_description',
        'active',
        'published_at',
    ];

    /**
     * Translatable alanlar (slug her dilde aynı)
     */
    public $translatable = [
        'title',
        'excerpt',
        'content',
        'tags',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'active' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function activities()
    {
        return $this->belongsToMany(Activity::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class)->where('active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Yayında olanlar: aktif ve yayın tarihi gelmiş.
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('active', true)
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * @param  'sm'|null  $size  liste için küçük kopya
     */
    public function coverUrl(?string $size = null): ?string
    {
        return ActivityImage::mediaUrl($this->cover_path, $size);
    }

    /**
     * Okuma süresi (dakika), İngilizce metne göre.
     */
    public function readingMinutes(): int
    {
        $content = $this->getTranslation('content', 'en', false);

        return max(1, (int) round(str_word_count(strip_tags((string) $content)) / 220));
    }
}
