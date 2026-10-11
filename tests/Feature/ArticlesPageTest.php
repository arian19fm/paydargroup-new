<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_shows_published_articles_with_chips_and_pager(): void
    {
        $blockchain = ArticleCategory::factory()->create(['name' => 'بلاکچین', 'slug' => 'blockchain', 'sort_order' => 1]);
        $ai = ArticleCategory::factory()->create(['name' => 'هوش مصنوعی', 'slug' => 'ai', 'sort_order' => 2]);
        ArticleCategory::factory()->create(['name' => 'غیرفعال', 'slug' => 'off', 'is_active' => false]);
        $cover = Media::factory()->create();

        $first = Article::factory()->published()->create(['title' => 'مقالهٔ اول', 'featured_image_id' => $cover->id, 'published_at' => now()->subDay()]);
        $first->categories()->sync([$blockchain->id]);
        $second = Article::factory()->published()->create(['title' => 'مقالهٔ دوم', 'published_at' => now()->subMinutes(2)]);
        $second->categories()->sync([$ai->id]);
        Article::factory()->create(['title' => 'پیش‌نویس']);
        Article::factory()->published()->count(9)->create(['title' => 'مقالهٔ قدیمی', 'published_at' => now()->subWeek()]);

        $html = $this->get('/articles')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('<title>'.__('articles.title').' | Paydar Group</title>', $html);
        $this->assertMatchesRegularExpression('/class="pg-chip\s+is-active/', $html); // the "همه" chip is active (Blade leaves two spaces)
        $this->assertMatchesRegularExpression('/pg-chip\s+is-active\s*"[^>]*aria-current="page"/', $html);
        $this->assertStringContainsString('href="'.route('articles.index', ['category' => 'blockchain']).'"', $html);
        $this->assertStringNotContainsString('غیرفعال', $html);
        $this->assertMatchesRegularExpression('/مقالهٔ دوم.*مقالهٔ اول/s', $html); // newest first
        $this->assertStringContainsString($cover->url(), $html);
        $this->assertStringNotContainsString('پیش‌نویس', $html);
        // The newest post is featured above the grid on the first page only.
        $this->assertSame(1, substr_count($html, 'class="pg-feature-post"'));
        $this->assertMatchesRegularExpression('/pg-feature-post.*مقالهٔ دوم.*pg-articles__grid/s', $html);
        $this->assertSame(8, substr_count($html, 'class="pg-post pg-articles__item"'));
        $this->assertStringContainsString('class="pg-pager__item is-active" aria-current="page"', $html);
        $this->assertStringContainsString('href="'.route('articles.index', ['page' => 2]).'" rel="next"', $html);
        $this->assertStringContainsString('pg-header--light', $html);

        $page2 = $this->get('/articles?page=2')->assertOk()->assertSee('مقالهٔ قدیمی')->getContent();
        $this->assertStringNotContainsString('pg-feature-post', $page2);

        $filtered = $this->get('/articles?category=blockchain')->assertOk()->getContent();
        $this->assertStringContainsString('مقالهٔ اول', $filtered);
        $this->assertStringNotContainsString('مقالهٔ دوم', $filtered);
        $this->assertStringContainsString('<title>بلاکچین | '.__('articles.title').' | Paydar Group</title>', $filtered);
        $this->assertStringContainsString('rel="canonical" href="https://paydargroup.test/articles?category=blockchain"', $filtered);

        $this->get('/articles?category=missing')->assertNotFound();
        $this->get('/sitemaps/pages.xml')->assertOk()->assertSee('https://paydargroup.test/articles</loc>', false);
    }

    public function test_listing_empty_state(): void
    {
        $this->get('/articles')->assertOk()->assertSee('هنوز مطلبی منتشر نشده است.')->assertDontSee('pg-pager');
    }

    public function test_article_page_renders_body_toc_share_and_latest_posts(): void
    {
        $cover = Media::factory()->create(['alt_text' => 'کاور']);
        $article = Article::factory()->published()->create([
            'title' => 'مبانی سرمایه‌گذاری', 'slug' => 'basics', 'excerpt' => 'لید مقاله', 'featured_image_id' => $cover->id,
            'content' => '<h2>دارایی توکنی چیست؟</h2><p>پاراگراف <b>x</b><script>alert(1)</script></p><h2>سود دوره‌ای</h2><ul><li><p>مورد یک</p></li><li><p>مورد دو</p></li></ul>',
        ]);
        $other = Article::factory()->published()->create(['title' => 'مطلب دیگر']);
        Article::factory()->create(['title' => 'پیش‌نویس مخفی']);

        $html = $this->get('/articles/basics')->assertOk()->getContent();

        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('alt="کاور"', $html);
        $this->assertStringContainsString('<h2 id="section-1">دارایی توکنی چیست؟</h2>', $html);
        $this->assertStringContainsString('<p>پاراگراف <b>x</b></p>', $html);
        $this->assertStringNotContainsString('alert(1)', $html);
        $this->assertStringContainsString('<ul><li><p>مورد یک</p></li><li><p>مورد دو</p></li></ul>', $html);
        $this->assertStringContainsString('<li><a href="#section-1">دارایی توکنی چیست؟</a></li>', $html);
        $this->assertStringContainsString('data-article-toc', $html);
        $this->assertStringContainsString('<li><a href="#section-2">سود دوره‌ای</a></li>', $html);
        $this->assertStringContainsString('لید مقاله', $html);
        $this->assertStringContainsString('data-copy-link', $html);
        $this->assertStringContainsString('https://www.linkedin.com/sharing/share-offsite/?url='.urlencode($article->publicUrl()), $html);
        $this->assertStringContainsString('مطلب دیگر', $html); // latest posts strip
        $this->assertStringContainsString('href="'.route('articles.index').'"', $html);
        $this->assertStringContainsString('id="contact"', $html);
        $this->assertStringNotContainsString('پیش‌نویس مخفی', $html);
        $this->assertStringContainsString('"@type":"Article"', $html);
    }
}
