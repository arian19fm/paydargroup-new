<?php

namespace Tests\Feature\Admin;

use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_admin_can_log_in_and_is_redirected_to_dashboard(): void
    {
        $user = $this->superAdmin(['email' => 'admin@example.test']);

        $response = $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'password']);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);

        $this->get('/admin')->assertOk()->assertSee($user->name);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->superAdmin(['email' => 'admin@example.test']);

        $this->from('/admin/login')
            ->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'wrong-password'])
            ->assertRedirect('/admin/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $this->superAdmin(['email' => 'inactive@example.test', 'is_active' => false]);

        $this->post('/admin/login', ['email' => 'inactive@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_deactivated_after_login_is_logged_out_on_next_request(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)->get('/admin')->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)->post('/admin/logout')->assertRedirect('/admin/login');

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/pages')->assertRedirect('/admin/login');
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs($this->superAdmin())->get('/admin/login')->assertRedirect('/admin');
    }

    public function test_login_is_rate_limited(): void
    {
        $this->superAdmin(['email' => 'admin@example.test']);

        for ($i = 0; $i < LoginRequest::MAX_ATTEMPTS; $i++) {
            $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'wrong']);
        }

        // Even the correct password is refused while locked out.
        $this->post('/admin/login', ['email' => 'admin@example.test', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString(trim(Str::before(__('auth.throttle'), ':seconds')), session('errors')->first('email'));
    }

    public function test_user_without_admin_access_permission_gets_403(): void
    {
        $this->seedRoles();
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_no_registration_routes_exist(): void
    {
        $this->get('/admin/register')->assertNotFound();
        $this->get('/register')->assertNotFound();
    }
}
