<?php

namespace App\Policies;

use App\Models\User;

/**
 * Admin user management. Deleting accounts is deliberately not supported
 * (deactivate instead) so audit references stay intact.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.manage');
    }

    /** Nobody deactivates themselves; only super admins may touch super admins. */
    public function toggleActive(User $user, User $model): bool
    {
        if ($user->is($model)) {
            return false;
        }

        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('users.manage');
    }

    public function assignRoles(User $user, User $model): bool
    {
        if ($model->isSuperAdmin() && ! $user->isSuperAdmin()) {
            return false;
        }

        return $user->can('users.manage');
    }
}
