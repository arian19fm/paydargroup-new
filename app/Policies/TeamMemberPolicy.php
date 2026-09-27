<?php

namespace App\Policies;

use App\Models\TeamMember;
use App\Models\User;

/** Maps TeamMember abilities onto permissions (team.view / team.manage). */
class TeamMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('team.view');
    }

    public function view(User $user, TeamMember $model): bool
    {
        return $user->can('team.view');
    }

    public function create(User $user): bool
    {
        return $user->can('team.manage');
    }

    public function update(User $user, TeamMember $model): bool
    {
        return $user->can('team.manage');
    }

    public function delete(User $user, TeamMember $model): bool
    {
        return $user->can('team.manage');
    }
}
