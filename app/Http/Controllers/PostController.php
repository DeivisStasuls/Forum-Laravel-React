<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Models\Post;
use App\Models\Thread;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Services\ForumQueryService;
use App\Services\MediaStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PostController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ForumQueryService $forumQueryService,
        private readonly MediaStorageService $mediaStorageService
    ) {
    }

    public function myPosts(Request $request)
    {
        $posts = Post::with(['thread.subforum'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->take(50)
            ->get()
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'body' => $post->body,
                'preview' => Str::limit($post->body, 180),
                'image_url' => $post->image_url,
                'score' => $post->score,
                'user_vote' => $post->user_vote,
                'created_at' => $post->created_at,
                'edited_at' => $post->edited_at,
                'thread' => [
                    'id' => $post->thread->id,
                    'slug' => $post->thread->slug,
                    'title' => $post->thread->title,
                ],
                'subforum' => [
                    'slug' => $post->thread->subforum->slug,
                    'name' => $post->thread->subforum->name,
                ],
            ])
            ->values();

        return Inertia::render('Forum/MyPosts', [
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(StorePostRequest $request, string $threadSlug)
    {
        $thread = Thread::findThread($threadSlug, true);
        $this->authorize('createInThread', [Post::class, $thread]);

        $imagePath = $this->mediaStorageService->storeFromRequest($request, 'image', 'post-images');

        Post::create([
            'body' => $request->body,
            'image_path' => $imagePath,
            'user_id' => $request->user()->id,
            'thread_id' => $thread->id,
        ]);
        $this->forumQueryService->bumpForumCacheVersion();

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Comment posted successfully!');
    }

    /**
     * Show the form for editing the specified post.
     */

    public function edit(string $threadSlug, int $postId)
{
    [$thread, $post] = $this->getPost($threadSlug, $postId);
    $this->authorize('update', $post);

    return Inertia::render('Forum/EditPost', [
        'thread' => [
            'id' => $thread->id,
            'title' => $thread->title,
            'slug' => $thread->slug,
        ],
        'post' => [
            'id' => $post->id,
            'body' => $post->body,
            'image_url' => $post->image_url,
        ],
    ]);
}


    /**
     * Update the specified post in storage.
     */
    public function update(UpdatePostRequest $request, string $threadSlug, int $postId)
{
    [$thread, $post] = $this->getPost($threadSlug, $postId);
    $this->authorize('update', $post);
    $post->image_path = $this->mediaStorageService->resolveImagePathForUpdate(
        $request,
        'image',
        'post-images',
        $post->image_path
    );

    $post->update([
        'body' => $request->body,
        'image_path' => $post->image_path,
        'edited_by_user_id' => $request->user()->id,
        'edited_at' => now(),
    ]);
    $this->forumQueryService->bumpForumCacheVersion();

    return Redirect::route('threads.show', $thread->slug)
        ->with('success', 'Comment updated successfully!');
}

public function destroy(string $threadSlug, int $postId)
    {
        [$thread, $post] = $this->getPost($threadSlug, $postId);

        // Use policy to check authorization
        $this->authorize('delete', $post);

        $this->mediaStorageService->deletePublicFile($post->image_path);

        $post->delete();
        $this->forumQueryService->bumpForumCacheVersion();

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Comment deleted successfully!');
    }

    private function getPost(string $threadSlug, int $postId)
{
    $thread = Thread::findThread($threadSlug, true);
    $post = Post::where('id', $postId)
        ->where('thread_id', $thread->id)
        ->firstOrFail();
    return [$thread, $post];
}

}


