<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Menu;
use App\Models\Page;
use App\Support\Menus\MenuRepository;
use Database\Seeders\MenusSeeder;
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
        $menu->items()->create(['label' => 'زیرمنو', 'url' => 'https://example.com/x', 'parent_id' => $parent->id, 'target' => '_self']);
        $menu->items()->create(['label' => 'پنهان', 'page_id' => $draft->id, 'target' => '_self', 'sort_order' => 2]);
        $menu->items()->create(['label' => 'غیرفعال', 'url' => '/team', 'is_active' => false, 'target' => '_self', 'sort_order' => 3]);

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

    public function test_businesses_source_lists_published_businesses_automatically(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'main']);
        $menu->items()->create(['label' => 'کسب‌وکارها', 'source' => 'businesses', 'target' => '_self']);

        $second = Business::factory()->published()->create(['title' => 'صرافی پایدار', 'slug' => 'exchange', 'sort_order' => 2]);
        $first = Business::factory()->published()->create(['title' => 'صندوق پایدار', 'slug' => 'fund', 'sort_order' => 1]);
        Business::factory()->create(['title' => 'پیش‌نویس', 'slug' => 'draft-one']);
        Business::factory()->scheduled()->create(['title' => 'زمان‌بندی‌شده', 'slug' => 'later-one']);

        $tree = app(MenuRepository::class)->tree('main');

        $this->assertCount(1, $tree);
        $this->assertSame('/#products', $tree[0]['url']);
        $this->assertSame(['صندوق پایدار', 'صرافی پایدار'], array_column($tree[0]['children'], 'label'));
        $this->assertSame($first->publicUrl(), $tree[0]['children'][0]['url']);
        $this->assertSame($second->publicUrl(), $tree[0]['children'][1]['url']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('href="'.$first->publicUrl().'"', $html);
        $this->assertStringNotContainsString('پیش‌نویس', $html);
        $this->assertStringNotContainsString('زمان‌بندی‌شده', $html);

        // Publishing a business refreshes the cached menu without any admin action.
        $third = Business::factory()->published()->create(['title' => 'کارگزاری پایدار', 'slug' => 'broker', 'sort_order' => 3]);
        $this->get('/')->assertSee('href="'.$third->publicUrl().'"', false);
    }

    public function test_url_links_to_cms_pages_are_hidden_until_the_page_is_published(): void
    {
        $menu = Menu::create(['name' => 'Main', 'location' => 'main']);
        $menu->items()->create(['label' => 'درباره ما', 'url' => '/about', 'target' => '_self', 'sort_order' => 1]);
        $menu->items()->create(['label' => 'تیم ما', 'url' => '/team', 'target' => '_self', 'sort_order' => 2]);

        $this->get('/')->assertOk()->assertDontSee('href="/about"', false)->assertSee('href="/team"', false);

        Page::factory()->published()->create(['slug' => 'about']);

        $this->get('/')->assertSee('href="/about"', false);
    }

    public function test_source_item_needs_no_target_and_is_saved_from_the_admin(): void
    {
        $admin = $this->superAdmin();
        $menu = Menu::create(['name' => 'Main', 'location' => 'main']);

        $this->actingAs($admin)->post("/admin/menus/{$menu->id}/items", ['label' => 'کسب‌وکارها', 'target' => '_self', 'source' => 'businesses'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('menu_items', ['menu_id' => $menu->id, 'source' => 'businesses', 'url' => null, 'page_id' => null]);

        $this->actingAs($admin)->from("/admin/menus/{$menu->id}/edit")
            ->post("/admin/menus/{$menu->id}/items", ['label' => 'x', 'target' => '_self', 'source' => 'unknown'])
            ->assertSessionHasErrors('source');

        $this->actingAs($admin)->get("/admin/menus/{$menu->id}/edit")->assertOk()->assertSee(__('admin.menus.sources.businesses'));
    }

    public function test_seeder_builds_the_default_menus_once_and_never_overwrites_edits(): void
    {
        $this->seed(MenusSeeder::class);
        $this->seed(MenusSeeder::class);

        $this->assertSame(['footer', 'legal', 'main'], Menu::query()->orderBy('location')->pluck('location')->all());

        $main = Menu::where('location', 'main')->first();
        $this->assertSame(
            ['home', 'about', 'businesses', 'team', 'careers', 'articles', 'contact'],
            $main->items()->pluck('key')->all(),
        );
        $this->assertSame(5, Menu::where('location', 'footer')->first()->items()->count());
        $this->assertSame(['privacy', 'terms'], Menu::where('location', 'legal')->first()->items()->pluck('key')->all());

        $businesses = $main->items()->where('key', 'businesses')->first();
        $this->assertSame('businesses', $businesses->source);
        $this->assertSame(__('nav.businesses'), $businesses->label);

        // Admin edits survive a re-run; the "about" link waits for its page.
        $main->items()->where('key', 'team')->first()->update(['label' => 'همکاران', 'is_active' => false]);
        $this->seed(MenusSeeder::class);
        $this->assertSame(7, $main->items()->count());
        $this->assertDatabaseHas('menu_items', ['key' => 'team', 'label' => 'همکاران', 'is_active' => false]);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString(__('nav.contact'), $html);
        $this->assertStringNotContainsString('همکاران', $html);
        $this->assertStringNotContainsString('href="/about"', $html);

        Business::factory()->published()->create(['title' => 'صندوق پایدار', 'slug' => 'fund']);
        Page::factory()->published()->create(['slug' => 'about']);

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('صندوق پایدار', $html);
        $this->assertStringContainsString('href="/about"', $html);
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
