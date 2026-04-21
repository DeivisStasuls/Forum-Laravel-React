<?php

namespace App\Services;

use App\Models\User;

class OwnershipAuthorizationService
{
    public function authorizeOwnerOrAdmin(int $ownerId, ?User $user): void
    {
        if (! $user) {
            abort(403, 'Unauthorized action.');
        }

        if ($ownerId !== $user->id && ! $user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
