<?php

namespace App\Support;

use DOMDocument;
use Illuminate\Support\Str;

/**
 * Blog yazısını ana başlıklarından (h2) bölümlere ayırır: şehir rehberindeki gibi her başlık ayrı bölüm,
 * üstte yapışkan sekmeler, telefonda bölüm kılavuzu. İlk h2'den önceki kısım giriş olur.
 *
 * @return array{intro: string, intro_title: ?string, sections: list<array{id: string, title: string, html: string, minutes: int}>}
 */
class ArticleSections
{
    /**
     * Panelde başlığa çevrilmeden yapıştırılan "## Başlık" / "### Alt başlık" satırları başlık olarak gösterilir,
     * "**kalın**" kalın yazılır (panel kaydederken de aynısını yapar: HtmlSanitizer::markdownHeadings).
     */
    public static function markdownHeadings(string $html): string
    {
        $html = preg_replace_callback('#<p>\s*(?:<(?:strong|b)>)?\s*(\#{1,4})\s+(.*?)\s*(?:</(?:strong|b)>)?\s*</p>#is', function ($m) {
            $level = min(4, max(2, strlen($m[1])));
            $text = trim(preg_replace('#</?(?:strong|b)>#i', '', $m[2]));

            return $text === '' ? $m[0] : "<h{$level}>{$text}</h{$level}>";
        }, $html) ?? $html;

        return preg_replace('#\*\*(\S(?:.*?\S)?)\*\*#s', '<strong>$1</strong>', $html) ?? $html;
    }

    public static function split(?string $html): array
    {
        $html = self::markdownHeadings(trim((string) $html));
        if ($html === '' || !preg_match('/<h2[\s>]/i', $html)) {
            return ['intro' => $html, 'intro_title' => null, 'sections' => []]; // ana başlık (h2) yoksa yazı tek parça
        }

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><div id="root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        $root = $doc->getElementById('root');

        $intro = '';
        $sections = [];
        $used = [];
        foreach (iterator_to_array($root->childNodes) as $node) {
            if ($node->nodeName === 'h2' && trim($node->textContent) !== '') {
                $title = trim(preg_replace('/\s+/u', ' ', $node->textContent));
                $base = Str::slug(Str::limit($title, 60, ''), '-', 'en') ?: 'section';
                $id = $base;
                for ($n = 2; isset($used[$id]); $n++) {
                    $id = $base . '-' . $n;
                }
                $used[$id] = true;
                $sections[] = ['id' => $id, 'title' => $title, 'html' => ''];
                continue;
            }
            $part = $doc->saveHTML($node);
            if ($sections) {
                $sections[array_key_last($sections)]['html'] .= $part;
            } else {
                $intro .= $part;
            }
        }

        foreach ($sections as &$section) {
            $section['minutes'] = max(1, (int) round(str_word_count(strip_tags($section['html'])) / 200));
        }

        // girişin başındaki başlık (h1/h3/h4) solda girişin başlığı olur, yazıdan çıkarılır
        $introTitle = null;
        if (preg_match('/^\s*<(h[1346])[^>]*>(.*?)<\/\1>/is', $intro, $m) && trim(strip_tags($m[2])) !== '') {
            $introTitle = trim(html_entity_decode(strip_tags($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $intro = substr($intro, strlen($m[0]));
        }
        $hasIntro = trim(strip_tags($intro)) !== '' || str_contains($intro, '<img');

        return ['intro' => $hasIntro ? $intro : '', 'intro_title' => $hasIntro ? $introTitle : null, 'sections' => $sections];
    }
}
