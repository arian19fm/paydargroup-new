<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Article;

/** Published, indexable articles, with their cover image. */
class ArticlesSource implements SitemapSource
{
    public function urls(): iterable
    {
        $query = Article::query()->published()->indexable()->with('featuredImage')->orderBy('id')
            ->select(['id', 'slug', 'featured_image_id', 'updated_at']);

        foreach ($query->lazy(500) as $article) {
            yield new SitemapUrl(
                $article->publicUrl(),
                $article->updated_at,
                $article->featuredImage?->isImage() ? [$article->featuredImage->absoluteUrl()] : [],
            );
        }
    }
}
