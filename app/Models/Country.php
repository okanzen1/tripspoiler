<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'code',
        'name',
        'slug',
        'currency',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function cities()
    {
        return $this->hasMany(City::class);
    }
}
