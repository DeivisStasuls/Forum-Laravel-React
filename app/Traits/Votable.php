<?php

namespace App\Traits;

use App\Models\Vote;

trait Votable
{
    public function votes()
    {
        return $this->morphMany(Vote::class, 'vottable');
    }

    // Optional: A helper to get the total score
    public function getScoreAttribute()
    {
        return $this->votes()->sum('value');
    }
}