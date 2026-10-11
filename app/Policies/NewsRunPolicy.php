<?php

namespace App\Policies;

use App\Models\User;

/** Automatic news: configuring it and starting runs need news.manage. */
class NewsRunPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('news.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('news.manage');
    }
}
