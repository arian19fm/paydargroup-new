<?php

namespace App\Policies;

use App\Models\TeamGroup;
use App\Models\User;

/** Maps TeamGroup abilities onto permissions (team.view / team.manage). */
class TeamGroupPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('team.view');
    }

    public function view(User $user, TeamGroup $model): bool
    {
        return $user->can('team.view');
    }

    public function create(User $user): bool
    {
        return $user->can('team.manage');
    }

    public function update(User $user, TeamGroup $model): bool
    {
        return $user->can('team.manage');
    }

    public function delete(User $user, TeamGroup $model): bool
    {
        return $user->can('team.manage');
    }
}
