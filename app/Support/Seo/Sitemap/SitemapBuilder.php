<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Article;
use DateTimeInterface;
use XMLWriter;

/**
 * Writes sitemap XML — a sitemap index, a URL set (with the image
 * extension) and a Google News sitemap — with XMLWriter (never string
 * concatenation) so URLs are always escaped. Every document links the
 * /sitemap.xsl stylesheet so it reads as a table in a browser; crawlers
 * ignore it.
 */
class SitemapBuilder
{
    private const NS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    private const NS_IMAGE = 'http://www.google.com/schemas/sitemap-image/1.1';

    private const NS_NEWS = 'http://www.google.com/schemas/sitemap-news/0.9';

    /** @param  iterable<array{loc: string, lastmod: ?DateTimeInterface}>  $sitemaps */
    public function index(iterable $sitemaps): string
    {
        $xml = $this->open('sitemapindex', ['xmlns' => self::NS]);

        foreach ($sitemaps as $sitemap) {
            $xml->startElement('sitemap');
            $xml->writeElement('loc', $sitemap['loc']);
            $this->lastmod($xml, $sitemap['lastmod']);
            $xml->endElement();
        }

        return $this->close($xml);
    }

    /**
     * @param  iterable<SitemapUrl>  $urls
     *
     * @param-out ?DateTimeInterface  $newest  the latest lastmod written
     */
    public function urlset(iterable $urls, ?DateTimeInterface &$newest = null): string
    {
        $xml = $this->open('urlset', ['xmlns' => self::NS, 'xmlns:image' => self::NS_IMAGE]);
        $newest = null;

        foreach ($urls as $url) {
            $xml->startElement('url');
            $xml->writeElement('loc', $url->loc);
            $this->lastmod($xml, $url->lastModified);

            foreach (array_unique($url->images) as $image) {
                $xml->startElement('image:image');
                $xml->writeElement('image:loc', $image);
                $xml->endElement();
            }

            $xml->endElement();

            if ($url->lastModified && (! $newest || $url->lastModified > $newest)) {
                $newest = $url->lastModified;
            }
        }

        return $this->close($xml);
    }

    /** @param  iterable<Article>  $articles */
    public function news(iterable $articles, string $publication, string $language): string
    {
        $xml = $this->open('urlset', ['xmlns' => self::NS, 'xmlns:news' => self::NS_NEWS]);

        foreach ($articles as $article) {
            $xml->startElement('url');
            $xml->writeElement('loc', $article->publicUrl());
            $xml->startElement('news:news');
            $xml->startElement('news:publication');
            $xml->writeElement('news:name', $publication);
            $xml->writeElement('news:language', $language);
            $xml->endElement();
            $xml->writeElement('news:publication_date', $article->published_at->format(DateTimeInterface::ATOM));
            $xml->writeElement('news:title', $article->title);
            $xml->endElement();
            $xml->endElement();
        }

        return $this->close($xml);
    }

    /** @param  array<string, string>  $attributes */
    private function open(string $root, array $attributes): XMLWriter
    {
        $xml = new XMLWriter;
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->writePi('xml-stylesheet', 'type="text/xsl" href="/sitemap.xsl"');
        $xml->startElement($root);

        foreach ($attributes as $name => $value) {
            $xml->writeAttribute($name, $value);
        }

        return $xml;
    }

    private function close(XMLWriter $xml): string
    {
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function lastmod(XMLWriter $xml, ?DateTimeInterface $date): void
    {
        if ($date) {
            $xml->writeElement('lastmod', $date->format(DateTimeInterface::ATOM));
        }
    }
}
