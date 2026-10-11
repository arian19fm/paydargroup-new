<?php

namespace App\Support\Seo\Sitemap;

use DateTimeInterface;

/**
 * One <url> entry. Only `loc` is required; `lastmod` and image URLs (the
 * image sitemap extension) are emitted when present. Priority/changefreq
 * are ignored by major engines and are intentionally not modelled.
 */
final class SitemapUrl
{
    /** @param  list<string>  $images  absolute image URLs shown on the page */
    public function __construct(
        public readonly string $loc,
        public readonly ?DateTimeInterface $lastModified = null,
        public readonly array $images = [],
    ) {}
}
