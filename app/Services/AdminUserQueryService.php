<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class AdminUserQueryService
{
    public function getUsersForManagement(): Collection
    {
        return User::query()
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'email', 'role', 'banned_at', 'ban_reason', 'warnings_count', 'created_at']);
    }
}
