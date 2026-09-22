<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Media;
use App\Models\Page;
use App\Models\SeoMeta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class SeoIntegrationTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_seo_meta_is_a_polymorphic_relation_shared_by_pages_and_articles(): void
    {
        $page = Page::factory()->create();
        $article = Article::factory()->create();

        $page->seo()->create(['title' => 'p']);
        $article->seo()->create(['title' => 'a']);

        $this->assertInstanceOf(SeoMeta::class, $page->fresh()->seo);
        $this->assertSame(Page::class, $page->seo->seoable_type);
        $this->assertTrue($article->fresh()->seo->seoable->is($article));
        $this->assertSame(2, SeoMeta::count());
    }

    public function test_model_falls_back_to_content_when_no_overrides_exist(): void
    {
        config(['seo.indexable' => true]);
        $article = Article::factory()->published()->create(['slug' => 'news-1', 'title' => 'عنوان مقاله', 'excerpt' => 'خلاصه مقاله']);
        $article->featuredImage()->associate(Media::factory()->create(['path' => 'media/x.jpg', 'alt_text' => 'تصویر']))->save();

        seo()->fromModel($article->fresh(['seo', 'featuredImage']));

        $this->assertSame('عنوان مقاله', seo()->rawTitle());
        $this->assertSame('خلاصه مقاله', seo()->getDescription());
        $this->assertSame('https://paydargroup.test/articles/news-1', seo()->getCanonical());
        $this->assertSame('article', seo()->getOgType());
        $this->assertSame('index, follow', seo()->robots());
        $this->assertStringEndsWith('/storage/media/x.jpg', seo()->getImage()['url']);
        $this->assertSame(0, SeoMeta::count(), 'fallbacks must never be written to the database');
    }

    public function test_explicit_overrides_win_and_render(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'legal-notice', 'title' => 'تماس', 'excerpt' => 'خلاصه']);
        $page->seo()->create([
            'title' => 'عنوان سئو',
            'description' => 'توضیح سئو',
            'canonical_url' => 'https://paydargroup.test/preferred',
            'robots_index' => false,
            'og_title' => 'عنوان OG',
        ]);

        $this->get('/legal-notice')
            ->assertOk()
            ->assertSee('<title>عنوان سئو | Paydar Group</title>', false)
            ->assertSee('<meta name="description" content="توضیح سئو">', false)
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/preferred">', false)
            ->assertSee('<meta name="robots" content="noindex, follow">', false)
            ->assertSee('<meta property="og:title" content="عنوان OG">', false);
    }

    public function test_admin_form_saves_seo_only_when_something_is_set(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post('/admin/pages', [
            'title' => 'Plain', 'status' => 'draft',
            'seo' => ['title' => '', 'description' => '', 'robots_index' => '1', 'robots_follow' => '1'],
        ]);
        $this->assertSame(0, SeoMeta::count());

        $page = Page::where('slug', 'plain')->firstOrFail();

        $this->actingAs($admin)->put("/admin/pages/{$page->id}", [
            'title' => 'Plain', 'slug' => 'plain', 'status' => 'draft',
            'seo' => ['title' => 'Custom', 'robots_index' => '1', 'robots_follow' => '0'],
        ])->assertRedirect();

        $this->assertDatabaseHas('seo_meta', ['seoable_id' => $page->id, 'title' => 'Custom', 'robots_index' => 1, 'robots_follow' => 0]);
    }

    public function test_sitemap_includes_only_published_indexable_content(): void
    {
        config(['seo.indexable' => true]);

        $visible = Page::factory()->published()->create(['slug' => 'visible']);
        Page::factory()->create(['slug' => 'draft']);
        Page::factory()->scheduled()->create(['slug' => 'scheduled']);
        $hidden = Page::factory()->published()->create(['slug' => 'hidden']);
        $hidden->seo()->create(['robots_index' => false]);
        Article::factory()->published()->create(['slug' => 'story']);
        Article::factory()->create(['slug' => 'draft-story']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>https://paydargroup.test/visible</loc>', false)
            ->assertSee('<loc>https://paydargroup.test/articles/story</loc>', false)
            ->assertDontSee('/draft</loc>', false)
            ->assertDontSee('/scheduled</loc>', false)
            ->assertDontSee('/hidden</loc>', false)
            ->assertDontSee('/draft-story</loc>', false)
            ->assertDontSee('/admin', false);
    }
}
