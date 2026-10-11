<?php

namespace App\Support\Seo\Sitemap;

use App\Models\ArticleCategory;
use App\Support\Seo\SeoManager;
use Illuminate\Support\Carbon;

/**
 * Article category listings (/articles?category=…, self-canonical), first
 * page only, for active categories that have at least one published,
 * indexable article; lastmod = that category's newest article.
 */
class ArticleCategoriesSource implements SitemapSource
{
    public function urls(): iterable
    {
        $categories = ArticleCategory::query()->active()->ordered()->select(['id', 'slug'])
            ->withMax(['articles as last_article_at' => fn ($q) => $q->published()->indexable()], 'updated_at')
            ->get();

        foreach ($categories as $category) {
            if ($category->last_article_at) {
                yield new SitemapUrl(
                    SeoManager::absoluteUrl(parse_url(route('articles.index'), PHP_URL_PATH) ?? '').'?'.http_build_query(['category' => $category->slug]),
                    Carbon::parse($category->last_article_at),
                );
            }
        }
    }
}
