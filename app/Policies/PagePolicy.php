<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

/**
 * Maps Page abilities onto permissions. Pages are fixed, so create and
 * delete are denied to everyone.
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

    /** Pages are fixed; none can be created from the admin. */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Page $model): bool
    {
        return $user->can('pages.update');
    }

    /** Pages are fixed; none can be deleted from the admin. */
    public function delete(User $user, Page $model): bool
    {
        return false;
    }

    public function restore(User $user, Page $model): bool
    {
        return false;
    }

    /** Setting status = published (or changing a published item's date). */
    public function publish(User $user, ?Page $model = null): bool
    {
        return $user->can('pages.publish');
    }
}
