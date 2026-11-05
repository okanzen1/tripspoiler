<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Partner (panel > Yönetim > Partnerler). "show_on_site" işaretli ve logosu olanlar footer'ın üstündeki şeritte döner.
 */
class Partner extends Model
{
    protected $casts = ['active' => 'boolean', 'show_on_site' => 'boolean'];

    public function logoUrl(): ?string
    {
        return ActivityImage::mediaUrl($this->logo_path);
    }
}
