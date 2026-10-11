<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Media;
use App\Models\NewsRun;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotsAndSitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_disallows_everything_when_not_indexable(): void
    {
        config(['seo.indexable' => false]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee("User-agent: *\nDisallow: /\n", false)
            ->assertDontSee('Sitemap:');
    }

    public function test_robots_txt_allows_crawling_in_production_mode(): void
    {
        config(['seo.indexable' => true]);

        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee("Allow: /\n", false)
            ->assertSee('Disallow: /admin')
            ->assertSee('Sitemap: https://paydargroup.test/sitemap.xml')
            ->assertDontSee('Disallow: /build');
    }

    public function test_sitemap_index_links_one_sitemap_per_section(): void
    {
        Article::factory()->published()->create(['updated_at' => '2026-10-01 10:00:00']);

        $xml = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<?xml-stylesheet type="text/xsl" href="/sitemap.xsl"?>', false)
            ->assertSee('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', false)
            ->getContent();

        $this->assertStringContainsString('<loc>https://paydargroup.test/sitemaps/pages.xml</loc>', $xml);
        $this->assertMatchesRegularExpression('#<loc>https://paydargroup.test/sitemaps/articles.xml</loc>\s*<lastmod>2026-10-01T10:00:00#', $xml);
        $this->assertStringNotContainsString('sitemap-news.xml', $xml, 'no recent news');
        $this->assertNotFalse(simplexml_load_string($xml));

        $this->get('/sitemaps/missing.xml')->assertNotFound();
    }

    public function test_sections_list_static_pages_categories_and_images(): void
    {
        $cover = Media::factory()->create(['path' => 'media/cover.webp', 'mime_type' => 'image/webp']);
        $crypto = ArticleCategory::factory()->create(['slug' => 'crypto', 'is_active' => true]);
        $empty = ArticleCategory::factory()->create(['slug' => 'empty', 'is_active' => true]);
        $article = Article::factory()->published()->create(['slug' => 'btc', 'featured_image_id' => $cover->id, 'updated_at' => '2026-10-05 08:00:00']);
        $article->categories()->sync([$crypto->id]);

        $pages = $this->get('/sitemaps/pages.xml')->assertOk()->getContent();
        $this->assertStringContainsString('<loc>https://paydargroup.test</loc>', $pages);
        $this->assertMatchesRegularExpression('#<loc>https://paydargroup.test/articles</loc>\s*<lastmod>2026-10-05T08:00:00#', $pages);

        $articles = $this->get('/sitemaps/articles.xml')->assertOk()->getContent();
        $this->assertStringContainsString('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', $articles);
        $this->assertMatchesRegularExpression('#<loc>https://paydargroup.test/articles/btc</loc>.*<image:loc>https://paydargroup.test/storage/media/cover.webp</image:loc>#s', $articles);
        $this->assertStringContainsString('<loc>https://paydargroup.test/articles?category=crypto</loc>', $articles);
        $this->assertStringNotContainsString('category=empty', $articles, 'a category without articles is left out');
        $this->assertNotFalse(simplexml_load_string($articles));
    }

    public function test_news_sitemap_lists_the_last_two_days_of_news(): void
    {
        $this->travelTo(now()->startOfMinute());
        $run = NewsRun::create(['trigger' => 'scheduled', 'status' => 'succeeded']);
        Article::factory()->published()->create(['slug' => 'fresh-news', 'title' => 'طلا & دلار', 'news_run_id' => $run->id, 'published_at' => now()->subHours(3)]);
        $marked = Article::factory()->published()->create(['slug' => 'marked-news', 'published_at' => now()->subDay()]);
        $marked->seo()->create(['schema_type' => 'NewsArticle']);
        Article::factory()->published()->create(['slug' => 'old-news', 'news_run_id' => $run->id, 'published_at' => now()->subDays(3)]);
        Article::factory()->published()->create(['slug' => 'plain-article', 'published_at' => now()->subHour()]);

        $xml = $this->get('/sitemap-news.xml')->assertOk()->getContent();

        $this->assertStringContainsString('xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"', $xml);
        $this->assertStringContainsString('<news:title>طلا &amp; دلار</news:title>', $xml);
        $this->assertStringContainsString('<news:language>fa</news:language>', $xml);
        $this->assertStringContainsString('/articles/marked-news</loc>', $xml);
        $this->assertStringNotContainsString('old-news', $xml);
        $this->assertStringNotContainsString('plain-article', $xml);
        $this->assertNotFalse(simplexml_load_string($xml));

        $this->get('/sitemap.xml')->assertSee('<loc>https://paydargroup.test/sitemap-news.xml</loc>', false);
    }

    public function test_cached_sitemaps_are_refreshed_when_content_changes(): void
    {
        $this->get('/sitemaps/articles.xml')->assertDontSee('/articles/late');
        $article = Article::factory()->published()->create(['slug' => 'late']);
        $this->get('/sitemaps/articles.xml')->assertSee('/articles/late');

        $article->seo()->create(['robots_index' => false]);
        $this->get('/sitemaps/articles.xml')->assertDontSee('/articles/late');
    }
}
