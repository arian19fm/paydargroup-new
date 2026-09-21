<?php

namespace App\Support\Seo\Sitemap;

/**
 * A provider of sitemap URLs. Register implementations in
 * config/seo.php → sitemap_sources. Sources must only yield URLs that are
 * publicly reachable, canonical and indexable (published, not noindex).
 *
 * Planned sources (see docs/SEO.md):
 *  - StaticPagesSource   fixed routes such as the home page (Phase 2)
 *  - managed pages       CMS pages, when the model exists
 *  - articles / news     published articles, lastmod = updated_at
 *  - company / projects  future company or project pages
 */
interface SitemapSource
{
    /** @return iterable<SitemapUrl> */
    public function urls(): iterable;
}
