<?php

namespace App\Policies;

use App\Models\TeamPart;
use App\Models\User;

class TeamPartPolicy
{
    public function viewAny(User $user): bool
    {
        return session()->get('session_team')
                ?->admins()
                ->where('user_id', $user->id)
                ->exists() || 
            session()->get('session_team')
                ?->members()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function create(User $user): bool
    {
        return session()->get('session_team')
                ?->admins()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function update(User $user, TeamPart $teamPart): bool
    {
        return session()->get('session_team')
                ?->admins()
                ->where('user_id', $user->id)
                ->exists();
    }
}
