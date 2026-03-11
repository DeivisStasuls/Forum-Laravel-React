<?php

namespace App\Traits;

use App\Models\Vote;

trait Votable
{
    public function votes()
    {
        return $this->morphMany(Vote::class, 'votable'); // note: 'votable' matches your morph field
    }

    public function getScoreAttribute()
    {
        return $this->votes()->sum('value');
    }
    public function userVote($user)
    {
        return $this->votes()->where('user_id', $user->id)->first()?->value ?? 0;
    }

    public function getUserVoteAttribute(): int
    {
        if (! auth()->check()) {
            return 0;
        }

        return (int) $this->votes()->where('user_id', auth()->id())->value('value');
    }
}