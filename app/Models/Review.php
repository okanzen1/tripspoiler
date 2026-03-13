<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'name',
        'email',
        'email_hash',
        'source',
        'source_id',
        'rating',
        'comment',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
        'name' => 'encrypted',
        'email' => 'encrypted',
    ];
    
    public function getNameAttribute($value)
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
