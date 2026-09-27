<?php

namespace App\Support\Content;

/**
 * Renders an article's plain-text body (Figma 313:13 / 315:1483): a line
 * starting with `#` becomes a section heading with an anchor id (these
 * also form the sidebar's table of contents), lines starting with `-`,
 * `*` or `•` become bullet items, blank lines separate paragraphs. All
 * text is escaped; no HTML is accepted.
 */
class ArticleBody
{
    /**
     * @return array{html: string, toc: list<array{id: string, text: string}>}
     */
    public static function render(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return ['html' => '', 'toc' => []];
        }

        $html = [];
        $toc = [];
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
                $heading = trim($m[1]);
                $id = 'section-'.(count($toc) + 1);
                $toc[] = ['id' => $id, 'text' => $heading];
                $html[] = '<h2 id="'.$id.'">'.e($heading).'</h2>';
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

        return ['html' => implode("\n", $html), 'toc' => $toc];
    }
}
