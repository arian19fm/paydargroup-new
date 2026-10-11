<?php

namespace App\Support\News;

use App\Enums\ContentStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Media;
use App\Models\NewsRun;
use App\Models\User;
use App\Support\Content\HtmlContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Turns writer drafts into articles: validates and sanitises them, keeps
 * only sources that came from the run's own search results, appends the
 * sources list, fills the SEO overrides (NewsArticle schema) and publishes
 * or saves as draft per the admin setting.
 */
class NewsPublisher
{
    /**
     * @param  list<string>  $searchedUrls
     * @return list<Article>
     */
    public function publish(NewsRun $run, array $drafts, array $searchedUrls, NewsSettings $settings): array
    {
        $articles = [];

        foreach ($drafts as $draft) {
            $sources = $this->verifiedSources($draft->sources, $searchedUrls);
            $body = HtmlContent::sanitize($draft->contentHtml);

            // Skip anything unusable rather than publish a broken page.
            if ($draft->title === '' || $body === '' || $sources === [] || mb_strlen(strip_tags($body)) < 300) {
                continue;
            }

            $articles[] = DB::transaction(fn () => $this->create($run, $draft, $body, $sources, $settings));
        }

        if ($articles !== []) {
            Cache::forget(config('seo.sitemap_cache_key', 'seo.sitemap.xml'));
        }

        return $articles;
    }

    /** @param  list<array{title: string, url: string}>  $sources */
    protected function create(NewsRun $run, NewsDraft $draft, string $body, array $sources, NewsSettings $settings): Article
    {
        $article = Article::create([
            'title' => Str::limit($draft->title, 250, ''),
            'slug' => $this->uniqueSlug($draft->slug ?: $draft->title),
            'excerpt' => Str::limit($draft->excerpt, 1000, '') ?: null,
            'content' => $body."\n".$this->sourcesHtml($sources),
            'featured_image_id' => $settings->coverMediaId && Media::whereKey($settings->coverMediaId)->exists() ? $settings->coverMediaId : null,
            'status' => $settings->autoPublish ? ContentStatus::Published : ContentStatus::Draft,
            'published_at' => $settings->autoPublish ? now() : null,
            'author_id' => $settings->authorId && User::whereKey($settings->authorId)->exists() ? $settings->authorId : null,
            'news_run_id' => $run->id,
            'sources' => $sources,
        ]);

        if ($settings->categoryId && ArticleCategory::whereKey($settings->categoryId)->exists()) {
            $article->categories()->sync([$settings->categoryId]);
        }

        $article->seo()->create([
            'title' => $draft->seoTitle !== '' ? Str::limit($draft->seoTitle, 70, '') : null,
            'description' => $draft->seoDescription !== '' ? Str::limit($draft->seoDescription, 160, '') : null,
            'schema_type' => 'NewsArticle',
        ]);

        return $article;
    }

    /**
     * Sources whose host appeared in the run's search results (a URL the
     * model made up is dropped). Without any recorded result URL nothing
     * can be checked, so the http(s) sources are kept as given.
     *
     * @param  list<array{title: string, url: string}>  $sources
     * @param  list<string>  $searchedUrls
     * @return list<array{title: string, url: string}>
     */
    protected function verifiedSources(array $sources, array $searchedUrls): array
    {
        $hosts = array_flip(array_filter(array_map(fn (string $u) => $this->host($u), $searchedUrls)));
        $out = [];

        foreach ($sources as $source) {
            $host = $this->host($source['url']);

            if ($host === null || ($hosts !== [] && ! isset($hosts[$host])) || isset($out[$source['url']])) {
                continue;
            }

            $out[$source['url']] = ['title' => Str::limit($source['title'] ?: $host, 200, ''), 'url' => $source['url']];
        }

        return array_values($out);
    }

    protected function host(string $url): ?string
    {
        if (! preg_match('#^https?://#i', $url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return $host === '' ? null : preg_replace('/^www\./', '', $host);
    }

    /** @param  list<array{title: string, url: string}>  $sources */
    protected function sourcesHtml(array $sources): string
    {
        $items = array_map(
            fn (array $s) => '<li><a href="'.e($s['url']).'" target="_blank" rel="nofollow noopener">'.e($s['title']).'</a></li>',
            $sources
        );

        return '<h2>'.e(__('articles.sources')).'</h2><ul>'.implode('', $items).'</ul>';
    }

    protected function uniqueSlug(string $value): string
    {
        $base = Str::limit(Str::slug($value), 80, '');
        $base = trim($base, '-') ?: 'news-'.now()->format('Ymd-His');
        $slug = $base;

        for ($i = 2; Article::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
