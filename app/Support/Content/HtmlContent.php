<?php

namespace App\Support\Content;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Rich content (article, business, page, job and FAQ bodies) is stored as
 * HTML written in the admin editor — visually or as raw HTML — and is
 * sanitised against an allow-list both when it is saved and when it is
 * rendered (docs/CMS.md §3).
 *
 * Rendering also makes the markup fit its place on the page: headings are
 * shifted so the body never competes with the page's own H1/H2 (the
 * hierarchy is kept), render() gives top-level headings anchor ids for a
 * table of contents, images load lazily, new-tab links get rel="noopener" and tables
 * scroll inside a wrapper. Text without any tag (legacy plain text, or a
 * field typed with JavaScript off) is rendered as paragraphs.
 */
class HtmlContent
{
    /** Longest accepted input in bytes; form requests validate below this. */
    public const MAX_BYTES = 1_000_000;

    /** Attributes allowed on every element. */
    private const GLOBAL_ATTRIBUTES = ['id', 'class', 'dir', 'lang', 'title'];

    /** @var array<string, list<string>> element => extra attributes */
    private const ELEMENTS = [
        'p' => [], 'br' => [], 'hr' => [], 'div' => [], 'span' => [],
        'h1' => [], 'h2' => [], 'h3' => [], 'h4' => [], 'h5' => [], 'h6' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [], 'del' => [], 'ins' => [],
        'sub' => [], 'sup' => [], 'mark' => [], 'small' => [], 'abbr' => [], 'cite' => [], 'dfn' => [],
        'q' => ['cite'], 'blockquote' => ['cite'], 'code' => [], 'pre' => [], 'kbd' => [], 'samp' => [],
        'time' => ['datetime'], 'bdi' => [], 'bdo' => [], 'wbr' => [],
        'ul' => [], 'ol' => ['start', 'reversed', 'type'], 'li' => ['value'], 'dl' => [], 'dt' => [], 'dd' => [],
        'a' => ['href', 'target', 'rel', 'hreflang'],
        'img' => ['src', 'alt', 'width', 'height', 'loading', 'decoding'],
        'figure' => [], 'figcaption' => [], 'picture' => [], 'source' => ['srcset', 'media', 'type', 'width', 'height'],
        'table' => [], 'caption' => [], 'colgroup' => ['span'], 'col' => ['span'],
        'thead' => [], 'tbody' => [], 'tfoot' => [], 'tr' => [],
        'th' => ['colspan', 'rowspan', 'scope', 'headers'], 'td' => ['colspan', 'rowspan', 'headers'],
        'section' => [], 'aside' => [], 'details' => ['open'], 'summary' => [], 'address' => [],
    ];

    /** Wrappers that make no sense inside a body: the tag goes, the text stays. */
    private const UNWRAPPED = ['article', 'header', 'footer', 'main', 'nav', 'font', 'center', 'body', 'html'];

    private static ?HtmlSanitizer $sanitizer = null;

    /**
     * Clean HTML for storage. Returns '' when nothing visible is left, so
     * "required" rules see an empty editor (`<p></p>`) as empty.
     */
    public static function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $clean = trim(self::sanitizer()->sanitize($html));

