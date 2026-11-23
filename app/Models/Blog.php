<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Blog extends Model
{
    use HasTranslations, \App\Models\Concerns\HasLocalizedSlugs;

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

    /** Yayınlandığı şehirler (blog_city). Hiç şehri yoksa yazı genel: her şehirde görünür. */
    public function cities()
    {
        return $this->belongsToMany(City::class, 'blog_city');
    }

    /**
     * Bir şehirde görünen yazılar: o şehre bağlananlar + genel yazılar. Şehir yoksa yalnızca genel yazılar.
     */
    /**
     * Bir şehrin REHBER listesi: o şehre bağlanan rehberler + hiçbir şehre bağlı olmayan rehberler. Haberler (Travel News)
     * şehir listelerine girmez (Atina listesinde GTA 6 haberi çıkıyordu).
     */
    public function scopeForCity(Builder $query, ?int $cityId): void
    {
        $query->guides()->where(fn ($q) => $q->doesntHave('cities')
            ->when($cityId, fn ($q) => $q->orWhereHas('cities', fn ($c) => $c->whereKey($cityId))));
    }

    /**
     * Tür sütunu (panel: Rehber / Haber) veritabanında var mı. Panel güncellenip migrate edilene kadar yoksa eski
     * kural: hiçbir şehre bağlı olmayan yazı haber sayılır. 10 dk önbellekte.
     */
    public static function hasKind(): bool
    {
        return once(fn () => (bool) \Illuminate\Support\Facades\Cache::remember('blogs:has-kind', 600,
            fn () => \Illuminate\Support\Facades\Schema::hasColumn('blogs', 'kind') ? 1 : 0));
    }

    /** Destination Guides: şehir, müze, aktivite ve gezi rehberleri */
    public function scopeGuides(Builder $query): void
    {
        // sütunlar tablo adıyla: şehir sayısı (City withCount) gibi birleşik sorgularda "city_id" iki tabloda da var
        self::hasKind()
            ? $query->where($query->qualifyColumn('kind'), '!=', 'news')
            : $query->where(fn ($q) => $q->whereNotNull($query->qualifyColumn('city_id'))->orWhereHas('cities'));
    }

    /** Travel News: güncel seyahat haberleri (vize, ulaşım, etkinlik, turizm) */
    public function scopeNews(Builder $query): void
    {
        self::hasKind()
            ? $query->where($query->qualifyColumn('kind'), 'news')
            : $query->whereNull($query->qualifyColumn('city_id'))->doesntHave('cities');
    }

    public function isNews(): bool
    {
        return self::hasKind() ? $this->kind === 'news' : ($this->city_id === null && !$this->cities()->exists());
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
