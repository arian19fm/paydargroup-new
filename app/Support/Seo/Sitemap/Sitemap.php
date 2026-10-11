<?php

namespace App\Support\Seo\Sitemap;

use App\Models\Article;
use App\Support\Seo\SeoManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * The dynamic sitemap (docs/SEO.md §10):
 *
 * - /sitemap.xml            index of the sections below, each with the
 *                           newest lastmod it contains;
 * - /sitemaps/{name}.xml    one URL set per section of config('seo.sitemaps'),
 *                           listing images too;
 * - /sitemap-news.xml       Google News sitemap: news articles of the last
 *                           two days (listed in the index while it has any).
 *
 * Each document is cached for seo.sitemap_cache_seconds and flushed as soon
 * as listed content (or its SEO settings) changes — see AppServiceProvider.
 */
class Sitemap
{
    /** Google News only reads articles published in the last 48 hours. */
    public const NEWS_HOURS = 48;

    public const NEWS_LIMIT = 1000;

    protected ?Collection $news = null;

    public function __construct(protected SitemapBuilder $builder) {}

    /** @return list<string> */
    public function sections(): array
    {
        return array_keys(config('seo.sitemaps', []));
    }

    public function has(string $section): bool
    {
        return in_array($section, $this->sections(), true);
    }

    public function indexXml(): string
    {
        return $this->remember('index', function () {
            $entries = array_map(fn (string $name) => [
                'loc' => SeoManager::absoluteUrl("sitemaps/{$name}.xml"),
                'lastmod' => $this->section($name)['lastmod'],
            ], $this->sections());

            if ($this->newsArticles()->isNotEmpty()) {
                $entries[] = ['loc' => SeoManager::absoluteUrl('sitemap-news.xml'), 'lastmod' => $this->newsArticles()->first()->published_at];
            }

            return $this->builder->index($entries);
        });
    }

    public function sectionXml(string $name): string
    {
        return $this->section($name)['xml'];
    }

    public function newsXml(): string
    {
        return $this->remember('news', fn () => $this->builder->news(
            $this->newsArticles(),
            (string) (settings('general.site_name') ?: config('app.name')),
            (string) config('seo.news_language', 'fa'),
        ));
    }

    /** Forget every cached document. */
    public static function flush(): void
    {
        $prefix = config('seo.sitemap_cache_key', 'seo.sitemap');

        foreach ([...array_keys(config('seo.sitemaps', [])), 'index', 'news'] as $key) {
            Cache::forget("{$prefix}.{$key}");
        }
    }

    /** @return array{xml: string, lastmod: ?\DateTimeInterface} */
    protected function section(string $name): array
    {
        return $this->remember($name, function () use ($name) {
            $xml = $this->builder->urlset($this->urls(config("seo.sitemaps.{$name}", [])), $newest);

            return ['xml' => $xml, 'lastmod' => $newest];
        });
    }

    /**
     * @param  list<class-string<SitemapSource>>  $sources
     * @return iterable<SitemapUrl>
     */
    protected function urls(array $sources): iterable
    {
        foreach ($sources as $class) {
            yield from app($class)->urls();
        }
    }

    /** News articles (written by the automatic news, or marked NewsArticle) from the last two days. */
    protected function newsArticles(): Collection
    {
        return $this->news ??= Article::query()->published()->indexable()
            ->where('published_at', '>=', now()->subHours(self::NEWS_HOURS))
            ->where(fn (Builder $q) => $q->whereNotNull('news_run_id')
                ->orWhereHas('seo', fn (Builder $seo) => $seo->where('schema_type', 'NewsArticle')))
            ->latest('published_at')
            ->limit(self::NEWS_LIMIT)
            ->get(['id', 'slug', 'title', 'published_at']);
    }

    protected function remember(string $key, \Closure $build): mixed
    {
        return Cache::remember(
            config('seo.sitemap_cache_key', 'seo.sitemap').'.'.$key,
            now()->addSeconds((int) config('seo.sitemap_cache_seconds', 3600)),
            $build,
        );
    }
}
