<?php

namespace App\Services;

use App\Models\Vote;
use Illuminate\Foundation\Auth\User;

class VoteService
{
    /**
     * Cast a vote for a given votable model.
     *
     * @param User $user
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param int $value
     */
    public function castVote(User $user, $model, int $value): void
    {
        // Remove vote if value is 0 (undo)
        if ($value === 0) {
            $model->votes()->where('user_id', $user->id)->delete();
            return;
        }

        // Create or update vote
        $vote = $model->votes()->updateOrCreate(
            ['user_id' => $user->id],
            ['value' => $value]
        );
    }
}