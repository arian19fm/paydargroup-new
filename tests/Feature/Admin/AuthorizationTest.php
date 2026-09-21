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

    public function test_editor_can_create_drafts_but_not_publish_or_delete(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)->get('/admin/pages')->assertOk();

        $this->actingAs($editor)->post('/admin/pages', ['title' => 'Draft page', 'status' => 'draft'])
            ->assertRedirect();
        $this->assertDatabaseHas('pages', ['slug' => 'draft-page', 'status' => 'draft', 'created_by' => $editor->id]);

        $this->actingAs($editor)->post('/admin/pages', ['title' => 'Live page', 'status' => 'published'])
            ->assertForbidden();
        $this->assertDatabaseMissing('pages', ['slug' => 'live-page']);

        $page = Page::factory()->create();
        $this->actingAs($editor)->delete("/admin/pages/{$page->id}")->assertForbidden();
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'deleted_at' => null]);
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

        $this->actingAs($super)->post('/admin/pages', ['title' => 'Live page', 'status' => ContentStatus::Published->value])
            ->assertRedirect();
        $this->assertDatabaseHas('pages', ['slug' => 'live-page', 'status' => 'published']);

        $redirect = Redirect::factory()->create();
        $this->actingAs($super)->delete("/admin/redirects/{$redirect->id}")->assertRedirect();
        $this->assertDatabaseMissing('redirects', ['id' => $redirect->id]);
    }
}
