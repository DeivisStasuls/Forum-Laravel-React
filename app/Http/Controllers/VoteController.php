<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, Thread $thread)
    {
        $this->authorize('vote', $thread);
        $this->performVote($request, $thread);

        return back()->with('success', 'Vote recorded!');
    }

    public function storePost(Request $request, string $threadSlug, Post $post)
    {
        $thread = Thread::where('slug', $threadSlug)->firstOrFail();
        abort_unless($post->thread_id === $thread->id, 404);

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
        $existingVote = $votable->votes()->where('user_id', $user->id)->first();

        if (! $existingVote) {
            $votable->votes()->create([
                'user_id' => $user->id,
                'value' => $voteValue,
            ]);

            return;
        }

        if ($existingVote->value === $voteValue) {
            $existingVote->delete();

            return;
        }

        $existingVote->update(['value' => $voteValue]);
    }
}