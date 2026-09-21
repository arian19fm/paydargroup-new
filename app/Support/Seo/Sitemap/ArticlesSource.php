<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Article;

/** Published, indexable articles. */
class ArticlesSource implements SitemapSource
{
    public function urls(): iterable
    {
        $query = Article::query()->published()->indexable()->orderBy('id')
            ->select(['id', 'slug', 'updated_at']);

        foreach ($query->lazy(500) as $article) {
            yield new SitemapUrl($article->publicUrl(), $article->updated_at);
        }
    }
}
