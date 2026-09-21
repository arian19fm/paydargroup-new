<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_super_admin_can_create_users_with_roles(): void
    {
        $super = $this->superAdmin();

        $this->actingAs($super)->post('/admin/users', [
            'name' => 'ویراستار', 'email' => 'Editor@Example.test',
            'password' => 'Str0ngPassword123', 'password_confirmation' => 'Str0ngPassword123',
            'roles' => ['editor'], 'is_active' => '1',
        ])->assertRedirect('/admin/users');

        $user = User::where('email', 'editor@example.test')->firstOrFail();
        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue(Hash::check('Str0ngPassword123', $user->password));
        $this->assertNotSame('Str0ngPassword123', $user->password);
    }

    public function test_weak_password_and_duplicate_email_are_rejected(): void
    {
        $super = $this->superAdmin(['email' => 'taken@example.test']);

        $this->actingAs($super)->from('/admin/users/create')->post('/admin/users', [
            'name' => 'x', 'email' => 'taken@example.test', 'password' => 'short', 'password_confirmation' => 'short', 'roles' => ['editor'],
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_toggle_active_rules(): void
    {
        $super = $this->superAdmin();
        $other = $this->editor();

        $this->actingAs($super)->patch("/admin/users/{$other->id}/toggle-active")->assertRedirect();
        $this->assertFalse($other->fresh()->is_active);

        // Nobody can deactivate themselves.
        $this->actingAs($super)->patch("/admin/users/{$super->id}/toggle-active")->assertForbidden();

        // Admins cannot touch super admins.
        $admin = $this->admin();
        $this->actingAs($admin)->patch("/admin/users/{$super->id}/toggle-active")->assertForbidden();
    }

    public function test_admin_create_command_creates_a_hashed_user(): void
    {
        $this->seedRoles();

        $this->artisan('admin:create', ['--name' => 'مدیر', '--email' => 'boss@example.test', '--role' => 'super_admin'])
            ->expectsQuestion('Password (min 12 chars, letters and numbers)', 'VerySecret12345')
            ->expectsQuestion('Confirm password', 'VerySecret12345')
            ->expectsOutputToContain('created with role [super_admin]')
            ->doesntExpectOutputToContain('VerySecret12345')
            ->assertSuccessful();

        $user = User::where('email', 'boss@example.test')->firstOrFail();
        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue(Hash::check('VerySecret12345', $user->password));
    }

    public function test_admin_create_command_rejects_weak_passwords(): void
    {
        $this->seedRoles();

        $this->artisan('admin:create', ['--name' => 'x', '--email' => 'weak@example.test', '--role' => 'editor'])
            ->expectsQuestion('Password (min 12 chars, letters and numbers)', 'weak')
            ->expectsQuestion('Confirm password', 'weak')
            ->assertFailed();

        $this->assertDatabaseMissing('users', ['email' => 'weak@example.test']);
    }
}
