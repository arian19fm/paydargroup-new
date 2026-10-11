<?php

namespace Tests\Feature\Admin;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\NewsRun;
use App\Support\News\NewsDraft;
use App\Support\News\NewsResult;
use App\Support\News\NewsWriter;
use App\Support\Settings\Settings;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use RuntimeException;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class NewsAutomationTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    /** @var array{count: int, topics: list<string>, recent: list<string>}|null */
    public ?array $call = null;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.key' => 'test-key', 'news.timezone' => 'Asia/Tehran']);
    }

    private function fakeWriter(array $drafts, array $searched = ['https://www.reuters.com/a', 'https://coindesk.com/b'], ?\Throwable $fail = null): void
    {
        $test = $this;
        $this->app->instance(NewsWriter::class, new class($drafts, $searched, $fail, $test) extends NewsWriter
        {
            public function __construct(private array $drafts, private array $searched, private ?\Throwable $fail, private $test) {}

            public function write(int $count, array $topics, array $recentTitles, CarbonInterface $now): NewsResult
            {
                $this->test->call = ['count' => $count, 'topics' => $topics, 'recent' => $recentTitles];
                if ($this->fail) {
                    throw $this->fail;
                }

                return new NewsResult($this->drafts, $this->searched, 'claude-opus-5-5', 50000, 4000, 5);
            }
        });
    }

    private function draft(array $overrides = []): NewsDraft
    {
        return NewsDraft::fromToolInput(array_merge([
            'title' => 'بیت‌کوین از مرز ۱۰۰ هزار دلار گذشت',
            'slug' => 'bitcoin-crosses-100k',
            'excerpt' => 'قیمت بیت‌کوین در معاملات روز شنبه برای نخستین بار از ۱۰۰ هزار دلار عبور کرد.',
            'seo_title' => 'عبور بیت‌کوین از ۱۰۰ هزار دلار',
            'seo_description' => 'بیت‌کوین در معاملات شنبه از ۱۰۰ هزار دلار گذشت؛ دلایل رشد و واکنش بازار.',
            'content_html' => '<h1>تیتر</h1><p>'.str_repeat('بازار رمزارزها امروز شاهد رشد بود. ', 20).'</p><h2>چرا قیمت بالا رفت؟</h2><p onclick="x()">توضیح<script>alert(1)</script></p>',
            'sources' => [
                ['title' => 'Reuters', 'url' => 'https://www.reuters.com/a'],
                ['title' => 'ساختگی', 'url' => 'https://made-up.example/news'],
            ],
        ], $overrides));
    }

    private function configure(array $values): void
    {
        app(Settings::class)->setGroup('news', array_merge(['enabled' => true, 'run_at' => '09:00', 'articles_per_run' => 1, 'auto_publish' => true], $values));
    }

    public function test_the_daily_run_starts_after_the_configured_time_and_publishes_seo_ready_news(): void
    {
        $category = ArticleCategory::factory()->create();
        $this->configure(['category_id' => $category->id, 'articles_per_run' => 2, 'topics' => "طلا\nرمزارز"]);
        $this->fakeWriter([$this->draft()]);

        $this->travelTo(Carbon::parse('2026-10-11 08:59', 'Asia/Tehran'));
        $this->artisan('news:tick')->assertSuccessful();
        $this->assertSame(0, NewsRun::count(), 'not due yet');

        $this->travelTo(Carbon::parse('2026-10-11 09:00', 'Asia/Tehran'));
        $this->artisan('news:tick')->assertSuccessful();

        $run = NewsRun::sole();
        $this->assertSame(['succeeded', 'scheduled', 1, 50000, 5], [$run->status, $run->trigger, $run->articles_count, $run->input_tokens, $run->web_searches]);
        $this->assertSame(['count' => 2, 'topics' => ['طلا', 'رمزارز'], 'recent' => []], $this->call);

        $article = Article::sole();
        $this->assertTrue($article->isPublished());
        $this->assertSame('bitcoin-crosses-100k', $article->slug);
        $this->assertSame($run->id, $article->news_run_id);
        $this->assertSame([['title' => 'Reuters', 'url' => 'https://www.reuters.com/a']], $article->sources, 'a source not in the search results is dropped');
        $this->assertSame([$category->id], $article->categories->pluck('id')->all());
        $this->assertSame('NewsArticle', $article->seoMeta()->schema_type);
        $this->assertSame('عبور بیت‌کوین از ۱۰۰ هزار دلار', $article->seoMeta()->title);
        $this->assertStringNotContainsString('<script', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->assertStringContainsString('<h2>منابع</h2><ul><li><a href="https://www.reuters.com/a" target="_blank" rel="nofollow noopener">Reuters</a></li></ul>', $article->content);

        $html = $this->get('/articles/bitcoin-crosses-100k')->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('/<h1[\s>]/', $html));
        $this->assertStringContainsString('"@type":"NewsArticle"', $html);
        $this->assertStringContainsString('<title>عبور بیت‌کوین از ۱۰۰ هزار دلار', $html);

        // Once a day: later ticks the same day do nothing; the next day runs again.
        $this->travelTo(Carbon::parse('2026-10-11 18:00', 'Asia/Tehran'));
        $this->artisan('news:tick');
        $this->assertSame(1, NewsRun::count());

        $this->fakeWriter([$this->draft()]);
        $this->travelTo(Carbon::parse('2026-10-12 09:30', 'Asia/Tehran'));
        $this->artisan('news:tick');
        $this->assertSame(2, NewsRun::count());
        $this->assertSame('bitcoin-crosses-100k-2', Article::latest('id')->first()->slug);
        $this->assertSame(['بیت‌کوین از مرز ۱۰۰ هزار دلار گذشت'], $this->call['recent']);
    }

    public function test_nothing_runs_when_disabled_or_without_an_api_key(): void
    {
        $this->fakeWriter([$this->draft()]);
        $this->travelTo(Carbon::parse('2026-10-11 10:00', 'Asia/Tehran'));

        $this->configure(['enabled' => false]);
        $this->artisan('news:tick');
        $this->assertSame(0, NewsRun::count());

        $this->configure(['enabled' => true]);
        config(['services.anthropic.key' => null]);
        $this->artisan('news:tick');
        $this->assertSame(0, NewsRun::count());
    }

    public function test_drafts_mode_unusable_articles_and_failures(): void
    {
        $this->configure(['auto_publish' => false, 'enabled' => false]);

        $this->fakeWriter([$this->draft(), $this->draft(['slug' => 'no-sources', 'sources' => [['title' => 'x', 'url' => 'https://made-up.example/a']]])]);
        NewsRun::create(['trigger' => 'manual', 'status' => 'queued']);
        $this->artisan('news:tick');

        $this->assertSame('succeeded', NewsRun::sole()->status);
        $article = Article::sole();
        $this->assertFalse($article->isPublished(), 'saved as draft for review');
        $this->get('/articles/'.$article->slug)->assertNotFound();

        $this->fakeWriter([], fail: new RuntimeException('overloaded'));
        $run = NewsRun::create(['trigger' => 'manual', 'status' => 'queued']);
        $this->artisan('news:tick');
        $this->assertSame('failed', $run->refresh()->status);
        $this->assertStringContainsString('overloaded', $run->error);

        // A run whose process died is released after the stale timeout.
        $stuck = NewsRun::create(['trigger' => 'manual', 'status' => 'running', 'started_at' => now()->subHour()]);
        $this->fakeWriter([]);
        $this->artisan('news:tick');
        $this->assertSame('failed', $stuck->refresh()->status);
    }

    public function test_admin_page_settings_and_run_now(): void
    {
        $admin = $this->adminUser('admin');

        $this->actingAs($this->adminUser('editor'))->get('/admin/news')->assertForbidden();
        $this->actingAs($admin)->get('/admin/news')->assertOk()->assertSee(__('admin.news.run_now'))->assertSee(__('admin.news.no_scheduler'));
        $this->actingAs($admin)->get('/admin/settings/news')->assertNotFound();

        $this->actingAs($admin)->put('/admin/news', ['enabled' => 1, 'run_at' => '25:00', 'articles_per_run' => 9])
            ->assertSessionHasErrors(['run_at', 'articles_per_run']);
        $this->actingAs($admin)->put('/admin/news', ['enabled' => 1, 'run_at' => '07:30', 'articles_per_run' => 3, 'topics' => 'طلا'])
            ->assertSessionHasNoErrors()->assertRedirect('/admin/news');
        $this->assertSame([true, '07:30', 3, false], [settings('news.enabled'), settings('news.run_at'), (int) settings('news.articles_per_run'), (bool) settings('news.auto_publish')]);

        $this->actingAs($admin)->post('/admin/news/run')->assertSessionHas('success');
        $this->assertSame(['manual', 'queued', $admin->id], [NewsRun::sole()->trigger, NewsRun::sole()->status, NewsRun::sole()->requested_by]);
        $this->actingAs($admin)->post('/admin/news/run')->assertSessionHas('error');
        $this->assertSame(1, NewsRun::count());

        $this->actingAs($this->adminUser('editor'))->post('/admin/news/run')->assertForbidden();
    }
}
