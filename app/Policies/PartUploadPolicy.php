<?php

namespace App\Policies;

use App\Models\User;

class PartUploadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->systemAdminship()->exists();
    }

    public function create(User $user): bool
    {
        return $user->systemAdminship()->exists();
    }
}