<?php

namespace App\Support\Seo\Sitemap;

use DateTimeInterface;

/**
 * One <url> entry. Only `loc` is required; the optional hints are emitted
 * when present. Priority/changefreq are ignored by major engines and are
 * intentionally not modelled.
 */
final class SitemapUrl
{
    public function __construct(
        public readonly string $loc,
        public readonly ?DateTimeInterface $lastModified = null,
    ) {}
}
