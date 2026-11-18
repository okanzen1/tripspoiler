<?php

namespace App\Support;

/**
 * Yazı içi tablolar telefonda sayfayı taşırmasın: her tablo yatay kaydırılan bir kutuya alınır
 * (stil: resources/css/rich-text.css .table-scroll).
 */
class RichTables
{
    public static function wrap(string $html): string
    {
        if (!str_contains($html, '<table')) {
            return $html;
        }

        return preg_replace(['~<table\b~i', '~</table>~i'], ['<div class="table-scroll" role="region" tabindex="0"><table', '</table></div>'], $html) ?? $html;
    }
}
