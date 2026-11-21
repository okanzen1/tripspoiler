<?php

namespace App\Support;

/**
 * Yazı içi otomatik bağlantı: metinde geçen yer adlarının (ör. "Colosseum") ilk geçtiği yer o yerin sayfasına
 * bağlanır. Başlıklara, zaten bağlantı olan yerlere ve koda dokunulmaz; bir yazıda en fazla MAX bağlantı.
 * Uzun adlar önce aranır ("St. Peter's Basilica", "Peter"dan önce). Japonca / Çince / Korecede kelimeler boşlukla
 * ayrılmadığı için kelime sınırı aranmaz.
 */
class AutoLinks
{
    public const MAX = 10;

    private const NO_BOUNDARY_LOCALES = ['ja', 'zh', 'ko'];

    /**
     * @param  array<string, string>  $targets  ad → adres
     */
    public static function apply(string $html, array $targets, string $locale): string
    {
        $targets = array_filter($targets, fn ($url, $name) => mb_strlen(trim($name)) >= 3 && $url, ARRAY_FILTER_USE_BOTH);
        if (trim($html) === '' || !$targets) {
            return $html;
        }
        uksort($targets, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="autolinks-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $root = $dom->getElementById('autolinks-root');
        if (!$root) {
            return $html;
        }

        // yazıda zaten bağlantısı olan adresler tekrar bağlanmaz
        $existing = [];
        foreach ($root->getElementsByTagName('a') as $a) {
            $existing[rtrim((string) $a->getAttribute('href'), '/')] = true;
        }

        $boundary = !in_array($locale, self::NO_BOUNDARY_LOCALES, true);
        $xpath = new \DOMXPath($dom);
        $added = 0;

        foreach ($targets as $name => $url) {
            if ($added >= self::MAX || isset($existing[rtrim($url, '/')])) {
                continue;
            }
            $pattern = '/' . ($boundary ? '(?<![\p{L}\p{N}])' : '') . preg_quote(trim($name), '/') . ($boundary ? '(?![\p{L}\p{N}])' : '') . '/iu';
            $nodes = $xpath->query('.//text()[not(ancestor::a) and not(ancestor::h1) and not(ancestor::h2) and not(ancestor::h3) and not(ancestor::h4) and not(ancestor::h5) and not(ancestor::h6) and not(ancestor::code) and not(ancestor::script) and not(ancestor::style) and not(ancestor::button)]', $root);

            foreach ($nodes as $node) {
                if (!preg_match($pattern, $node->nodeValue, $m, PREG_OFFSET_CAPTURE)) {
                    continue;
                }
                [$matched, $byteOffset] = $m[0];
                $text = $node->nodeValue;
                $before = substr($text, 0, $byteOffset);
                $after = substr($text, $byteOffset + strlen($matched));

                $link = $dom->createElement('a');
                $link->setAttribute('href', $url);
                $link->setAttribute('data-autolink', '');
                $link->appendChild($dom->createTextNode($matched));

                $parent = $node->parentNode;
                if ($before !== '') {
                    $parent->insertBefore($dom->createTextNode($before), $node);
                }
                $parent->insertBefore($link, $node);
                if ($after !== '') {
                    $parent->insertBefore($dom->createTextNode($after), $node);
                }
                $parent->removeChild($node);

                $existing[rtrim($url, '/')] = true;
                $added++;
                break;
            }
        }

        if (!$added) {
            return $html;
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }
}
