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

    public function mapGroups(Collection $groups): array
    {
        return $groups->map(function ($group) {
            $latestMessage = $group->messages->first();

            return [
                'id' => $group->id,
                'name' => $group->name,
                'members' => $group->members->map(fn ($member) => [
                    'id' => $member->id,
                    'name' => $member->name,
                ])->all(),
                'latest_message' => $latestMessage ? [
                    'body' => $latestMessage->body,
                    'user_name' => $latestMessage->user?->name,
                    'created_at' => $latestMessage->created_at,
                ] : null,
                'updated_at' => $group->updated_at,
            ];
        })->all();
    }

    public function mapGroupDetails(PrivateGroup $privateGroup, User $currentUser): array
    {
        $canManage = $currentUser->id === $privateGroup->created_by;
        $availableUsers = $this->getAvailableUsersForGroup($currentUser, $privateGroup);

        return [
            'id' => $privateGroup->id,
            'name' => $privateGroup->name,
            'created_by' => $privateGroup->created_by,
            'can_manage' => $canManage,
            'members' => $privateGroup->members->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
            ])->all(),
            'messages' => $privateGroup->messages->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
                'created_at' => $message->created_at,
            ])->all(),
            'available_users' => $availableUsers,
        ];
    }

    public function getMessagesPayload(PrivateGroup $privateGroup, int $afterId): array
    {
        return $privateGroup->messages()
            ->with('user:id,name')
            ->when($afterId > 0, function ($query) use ($afterId) {
                $query->where('id', '>', $afterId);
            })
            ->orderBy('id')
            ->get()
            ->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->name,
                ],
                'created_at' => $message->created_at,
            ])
            ->all();
    }
}
