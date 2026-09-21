<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

/**
 * Maps Article abilities onto permissions. Super admins bypass policies
 * through Gate::before (AppServiceProvider).
 */
class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('articles.view');
    }

    public function view(User $user, Article $model): bool
    {
        return $user->can('articles.view');
    }

    public function create(User $user): bool
    {
        return $user->can('articles.create');
    }

    public function update(User $user, Article $model): bool
    {
        return $user->can('articles.update');
    }

    public function delete(User $user, Article $model): bool
    {
        return $user->can('articles.delete');
    }

    public function restore(User $user, Article $model): bool
    {
        return $user->can('articles.delete');
    }

    /** Setting status = published (or changing a published item's date). */
    public function publish(User $user, ?Article $model = null): bool
    {
        return $user->can('articles.publish');
    }
}
