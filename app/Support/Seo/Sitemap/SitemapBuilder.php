<?php

namespace App\Support\Seo\Sitemap;

use DateTimeInterface;
use XMLWriter;

/**
 * Renders the sitemap XML from all configured sources. Output is built with
 * XMLWriter (never string concatenation) so URLs are always escaped.
 *
 * When the URL count grows past ~10k, split into a sitemap index with one
 * sitemap per source (documented in docs/SEO.md).
 */
class SitemapBuilder
{
    /**
     * @param  list<class-string<SitemapSource>>  $sources
     */
    public function __construct(protected array $sources) {}

    public function toXml(): string
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');
        $xml->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($this->urls() as $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url->loc);

            if ($url->lastModified) {
                $xml->writeElement('lastmod', $url->lastModified->format(DateTimeInterface::ATOM));
            }

            $xml->endElement();
        }

        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    /** @return iterable<SitemapUrl> */
    public function urls(): iterable
    {
        foreach ($this->sources as $class) {
            /** @var SitemapSource $source */
            $source = app($class);

            yield from $source->urls();
        }
    }
}
