<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Partner (affiliate) bağlantısına tıklama (panel > Analitik). Tablo admin projesinin migration'ı ile kurulur. */
class AffiliateClick extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['id'];
}
