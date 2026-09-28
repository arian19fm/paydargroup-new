<?php

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_editor_cannot_access_users_settings_or_redirects(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/settings/general')->assertForbidden();
        $this->actingAs($editor)->get('/admin/redirects')->assertForbidden();
        $this->actingAs($editor)->post('/admin/redirects', ['source_path' => '/a', 'destination_url' => '/b', 'http_status' => 301])->assertForbidden();
    }

    public function test_editor_can_edit_page_drafts_but_not_publish(): void
    {
        $editor = $this->editor();
        $page = Page::factory()->create(['slug' => 'privacy']);

        $this->actingAs($editor)->get('/admin/pages')->assertOk();

        $this->actingAs($editor)->put("/admin/pages/{$page->id}", ['title' => 'Draft page', 'status' => 'draft'])
            ->assertRedirect();
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Draft page', 'status' => 'draft', 'updated_by' => $editor->id]);

        $this->actingAs($editor)->put("/admin/pages/{$page->id}", ['title' => 'Live page', 'status' => 'published'])
            ->assertForbidden();
        $this->assertFalse($page->fresh()->isPublished());
    }

    public function test_admin_role_cannot_manage_users(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/users/create')->assertForbidden();
    }

    public function test_super_admin_has_full_access(): void
    {
        $super = $this->superAdmin();

        foreach (['/admin', '/admin/pages', '/admin/articles', '/admin/categories', '/admin/media', '/admin/menus', '/admin/redirects', '/admin/settings/general', '/admin/users', '/admin/users/create'] as $url) {
            $this->actingAs($super)->get($url)->assertOk();
        }

        $page = Page::factory()->create(['slug' => 'terms']);
        $this->actingAs($super)->put("/admin/pages/{$page->id}", ['title' => 'Live page', 'status' => ContentStatus::Published->value])
            ->assertRedirect();
        $this->assertDatabaseHas('pages', ['slug' => 'terms', 'status' => 'published']);

        $redirect = Redirect::factory()->create();
        $this->actingAs($super)->delete("/admin/redirects/{$redirect->id}")->assertRedirect();
        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    }
}
