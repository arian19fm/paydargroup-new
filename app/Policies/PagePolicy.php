<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

/**
 * Maps Page abilities onto permissions. Super admins bypass policies
 * through Gate::before (AppServiceProvider).
 */
class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pages.view');
    }

    public function view(User $user, Page $model): bool
    {
        return $user->can('pages.view');
    }

    public function create(User $user): bool
    {
        return $user->can('pages.create');
    }

    public function update(User $user, Page $model): bool
    {
        return $user->can('pages.update');
    }

    public function delete(User $user, Page $model): bool
    {
        return $user->can('pages.delete');
    }

    public function restore(User $user, Page $model): bool
    {
        return $user->can('pages.delete');
    }

    /** Setting status = published (or changing a published item's date). */
    public function publish(User $user, ?Page $model = null): bool
    {
        return $user->can('pages.publish');
    }
}
