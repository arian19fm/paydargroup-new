<?php

namespace App\Support\Content;

/**
 * Renders plain-text content as safe HTML paragraphs. Until a rich editor
 * and an HTML sanitisation policy are chosen (docs/CMS.md), stored content
 * is treated as text: everything is escaped, blank lines become
 * paragraphs and single newlines become <br>.
 */
class PlainText
{
    public static function toHtml(?string $text): string
    {
        if ($text === null || trim($text) === '') {
            return '';
        }

        $paragraphs = preg_split('/\R{2,}/u', trim($text));

        return implode("\n", array_map(
            fn (string $p) => '<p>'.nl2br(e($p), false).'</p>',
            $paragraphs
        ));
    }
}
