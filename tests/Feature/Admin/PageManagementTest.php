<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_page_crud_basics(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/pages/create')->assertOk();

        $this->actingAs($admin)->post('/admin/pages', [
            'title' => 'درباره ما',
            'slug' => 'about-us',
            'excerpt' => 'خلاصه',
            'content' => "پاراگراف اول\n\nپاراگراف دوم",
            'status' => 'published',
        ])->assertRedirect();

        $page = Page::where('slug', 'about-us')->firstOrFail();
        $this->assertTrue($page->isPublished());
        $this->assertSame($admin->id, $page->created_by);

        $this->actingAs($admin)->get("/admin/pages/{$page->id}/edit")->assertOk()->assertSee('about-us');

        $this->actingAs($admin)->put("/admin/pages/{$page->id}", [
            'title' => 'درباره گروه', 'slug' => 'about-us', 'status' => 'draft',
        ])->assertRedirect();

        $this->assertSame('درباره گروه', $page->fresh()->title);
        $this->assertFalse($page->fresh()->isPublished());

        $this->actingAs($admin)->delete("/admin/pages/{$page->id}")->assertRedirect('/admin/pages');
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_slug_must_be_unique_and_not_reserved(): void
    {
        $admin = $this->superAdmin();
        Page::factory()->create(['slug' => 'services']);

        $this->actingAs($admin)->from('/admin/pages/create')
            ->post('/admin/pages', ['title' => 'x', 'slug' => 'services', 'status' => 'draft'])
            ->assertSessionHasErrors('slug');

        foreach (['admin', 'up', 'articles', 'build'] as $reserved) {
            $this->actingAs($admin)->from('/admin/pages/create')
                ->post('/admin/pages', ['title' => 'x', 'slug' => $reserved, 'status' => 'draft'])
                ->assertSessionHasErrors('slug');
        }

        $this->assertSame(1, Page::count());
    }

    public function test_slug_is_generated_from_title_when_empty(): void
    {
        $this->actingAs($this->superAdmin())->post('/admin/pages', ['title' => 'Our Services', 'status' => 'draft']);

        $this->assertDatabaseHas('pages', ['slug' => 'our-services']);
    }

    public function test_draft_and_scheduled_pages_are_not_public(): void
    {
        Page::factory()->create(['slug' => 'draft-page']);
        Page::factory()->scheduled()->create(['slug' => 'future-page']);

        $this->get('/draft-page')->assertNotFound();
        $this->get('/future-page')->assertNotFound();
    }

    public function test_published_page_resolves_publicly_with_seo_head(): void
    {
        $page = Page::factory()->published()->create(['slug' => 'about-us', 'title' => 'درباره ما', 'excerpt' => 'خلاصه صفحه']);

        $this->get('/about-us')
            ->assertOk()
            ->assertSee('<h1>درباره ما</h1>', false)
            ->assertSee('<title>درباره ما | Paydar Group</title>', false)
            ->assertSee('<meta name="description" content="خلاصه صفحه">', false)
            ->assertSee('<link rel="canonical" href="https://paydargroup.test/about-us">', false)
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->assertTrue(Page::published()->where('id', $page->id)->exists());
    }

    public function test_published_scope_excludes_drafts_and_future_dates(): void
    {
        Page::factory()->published()->create();
        Page::factory()->create();
        Page::factory()->scheduled()->create();

        $this->assertSame(1, Page::published()->count());
    }

    public function test_catch_all_route_never_shadows_reserved_paths(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/robots.txt')->assertOk();
        $this->get('/sitemap.xml')->assertOk();
        $this->get('/up')->assertOk();
        $this->get('/articles')->assertOk()->assertSee(__('articles.title')); // the listing, never a CMS page
        $this->get('/careers')->assertOk();
    }
}
