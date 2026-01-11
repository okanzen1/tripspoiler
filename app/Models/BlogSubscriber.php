<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class BlogSubscriber extends Model
{
    protected $table = 'blog_subscribers';

    protected $fillable = [
        'email',
        'status',
        'unsubscribe_token',
    ];

    protected $attributes = [
        'status' => 1,
    ];

    /**
     * Model oluşturulurken otomatik token üret
     */
    protected static function booted()
    {
        static::creating(function ($subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(48);
            }
        });
    }

    /**
     * EMAIL SETTER
     * DB'ye kaydedilirken otomatik encrypt edilir
     */
    public function setEmailAttribute($value)
    {
        $normalized = mb_strtolower(trim($value));

        $this->attributes['email'] = Crypt::encryptString($normalized);
        $this->attributes['email_hash'] = hash('sha256', $normalized);
    }


    /**
     * EMAIL GETTER
     * Okunurken otomatik decrypt edilir
     */
    public function getEmailAttribute($value)
    {
        return Crypt::decryptString($value);
    }

    /**
     * Sadece aktif aboneler
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
