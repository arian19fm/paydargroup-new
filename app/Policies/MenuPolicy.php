<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\User;

/**
 * Maps Menu abilities onto permissions (menus.view / menus.manage).
 */
class MenuPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('menus.view');
    }

    public function view(User $user, Menu $model): bool
    {
        return $user->can('menus.view');
    }

    public function create(User $user): bool
    {
        return $user->can('menus.manage');
    }

    public function update(User $user, Menu $model): bool
    {
        return $user->can('menus.manage');
    }

    public function delete(User $user, Menu $model): bool
    {
        return $user->can('menus.manage');
    }
}
