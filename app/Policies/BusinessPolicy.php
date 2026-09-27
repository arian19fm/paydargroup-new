<?php

namespace App\Policies;

use App\Models\Business;
use App\Models\User;

/** Maps Business abilities onto permissions (businesses.view / businesses.manage / businesses.publish). */
class BusinessPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('businesses.view');
    }

    public function view(User $user, Business $model): bool
    {
        return $user->can('businesses.view');
    }

    public function create(User $user): bool
    {
        return $user->can('businesses.manage');
    }

    public function update(User $user, Business $model): bool
    {
        return $user->can('businesses.manage');
    }

    public function delete(User $user, Business $model): bool
    {
        return $user->can('businesses.manage');
    }

    public function publish(User $user, ?Business $model = null): bool
    {
        return $user->can('businesses.publish');
    }
}
