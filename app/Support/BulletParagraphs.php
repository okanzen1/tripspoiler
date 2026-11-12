<?php

namespace App\Support;

/**
 * Editörde elle "•" / "-" / "*" ile yazılmış satırlar (<p>• ...</p>) art arda geliyorsa gerçek listeye (<ul><li>) çevrilir;
 * böylece sitede kırmızı noktalı, sıkı bir liste olarak görünür.
 */
class BulletParagraphs
{
    private const MARK = '(?:•|·|●|▪|–|-|\*)';

    public static function toList(string $html): string
    {
        $item = '<p>\s*' . self::MARK . '\s*(.*?)</p>';

        return preg_replace_callback('~(?:' . $item . '\s*){2,}~su', function (array $m) {
            preg_match_all('~<p>\s*' . self::MARK . '\s*(.*?)</p>~su', $m[0], $items);

            return '<ul>' . implode('', array_map(fn ($text) => '<li>' . $text . '</li>', $items[1])) . '</ul>';
        }, $html) ?? $html;
    }
}
