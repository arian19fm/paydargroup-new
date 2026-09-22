<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

/**
 * Role administration. `super_admin` is the system's root role: it always
 * carries every permission (the seeder re-syncs it) and can be neither
 * edited nor deleted from the panel. A role that still has users cannot be
 * deleted — reassign them first.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('roles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('roles.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $role->name !== User::ROLE_SUPER_ADMIN && $user->can('roles.manage');
    }

    public function delete(User $user, Role $role): bool
    {
        return $role->name !== User::ROLE_SUPER_ADMIN
            && $user->can('roles.manage')
            && ! $role->users()->exists();
    }
}
