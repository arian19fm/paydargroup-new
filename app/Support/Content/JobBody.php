<?php

namespace App\Support\Content;

/**
 * Renders an opening's plain-text description (Figma 350:6108): a line
 * starting with `#` becomes a section heading, lines starting with `-`,
 * `*` or `•` become bullet items, blank lines separate paragraphs. All
 * text is escaped; no HTML is accepted.
 */
class JobBody
{
    public static function toHtml(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $html = [];
        $paragraph = [];
        $list = [];

        $flushParagraph = function () use (&$html, &$paragraph) {
            if ($paragraph !== []) {
                $html[] = '<p>'.implode('<br>', array_map('e', $paragraph)).'</p>';
                $paragraph = [];
            }
        };
        $flushList = function () use (&$html, &$list) {
            if ($list !== []) {
                $html[] = '<ul>'.implode('', array_map(fn (string $i) => '<li>'.e($i).'</li>', $list)).'</ul>';
                $list = [];
            }
        };

        foreach (preg_split('/\R/u', trim($text)) as $line) {
            $line = trim($line);

            if ($line === '') {
                $flushParagraph();
                $flushList();
            } elseif (preg_match('/^#+\s*(.+)$/u', $line, $m)) {
                $flushParagraph();
                $flushList();
                $html[] = '<h3>'.e(trim($m[1])).'</h3>';
            } elseif (preg_match('/^[-*•]\s*(.+)$/u', $line, $m)) {
                $flushParagraph();
                $list[] = trim($m[1]);
            } else {
                $flushList();
                $paragraph[] = $line;
            }
        }

        $flushParagraph();
        $flushList();

        return implode("\n", $html);
    }
}
