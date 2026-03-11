<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * Determine whether the user can update the post.
     */
    public function update(User $user, Post $post): bool
    {
        // Allow if user is the owner OR if user is an admin
        return $user->id === $post->user_id || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the post.
     */
    public function delete(User $user, Post $post): bool
    {
        // Same logic: owner or admin
        return $user->id === $post->user_id || $user->isAdmin();
    }

    /**
     * Determine if the user can vote on the post.
     */
    public function vote(User $user, Post $post): bool
    {
        // Users can't vote on their own posts (common forum rule)
        // Or you can simply allow all authenticated users
        return $user->id !== $post->user_id;
    }
}