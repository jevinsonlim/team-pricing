<?php

namespace App\Policies;

use App\Models\TeamPart;
use App\Models\User;

class TeamPartPolicy
{
    public function viewAny(User $user): bool
    {
        $isAdmin = $user->teamAdminships()->exists();
        $teamAdminOfSessionTeam = session()->get('session_team')
            ?->admins()
            ->where('user_id', $user->id)
            ->exists();

        if ($isAdmin && $teamAdminOfSessionTeam) return true;

        $isMember = $user->teamMemberships()->exists();
        $teamMemberOfSessionTeam = session()->get('session_team')
            ?->members()
            ->where('user_id', $user->id)
            ->exists();

        if ($isMember && $teamMemberOfSessionTeam) return true;

        return false;
    }

    public function create(User $user): bool
    {
        return $user->teamAdminships()->exists()
            && session()->get('session_team')
                ?->admins()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function update(User $user, TeamPart $teamPart): bool
    {
        return $user->teamAdminships()->exists()
            && session()->get('session_team')
                ?->admins()
                ->where('user_id', $user->id)
                ->exists();
    }

    public function destroy(User $user, TeamPart $teamPart): bool
    {
        return $user->teamAdminships()->exists()
            && $user->teamAdminships()
                ->where('teams.id', $teamPart->team_id)
                ->exists();
    }

    public function destroyBatch(User $user): bool
    {
        return $user->teamAdminships()->exists();
    }
}