        $visible = trim(html_entity_decode(strip_tags($clean), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\u{A0}");

        return $visible === '' && ! preg_match('/<(img|hr|table|picture)\b/i', $clean) ? '' : $clean;
    }

    public static function isHtml(?string $text): bool
    {
        return $text !== null && preg_match('/<\/?[a-z][a-z0-9]*(\s[^>]*)?\/?>/i', $text) === 1;
    }

    /**
     * Sanitised, page-ready HTML, without generated anchors (safe for bodies
     * repeated on one page, such as FAQ answers).
     *
     * @param  int  $topHeading  the highest heading level the body may use (2 under a page H1, 3 under a section H2…)
     */
    public static function toHtml(?string $html, int $topHeading = 2): string
    {
        return self::render($html, $topHeading, anchors: false)['html'];
    }

    /**
     * Page-ready HTML plus a table of contents of its top-level headings.
     *
     * @return array{html: string, toc: list<array{id: string, text: string}>}
     */
    public static function render(?string $html, int $topHeading = 2, bool $anchors = true): array
    {
        if ($html === null || trim($html) === '') {
            return ['html' => '', 'toc' => []];
        }

        if (! self::isHtml($html)) {
            $html = PlainText::toHtml($html);
        }

        $clean = self::sanitize($html);

        if ($clean === '') {
            return ['html' => '', 'toc' => []];
        }

        $doc = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8"><html><body><div id="pg-root">'.$clean.'</div></body></html>', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($doc);
        /** @var DOMElement $root */
        $root = $xpath->query('//div[@id="pg-root"]')->item(0);

        $toc = self::headings($doc, $xpath, $root, max(1, min(6, $topHeading)), $anchors);
        self::links($xpath, $root);
        self::images($xpath, $root);
        self::tables($doc, $xpath, $root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return ['html' => trim($out), 'toc' => $toc];
    }

    /**
     * Shift headings down so the shallowest one sits at $top (keeping the
     * relative hierarchy) and give the top-level ones unique anchor ids.
     *
     * @return list<array{id: string, text: string}>
     */
    private static function headings(DOMDocument $doc, DOMXPath $xpath, DOMElement $root, int $top, bool $anchors): array
    {
        $headings = iterator_to_array($xpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6', $root));

        if ($headings === []) {
            return [];
        }

        $shallowest = min(array_map(fn (DOMElement $h) => (int) $h->nodeName[1], $headings));
        $shift = max(0, $top - $shallowest);

        $used = [];
        foreach (iterator_to_array($xpath->query('.//*[@id]', $root)) as $el) {
            $used[$el->getAttribute('id')] = true;
        }

        $toc = [];
        foreach ($headings as $heading) {
            $level = min(6, (int) $heading->nodeName[1] + $shift);

            if ($shift > 0) {
                $heading = self::rename($doc, $heading, 'h'.$level);
            }

            if (! $anchors || $level !== $top) {
                continue;
            }

            $text = trim(preg_replace('/\s+/u', ' ', $heading->textContent));

            if ($text === '') {
                continue;
            }

            $id = $heading->getAttribute('id');
            if ($id === '') {
                $n = count($toc) + 1;
                do {
                    $id = 'section-'.$n++;
                } while (isset($used[$id]));
                $used[$id] = true;
                $heading->setAttribute('id', $id);
            }

            $toc[] = ['id' => $id, 'text' => $text];
        }

        return $toc;
    }

    private static function links(DOMXPath $xpath, DOMElement $root): void
    {
        foreach ($xpath->query('.//a[@target="_blank"]', $root) as $a) {
            $rel = preg_split('/\s+/', strtolower(trim($a->getAttribute('rel'))), -1, PREG_SPLIT_NO_EMPTY);
            $a->setAttribute('rel', implode(' ', array_unique([...$rel, 'noopener'])));
        }
    }

    private static function images(DOMXPath $xpath, DOMElement $root): void
    {
        foreach ($xpath->query('.//img', $root) as $img) {
            if (! $img->hasAttribute('alt')) {
                $img->setAttribute('alt', '');
            }
            if (! $img->hasAttribute('loading')) {
                $img->setAttribute('loading', 'lazy');
            }
            if (! $img->hasAttribute('decoding')) {
                $img->setAttribute('decoding', 'async');
            }
        }
    }

    private static function tables(DOMDocument $doc, DOMXPath $xpath, DOMElement $root): void
    {
        foreach (iterator_to_array($xpath->query('.//table', $root)) as $table) {
            $wrapper = $doc->createElement('div');
            $wrapper->setAttribute('class', 'pg-content__table');
            $table->parentNode->replaceChild($wrapper, $table);
            $wrapper->appendChild($table);
        }
    }

    private static function rename(DOMDocument $doc, DOMElement $el, string $name): DOMElement
    {
        $new = $doc->createElement($name);

        foreach (iterator_to_array($el->attributes) as $attr) {
            $new->setAttribute($attr->nodeName, $attr->nodeValue);
        }
        while ($el->firstChild) {
            $new->appendChild($el->firstChild);
        }
        $el->parentNode->replaceChild($new, $el);

        return $new;
    }

    private static function sanitizer(): HtmlSanitizer
    {
        if (self::$sanitizer) {
            return self::$sanitizer;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            ->withMaxInputLength(self::MAX_BYTES);

        foreach (self::ELEMENTS as $element => $attributes) {
            $config = $config->allowElement($element, [...self::GLOBAL_ATTRIBUTES, ...$attributes]);
        }

        foreach (self::UNWRAPPED as $element) {
            $config = $config->blockElement($element);
        }

        return self::$sanitizer = new HtmlSanitizer($config);
    }
}
