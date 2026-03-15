<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function store(Request $request, Thread $thread)
    {
        $this->authorize('vote', $thread);
        $this->performVote($request, $thread);

        return back()->with('success', 'Vote recorded!');
    }

    public function storePost(Request $request, string $threadSlug, Post $post)
    {
        $this->authorize('vote', $post);
        $this->performVote($request, $post);

        return back()->with('success', 'Vote recorded!');
    }

    /**
     * Core voting logic for thread or post.
     */
    private function performVote(Request $request, $votable)
    {
        $validated = $request->validate([
            'vote' => ['required', 'integer', 'in:1,-1'],
        ]);
        $voteValue = (int) $validated['vote'];

        $user = $request->user();

        // If user already voted, update it; otherwise, create new vote
        $existingVote = $votable->votes()->where('user_id', $user->id)->first();

        if ($existingVote) {
            if ($existingVote->value === $voteValue) {
                // Same vote again → remove vote (toggle)
                $existingVote->delete();
            } else {
                // Change vote direction
                $existingVote->update(['value' => $voteValue]);
            }
        } else {
            $votable->votes()->create([
                'user_id' => $user->id,
                'value' => $voteValue,
            ]);
        }
    }
}