<?php

namespace Tests\Feature\Admin;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class ArticleManagementTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_article_crud_basics_with_categories(): void
    {
        $admin = $this->superAdmin();
        $categories = ArticleCategory::factory()->count(2)->create();

        $this->actingAs($admin)->post('/admin/articles', [
            'title' => 'خبر اول',
            'slug' => 'first-news',
            'content' => 'متن خبر',
            'status' => 'published',
            'categories' => $categories->pluck('id')->all(),
            'author_id' => $admin->id,
        ])->assertRedirect();

        $article = Article::where('slug', 'first-news')->firstOrFail();
        $this->assertTrue($article->isPublished());
        $this->assertCount(2, $article->categories);
        $this->assertSame($admin->id, $article->author_id);

        $this->actingAs($admin)->put("/admin/articles/{$article->id}", [
            'title' => 'خبر اول', 'slug' => $article->slug, 'content' => 'ویرایش', 'status' => 'published',
            'published_at' => $article->published_at->format('Y-m-d H:i:s'),
            'categories' => [$categories->first()->id],
        ])->assertRedirect();

        $this->assertSame('ویرایش', $article->fresh()->content);
        $this->assertCount(1, $article->fresh()->categories);

        $this->actingAs($admin)->delete("/admin/articles/{$article->id}")->assertRedirect('/admin/articles');
        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }

    public function test_content_is_required_for_articles(): void
    {
        $this->actingAs($this->superAdmin())->from('/admin/articles/create')
            ->post('/admin/articles', ['title' => 'x', 'status' => 'draft'])
            ->assertSessionHasErrors('content');
    }

    public function test_draft_and_future_articles_are_not_public(): void
    {
        Article::factory()->create(['slug' => 'draft-news']);
        Article::factory()->scheduled()->create(['slug' => 'future-news']);

        $this->get('/articles/draft-news')->assertNotFound();
        $this->get('/articles/future-news')->assertNotFound();
        $this->assertSame(0, Article::published()->count());
    }

    public function test_published_article_is_public_with_article_json_ld(): void
    {
        $article = Article::factory()->published()->create(['slug' => 'big-news', 'title' => 'خبر مهم']);

        $this->get('/articles/big-news')
            ->assertOk()
            ->assertSee('<h1>خبر مهم</h1>', false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('<meta property="og:type" content="article">', false)
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/articles/big-news">', false);

        // Article schema must not leak onto other pages.
        $this->get('/')->assertDontSee('"@type":"Article"', false);
    }

    public function test_deleting_a_category_keeps_articles(): void
    {
        $admin = $this->superAdmin();
        $category = ArticleCategory::factory()->create();
        $article = Article::factory()->create();
        $article->categories()->attach($category);

        $this->actingAs($admin)->delete("/admin/categories/{$category->id}")->assertRedirect();

        $this->assertDatabaseMissing('article_categories', ['id' => $category->id]);
        $this->assertDatabaseHas('articles', ['id' => $article->id, 'deleted_at' => null]);
        $this->assertCount(0, $article->fresh()->categories);
    }
}
