<?php

namespace App\Policies;

use App\Models\JobApplication;
use App\Models\User;

/** Maps JobApplication abilities onto permissions (applications.view / applications.manage). */
class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('applications.view');
    }

    public function view(User $user, JobApplication $model): bool
    {
        return $user->can('applications.view');
    }

    public function delete(User $user, JobApplication $model): bool
    {
        return $user->can('applications.manage');
    }
}
