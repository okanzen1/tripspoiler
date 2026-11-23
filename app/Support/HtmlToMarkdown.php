<?php

namespace App\Support;

/**
 * Sayfanın ana içeriğini (<main>) sade Markdown'a çevirir: yapay zekâ ajanları "Accept: text/markdown" isteyince
 * HTML yerine bu döner (AgentFriendly). Paketsiz, basit bir çevirici: başlıklar, paragraflar, listeler, bağlantılar,
 * kalın / italik, tablolar. Menüler, düğmeler, formlar, ikonlar ve gizli öğeler atlanır.
 */
class HtmlToMarkdown
{
    private const SKIP = ['script', 'style', 'svg', 'noscript', 'template', 'button', 'form', 'input', 'select', 'textarea', 'nav', 'iframe', 'video', 'audio', 'dialog'];

    public static function page(string $html, string $url): string
    {
        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $title = trim((string) ($dom->getElementsByTagName('title')->item(0)?->textContent ?? ''));
        $description = '';
        foreach ($dom->getElementsByTagName('meta') as $meta) {
            if ($meta->getAttribute('name') === 'description') {
                $description = trim($meta->getAttribute('content'));
            }
        }
        $main = $dom->getElementsByTagName('main')->item(0) ?? $dom->getElementsByTagName('body')->item(0);

        $body = $main ? self::block($main) : '';
        // satırları kırp, boş satırları teke indir
        $body = implode("\n", array_map(fn ($line) => trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', $line)), explode("\n", $body)));
        $body = preg_replace("/\n{3,}/", "\n\n", trim($body));

        return '# ' . html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\n\n"
            . ($description !== '' ? '> ' . html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8') . "\n\n" : '')
            . 'Source: ' . $url . "\n\n"
            . $body . "\n";
    }

    private static function hidden(\DOMElement $el): bool
    {
        return $el->hasAttribute('hidden') || $el->hasAttribute('x-cloak') || $el->getAttribute('aria-hidden') === 'true'
            || preg_match('/(^|\s)(sr-only|hidden)(\s|$)/', $el->getAttribute('class'));
    }

    private static function block(\DOMNode $node): string
    {
        $out = '';
        foreach ($node->childNodes as $child) {
            $out .= self::node($child);
        }

        return $out;
    }

    private static function node(\DOMNode $node): string
    {
        if ($node instanceof \DOMText) {
            return preg_replace('/\s+/u', ' ', $node->nodeValue);
        }
        if (!$node instanceof \DOMElement) {
            return '';
        }
        $tag = strtolower($node->tagName);
        if (in_array($tag, self::SKIP, true) || self::hidden($node)) {
            return '';
        }

        $inner = fn () => trim(self::block($node));

        return match ($tag) {
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => ($text = $inner()) !== '' ? "\n\n" . str_repeat('#', min(6, (int) $tag[1] + 1)) . ' ' . $text . "\n\n" : '',
            'p', 'blockquote' => ($text = $inner()) !== '' ? "\n\n" . ($tag === 'blockquote' ? '> ' : '') . $text . "\n\n" : '',
            'br' => "\n",
            'strong', 'b' => ($text = $inner()) !== '' ? '**' . $text . '**' : '',
            'em', 'i' => ($text = $inner()) !== '' ? '*' . $text . '*' : '',
            'a' => self::link($node, $inner()),
            'img' => '',
            'ul', 'ol' => "\n\n" . self::listItems($node, $tag === 'ol') . "\n\n",
            'table' => "\n\n" . self::table($node) . "\n\n",
            'li' => '- ' . $inner() . "\n",
            'div', 'section', 'article', 'header', 'footer', 'aside', 'figure', 'details', 'summary' => "\n" . self::block($node) . "\n",
            default => self::block($node),
        };
    }

    private static function link(\DOMElement $a, string $text): string
    {
        $href = trim($a->getAttribute('href'));
        if ($text === '') {
            return '';
        }
        if ($href === '' || str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) {
            return $text;
        }

        return '[' . $text . '](' . $href . ')';
    }

    private static function listItems(\DOMElement $list, bool $ordered): string
    {
        $lines = [];
        $n = 1;
        foreach ($list->childNodes as $li) {
            if (!$li instanceof \DOMElement || strtolower($li->tagName) !== 'li' || self::hidden($li)) {
                continue;
            }
            $text = trim(preg_replace("/\n{2,}/", "\n", self::block($li)));
            if ($text !== '') {
                $lines[] = ($ordered ? ($n++) . '. ' : '- ') . str_replace("\n", "\n  ", $text);
            }
        }

        return implode("\n", $lines);
    }

    private static function table(\DOMElement $table): string
    {
        $rows = [];
        foreach ($table->getElementsByTagName('tr') as $tr) {
            $cells = [];
            foreach ($tr->childNodes as $cell) {
                if ($cell instanceof \DOMElement && in_array(strtolower($cell->tagName), ['td', 'th'], true)) {
                    $cells[] = str_replace('|', '\|', trim(preg_replace('/\s+/u', ' ', self::block($cell))));
                }
            }
            if ($cells) {
                $rows[] = '| ' . implode(' | ', $cells) . ' |';
            }
        }
        if (count($rows) > 1) {
            array_splice($rows, 1, 0, ['|' . str_repeat(' --- |', substr_count($rows[0], ' | ') + 1)]);
        }

        return implode("\n", $rows);
    }
}
