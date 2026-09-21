<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;

/**
 * Maps Redirect abilities onto permissions (redirects.view / redirects.manage).
 */
class RedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('redirects.view');
    }

    public function view(User $user, Redirect $model): bool
    {
        return $user->can('redirects.view');
    }

    public function create(User $user): bool
    {
        return $user->can('redirects.manage');
    }

    public function update(User $user, Redirect $model): bool
    {
        return $user->can('redirects.manage');
    }

    public function delete(User $user, Redirect $model): bool
    {
        return $user->can('redirects.manage');
    }
}
