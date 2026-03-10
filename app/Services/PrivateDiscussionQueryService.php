<?php

namespace App\Services;

use App\Models\PrivateGroup;
use App\Models\User;
use Illuminate\Support\Collection;

class PrivateDiscussionQueryService
{
    public function getGroupsForUser(User $user): Collection
    {
        return PrivateGroup::query()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('users.id', $user->id);
            })
            ->with(['members:id,name', 'messages' => function ($query) {
                $query->latest()->limit(1)->with('user:id,name');
            }])
            ->latest()
            ->get();
    }

    public function getAvailableUsersForIndex(User $user): Collection
    {
        return User::query()
            ->where('id', '!=', $user->id)
            ->whereNull('banned_at')
            ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);
    }

    public function getAvailableUsersForGroup(User $currentUser, PrivateGroup $privateGroup): Collection
    {
        return User::query()
            ->where('id', '!=', $currentUser->id)
            ->whereNull('banned_at')
            ->whereNotIn('id', $privateGroup->members->pluck('id'))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    public function getMessagesForGroup(PrivateGroup $privateGroup, int $afterId): Collection
    {
        return $privateGroup->messages()
            ->with('user:id,name')
            ->when($afterId > 0, function ($query) use ($afterId) {
                $query->where('id', '>', $afterId);
            })
            ->orderBy('id')
            ->get();
    }
}
