<?php

namespace App\Policies;

use App\Models\Subforum;
use App\Models\User;

class SubforumPolicy
{
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Subforum $subforum): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Subforum $subforum): bool
    {
        return $user->isAdmin();
    }
}
