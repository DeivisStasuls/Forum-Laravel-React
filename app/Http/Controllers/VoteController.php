<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoteController extends Controller
{
    /**
     * Vote on a thread.
     */
    public function store(Request $request, \App\Models\Thread $thread)
{
    $user = $request->user();

    // Prevent self-voting
    if ($thread->user_id === $user->id) {
        return back()->with('error', "You cannot vote on your own thread.");
    }

    $voteValue = (int) $request->input('vote');

    // Create or update the vote
    $thread->votes()->updateOrCreate(
        ['user_id' => $user->id],
        ['value' => $voteValue]
    );

    return back()->with('success', 'Vote recorded!');
}

    /**
     * Vote on a post.
     */
    public function storePost(Request $request, \App\Models\Post $post)
{
    $user = $request->user();

    if ($post->user_id === $user->id) {
        return back()->with('error', "You cannot vote on your own post.");
    }

    $voteValue = (int) $request->input('vote');

    $post->votes()->updateOrCreate(
        ['user_id' => $user->id],
        ['value' => $voteValue]
    );

    return back()->with('success', 'Vote recorded!');
}

    /**
     * Core voting logic for thread or post.
     */
    private function performVote(Request $request, $votable)
    {
        $voteValue = (int) $request->vote;

        if (! in_array($voteValue, [1, -1])) {
            abort(400, 'Invalid vote value.');
        }

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