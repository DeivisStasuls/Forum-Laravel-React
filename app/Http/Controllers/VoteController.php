<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use App\Services\VoteService;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function __construct(protected VoteService $service) {}

    public function store(Request $request, Thread $thread)
    {
        $this->authorize('vote', $thread);
        $this->performVote($request, $thread);
        return back();
    }

    public function storePost(Request $request, string $threadSlug, Post $post)
    {
        $this->authorize('vote', $post);
        $this->performVote($request, $post);
        return back();
    }

    private function performVote(Request $request, $model)
    {
        $validated = $request->validate(['value' => 'required|in:1,-1']);
        $this->service->castVote($request->user(), $model, (int)$validated['value']);
    }
}