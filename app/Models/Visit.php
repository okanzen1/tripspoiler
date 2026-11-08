<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Sayfa görüntüleme (panel > Analitik). Tablo admin projesinin migration'ı ile kurulur. */
class Visit extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
