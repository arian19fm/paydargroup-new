<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Business;

/** Published, indexable business pages, with their hero image. */
class BusinessesSource implements SitemapSource
{
    public function urls(): iterable
    {
        $query = Business::query()->published()->indexable()->ordered()->with('image')
            ->select(['id', 'slug', 'image_media_id', 'updated_at', 'sort_order']);

        foreach ($query->lazy(500) as $business) {
            yield new SitemapUrl(
                $business->publicUrl(),
                $business->updated_at,
                $business->image?->isImage() ? [$business->image->absoluteUrl()] : [],
            );
        }
    }
}
