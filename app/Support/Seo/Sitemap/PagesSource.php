<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Page;

/** Published, indexable managed pages. */
class PagesSource implements SitemapSource
{
    public function urls(): iterable
    {
        $query = Page::query()->published()->indexable()->orderBy('id')
            ->select(['id', 'slug', 'updated_at']);

        foreach ($query->lazy(500) as $page) {
            yield new SitemapUrl($page->publicUrl(), $page->updated_at);
        }
    }
}
