<?php

namespace App\Policies;

use App\Models\User;

class TeamPartUploadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->teamAdminships()->exists();
    }

    public function create(User $user): bool
    {
        return $user->teamAdminships()->exists();
    }
}
