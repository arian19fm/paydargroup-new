<?php

namespace Tests\Concerns;

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Spatie\Permission\Models\Role;

/**
 * Helpers for admin tests: seeds roles/permissions and creates users with
 * a given role. Requires RefreshDatabase on the test class.
 */
trait InteractsWithAdmin
{
    protected function seedRoles(): void
    {
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function adminUser(string $role = 'super_admin', array $attributes = []): User
    {
        if (! Role::where('name', $role)->exists()) {
            $this->seedRoles();
        }

        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    protected function superAdmin(array $attributes = []): User
    {
        return $this->adminUser('super_admin', $attributes);
    }

    protected function editor(array $attributes = []): User
    {
        return $this->adminUser('editor', $attributes);
    }

    protected function admin(array $attributes = []): User
    {
        return $this->adminUser('admin', $attributes);
    }
}
