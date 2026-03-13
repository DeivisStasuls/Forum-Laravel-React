<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use App\Services\VoteService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; 

class VoteController extends Controller
{
    use AuthorizesRequests; 

    public function __construct(protected VoteService $service) {}

    public function store(Request $request, Thread $thread)
{
    $user = $request->user();

    // Prevent user from voting on their own thread
    if ($thread->user_id === $user->id) {
        return back()->with('error', "You cannot upvote your own thread.");
    }

    // existing vote logic...
    $thread->votes()->updateOrCreate(
        ['user_id' => $user->id],
        ['vote' => 1] // or -1 for downvote
    );

    return back()->with('success', 'Vote recorded!');
}

    public function storePost(Request $request, string $threadSlug, Post $post)
    {
        $this->authorize('vote', $post); 
        $this->performVote($request, $post);
        return back();
    }

    private function performVote(Request $request, $model)
{
    $this->authorize('vote', $model); // use $model here
    $validated = $request->validate(['value' => 'required|in:1,-1']);
    $this->service->castVote($request->user(), $model, (int)$validated['value']);
}
}