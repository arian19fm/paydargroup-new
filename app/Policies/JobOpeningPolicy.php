<?php

namespace App\Policies;

use App\Models\JobOpening;
use App\Models\User;

/** Maps JobOpening abilities onto permissions (jobs.view / jobs.manage). */
class JobOpeningPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('jobs.view');
    }

    public function view(User $user, JobOpening $model): bool
    {
        return $user->can('jobs.view');
    }

    public function create(User $user): bool
    {
        return $user->can('jobs.manage');
    }

    public function update(User $user, JobOpening $model): bool
    {
        return $user->can('jobs.manage');
    }

    public function delete(User $user, JobOpening $model): bool
    {
        return $user->can('jobs.manage');
    }
}
