<?php

namespace Tests\Feature;

use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class RedirectsTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_active_redirect_is_applied_and_counted(): void
    {
        $redirect = Redirect::factory()->create(['source_path' => '/old-page', 'destination_url' => '/new-page', 'http_status' => 301]);

        $this->get('/old-page?utm=1')
            ->assertStatus(301)
            ->assertRedirect('/new-page?utm=1');

        $redirect->refresh();
        $this->assertSame(1, $redirect->hit_count);
        $this->assertNotNull($redirect->last_hit_at);
    }

    public function test_302_status_is_respected(): void
    {
        Redirect::factory()->create(['source_path' => '/temp', 'destination_url' => 'https://example.com/x', 'http_status' => 302]);

        $this->get('/temp')->assertStatus(302)->assertRedirect('https://example.com/x');
    }

    public function test_inactive_redirect_is_ignored(): void
    {
        Redirect::factory()->create(['source_path' => '/gone', 'destination_url' => '/x', 'is_active' => false]);

        $this->get('/gone')->assertNotFound();
    }

    public function test_cache_is_refreshed_when_a_rule_changes(): void
    {
        $this->get('/later')->assertNotFound(); // negative result cached

        $redirect = Redirect::factory()->create(['source_path' => '/later', 'destination_url' => '/x']);
        $this->get('/later')->assertRedirect('/x');

        $redirect->update(['is_active' => false]);
        $this->get('/later')->assertNotFound();
    }

    public function test_obvious_loops_and_protected_paths_are_rejected(): void
    {
        $admin = $this->superAdmin();
        Redirect::factory()->create(['source_path' => '/b', 'destination_url' => '/a']);

        // Direct self-loop.
        $this->actingAs($admin)->from('/admin/redirects/create')
            ->post('/admin/redirects', ['source_path' => '/same', 'destination_url' => '/same', 'http_status' => 301])
            ->assertSessionHasErrors('destination_url');

        // Two-hop loop: /a → /b → /a.
        $this->actingAs($admin)->from('/admin/redirects/create')
            ->post('/admin/redirects', ['source_path' => '/a', 'destination_url' => '/b', 'http_status' => 301])
            ->assertSessionHasErrors('destination_url');

        // Absolute URL pointing back at the site counts as internal.
        $this->actingAs($admin)->from('/admin/redirects/create')
            ->post('/admin/redirects', ['source_path' => '/a', 'destination_url' => 'https://paydargroup.test/b', 'http_status' => 301])
            ->assertSessionHasErrors('destination_url');

        foreach (['/admin', '/admin/login', '/build/x.css', '/storage/a.jpg', '/up', '/sitemap.xml', '/robots.txt', '/'] as $protected) {
            $this->actingAs($admin)->from('/admin/redirects/create')
                ->post('/admin/redirects', ['source_path' => $protected, 'destination_url' => '/x', 'http_status' => 301])
                ->assertSessionHasErrors('source_path');
        }

        $this->actingAs($admin)->from('/admin/redirects/create')
            ->post('/admin/redirects', ['source_path' => '/ok', 'destination_url' => '/x', 'http_status' => 307])
            ->assertSessionHasErrors('http_status');

        $this->assertSame(1, Redirect::count());
    }

    public function test_infrastructure_paths_are_never_redirected_even_if_a_row_exists(): void
    {
        Redirect::factory()->create(['source_path' => '/up', 'destination_url' => '/x']);

        $this->get('/up')->assertOk();
    }
}
