<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_pages_are_edited_but_never_created_or_deleted(): void
    {
        $admin = $this->superAdmin();
        $page = Page::factory()->create(['slug' => 'about', 'template' => 'about', 'title' => 'درباره ما']);

        $this->actingAs($admin)->get('/admin/pages')->assertOk()->assertSee('/about')
            ->assertDontSee(route('admin.pages.edit', $page).'" class="btn', false);
        $this->actingAs($admin)->get('/admin/pages/create')->assertStatus(405);
        $this->actingAs($admin)->post('/admin/pages', ['title' => 'x', 'status' => 'draft'])->assertStatus(405);
        $this->actingAs($admin)->delete("/admin/pages/{$page->id}")->assertStatus(405);
        $this->assertSame(1, Page::count());

        $this->actingAs($admin)->get("/admin/pages/{$page->id}/edit")->assertOk()
            ->assertSee(__('admin.pages.templates.about'))
            ->assertDontSee('name="slug"', false)
            ->assertDontSee('name="template"', false);

        // Slug and template are locked even when submitted.
        $this->actingAs($admin)->put("/admin/pages/{$page->id}", [
            'title' => 'درباره گروه', 'slug' => 'hacked', 'template' => 'default',
            'excerpt' => 'خلاصه', 'content' => "پاراگراف اول\n\nپاراگراف دوم", 'status' => 'published',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $page->refresh();
        $this->assertSame('درباره گروه', $page->title);
        $this->assertSame('about', $page->slug);
        $this->assertSame('about', $page->template);
        $this->assertTrue($page->isPublished());
        $this->assertSame($admin->id, $page->updated_by);
    }

    public function test_nobody_holds_create_or_delete_page_permissions(): void
    {
        $this->superAdmin();

        $this->assertFalse(Permission::whereIn('name', ['pages.create', 'pages.delete'])->exists());
        $this->assertFalse($this->superAdmin()->can('create', Page::class));
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
