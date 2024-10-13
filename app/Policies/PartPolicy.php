<?php

namespace App\Policies;

use App\Models\Part;
use App\Models\User;

class PartPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->systemAdminship()->exists()
            || session()->get('session_team')
            ?->admins()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->systemAdminship()->exists();
    }

    public function update(User $user, Part $part): bool
    {
        return $user->systemAdminship()->exists();
    }

    public function associateToTeam(User $user, Part $part): bool
    {
        return $user->teamAdminships()->exists();
    }
}
