<?php

namespace App\Policies;

use App\Models\ArticleCategory;
use App\Models\User;

/**
 * Maps ArticleCategory abilities onto permissions (categories.view / categories.manage).
 */
class ArticleCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('categories.view');
    }

    public function view(User $user, ArticleCategory $model): bool
    {
        return $user->can('categories.view');
    }

    public function create(User $user): bool
    {
        return $user->can('categories.manage');
    }

    public function update(User $user, ArticleCategory $model): bool
    {
        return $user->can('categories.manage');
    }

    public function delete(User $user, ArticleCategory $model): bool
    {
        return $user->can('categories.manage');
    }
}
