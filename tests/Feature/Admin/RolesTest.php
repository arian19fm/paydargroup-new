<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\InteractsWithAdmin;
use Tests\TestCase;

class RolesTest extends TestCase
{
    use InteractsWithAdmin, RefreshDatabase;

    public function test_super_admin_can_create_a_role_with_permissions(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/roles/create')->assertOk()->assertSee('name="permissions[]"', false);

        $this->actingAs($admin)->post('/admin/roles', [
            'name' => 'مدیر محتوا',
            'permissions' => ['admin.access', 'pages.view', 'pages.update', 'articles.view'],
        ])->assertRedirect('/admin/roles');

        $role = Role::findByName('مدیر محتوا');
        $this->assertEqualsCanonicalizing(['admin.access', 'pages.view', 'pages.update', 'articles.view'], $role->permissions->pluck('name')->all());

        // The new role is immediately assignable to users and grants access.
        $user = User::factory()->create();
        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => $user->name, 'email' => $user->email, 'is_active' => 1, 'roles' => ['مدیر محتوا'],
        ])->assertRedirect();
        $this->assertTrue($user->fresh()->can('pages.update'));
        $this->assertFalse($user->fresh()->can('pages.publish'));
        $this->actingAs($user->fresh())->get('/admin/pages')->assertOk();
    }

    public function test_role_permissions_can_be_edited_and_take_effect(): void
    {
        $admin = $this->superAdmin();
        $editor = $this->editor();

        $this->assertFalse($editor->can('pages.publish'));

        $role = Role::findByName('editor');
        $this->actingAs($admin)->put("/admin/roles/{$role->id}", [
            'name' => 'editor',
            'permissions' => ['admin.access', 'pages.view', 'pages.publish'],
        ])->assertRedirect('/admin/roles');

        $this->assertTrue($editor->fresh()->can('pages.publish'));
        $this->assertFalse($editor->fresh()->can('media.manage'));
    }

    public function test_super_admin_role_is_read_only(): void
    {
        $admin = $this->superAdmin();
        $root = Role::findByName('super_admin');

        $this->actingAs($admin)->get("/admin/roles/{$root->id}/edit")->assertForbidden();
        $this->actingAs($admin)->put("/admin/roles/{$root->id}", ['name' => 'root', 'permissions' => []])->assertForbidden();
        $this->actingAs($admin)->delete("/admin/roles/{$root->id}")->assertForbidden();
        $this->actingAs($admin)->post('/admin/roles', ['name' => 'super_admin', 'permissions' => []])->assertSessionHasErrors('name');
    }

    public function test_roles_with_users_cannot_be_deleted_but_empty_ones_can(): void
    {
        $admin = $this->superAdmin();
        $this->editor();
        $editor = Role::findByName('editor');
        $spare = Role::create(['name' => 'spare', 'guard_name' => 'web']);

        $this->actingAs($admin)->delete("/admin/roles/{$editor->id}")->assertForbidden();
        $this->actingAs($admin)->delete("/admin/roles/{$spare->id}")->assertRedirect('/admin/roles');
        $this->assertDatabaseMissing('roles', ['name' => 'spare']);
    }

    public function test_admins_without_roles_manage_only_see_the_list(): void
    {
        $manager = $this->adminUser('admin');

        $this->actingAs($manager)->get('/admin/roles')->assertOk()->assertDontSee('/admin/roles/create');
        $this->actingAs($manager)->post('/admin/roles', ['name' => 'x', 'permissions' => []])->assertForbidden();
        $this->actingAs($this->editor())->get('/admin/roles')->assertForbidden();
    }
}
