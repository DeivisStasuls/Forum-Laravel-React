<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Thread;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class PostController extends Controller
{
    /**
     * Store a newly created post in storage.
     */
    public function store(StorePostRequest $request, string $threadSlug)
    {
        $thread = Thread::where('slug', $threadSlug)->firstOrFail();

        $post = Post::create([
            'body' => $request->body,
            'user_id' => $request->user()->id,
            'thread_id' => $thread->id,
        ]);

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Reply posted successfully!');
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(string $threadSlug, int $postId)
    {
        $thread = Thread::where('slug', $threadSlug)->firstOrFail();
        $post = Post::where('id', $postId)
            ->where('thread_id', $thread->id)
            ->firstOrFail();

        // Check authorization
        if ($post->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Forum/EditPost', [
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'slug' => $thread->slug,
            ],
            'post' => [
                'id' => $post->id,
                'body' => $post->body,
            ],
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(UpdatePostRequest $request, string $threadSlug, int $postId)
    {
        $thread = Thread::where('slug', $threadSlug)->firstOrFail();
        $post = Post::where('id', $postId)
            ->where('thread_id', $thread->id)
            ->firstOrFail();

        // Check authorization
        if ($post->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $post->update([
            'body' => $request->body,
        ]);

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Reply updated successfully!');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(string $threadSlug, int $postId)
    {
        $thread = Thread::where('slug', $threadSlug)->firstOrFail();
        $post = Post::where('id', $postId)
            ->where('thread_id', $thread->id)
            ->firstOrFail();

        // Check authorization - only author or admin can delete
        if ($post->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $post->delete();

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Reply deleted successfully!');
    }
}


