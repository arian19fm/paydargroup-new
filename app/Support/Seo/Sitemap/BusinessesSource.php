<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Business;

/** Published, indexable business pages. */
class BusinessesSource implements SitemapSource
{
    public function urls(): iterable
    {
        $query = Business::query()->published()->indexable()->ordered()
            ->select(['id', 'slug', 'updated_at', 'sort_order']);

        foreach ($query->lazy(500) as $business) {
            yield new SitemapUrl($business->publicUrl(), $business->updated_at);
        }
    }
}
