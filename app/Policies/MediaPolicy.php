<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;

/**
 * Maps Media abilities onto permissions (media.view / media.manage).
 */
class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('media.view');
    }

    public function view(User $user, Media $model): bool
    {
        return $user->can('media.view');
    }

    public function create(User $user): bool
    {
        return $user->can('media.manage');
    }

    public function update(User $user, Media $model): bool
    {
        return $user->can('media.manage');
    }

    public function delete(User $user, Media $model): bool
    {
        return $user->can('media.manage');
    }
}
