<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class MenusTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_menu_item_needs_exactly_one_target(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'Main', 'location' => 'main']);
        $page = Page::factory()->published()->create();

        $this->actingAs($admin)->from("/admin/menus/{$menu->id}/edit")
            ->post("/admin/menus/{$menu->id}/items", ['label' => 'x', 'target' => '_self'])
            ->assertSessionHasErrors('url');

        $this->actingAs($admin)->from("/admin/menus/{$menu->id}/edit")
            ->post("/admin/menus/{$menu->id}/items", ['label' => 'x', 'target' => '_self', 'url' => '/a', 'page_id' => $page->id])
            ->assertSessionHasErrors('url');

        $this->actingAs($admin)->post("/admin/menus/{$menu->id}/items", ['label' => 'صفحه', 'target' => '_self', 'page_id' => $page->id])
            ->assertRedirect();
        $this->actingAs($admin)->post("/admin/menus/{$menu->id}/items", ['label' => 'لینک', 'target' => '_blank', 'url' => 'https://example.com'])
            ->assertRedirect();

        $this->assertSame(2, $menu->items()->count());
    }

    public function test_public_navigation_renders_managed_menu_and_hides_unpublished_pages(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'main']);
        $published = Page::factory()->published()->create(['slug' => 'about', 'title' => 'درباره']);
        $draft = Page::factory()->create(['slug' => 'secret']);

        $parent = $menu->items()->create(['label' => 'شرکت', 'page_id' => $published->id, 'target' => '_self', 'sort_order' => 1]);
        $menu->items()->create(['label' => 'زیرمنو', 'url' => '/x', 'parent_id' => $parent->id, 'target' => '_self']);
        $menu->items()->create(['label' => 'پنهان', 'page_id' => $draft->id, 'target' => '_self', 'sort_order' => 2]);
        $menu->items()->create(['label' => 'غیرفعال', 'url' => '/y', 'is_active' => false, 'target' => '_self', 'sort_order' => 3]);

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('href="https://paydargroup.test/about"', $html);
        $this->assertStringContainsString('زیرمنو', $html);
        $this->assertStringNotContainsString('پنهان', $html);
        $this->assertStringNotContainsString('غیرفعال', $html);
    }

    public function test_navigation_falls_back_to_config_when_menu_is_empty(): void
    {
        $this->get('/')->assertOk()->assertSee(__('nav.home'));
    }

    public function test_menu_cache_is_flushed_when_a_page_is_published(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'main']);
        $page = Page::factory()->create(['slug' => 'later', 'title' => 'بعداً']);
        $menu->items()->create(['label' => 'بعداً', 'page_id' => $page->id, 'target' => '_self']);

        $this->get('/')->assertDontSee('href="https://paydargroup.test/later"', false);

        $page->update(['status' => 'published', 'published_at' => now()->subMinute()]);

        $this->get('/')->assertSee('href="https://paydargroup.test/later"', false);
    }
}
