<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    public function createInThread(User $user, Thread $thread): Response
    {
        if (! $thread->creator_only_comments || $thread->user_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('Only the discussion creator can comment on this discussion.');
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    public function vote(User $user, Post $post): bool
    {
        return $user->id !== $post->user_id;
    }
}