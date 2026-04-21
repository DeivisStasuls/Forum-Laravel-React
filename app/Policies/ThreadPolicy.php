<?php

namespace App\Policies;

use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    public function create(User $user): bool
    {
        return ! $user->isBanned();
    }

    public function createInSubforum(User $user, Subforum $subforum): bool
    {
        if (! $subforum->restricted_thread_creation) {
            return true;
        }

        return $user->isAdmin() || $subforum->moderators()->where('users.id', $user->id)->exists();
    }

    public function update(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id || $user->isAdmin();
    }

    public function delete(User $user, Thread $thread): bool
    {
        return $user->id === $thread->user_id || $user->isAdmin();
    }

    public function vote(User $user, Thread $thread): bool
    {
        return $user->id !== $thread->user_id;
    }
}
