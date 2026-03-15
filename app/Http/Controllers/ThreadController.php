<?php

namespace App\Http\Controllers;

use App\Http\Resources\Forum\RecentPostResource;
use App\Http\Resources\Forum\RecentThreadResource;
use App\Http\Resources\Forum\SubforumResource;
use App\Http\Resources\Forum\ThreadDetailResource;
use App\Models\Subforum;
use App\Models\Thread;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Services\ForumQueryService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ThreadController extends Controller
{ 
    public function __construct(
        private readonly ForumQueryService $forumQueryService
    ) {
    }

    /**
     * Display a listing of the threads, grouped by subforum.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $subforums = $this->forumQueryService->getForumSubforums();
        $recentThreads = $this->forumQueryService->getRecentThreads($search);
        $recentPosts = $this->forumQueryService->getRecentPosts();

        return Inertia::render('Forum/Index', [
            'subforums' => SubforumResource::collection($subforums)->resolve(),
            'recentThreads' => RecentThreadResource::collection($recentThreads)->resolve(),
            'recentPosts' => RecentPostResource::collection($recentPosts)->resolve(),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new thread.
     */
    public function create()
    {
        $subforums = $this->forumQueryService->getThreadCreateSubforums();
        $user = auth()->user();

        return Inertia::render('Forum/CreateThread', [
            'subforums' => $subforums->map(fn (Subforum $subforum) => [
                'id' => $subforum->id,
                'name' => $subforum->name,
                'slug' => $subforum->slug,
                'restricted_thread_creation' => (bool) $subforum->restricted_thread_creation,
                'can_create_threads' => ! $subforum->restricted_thread_creation
                    || $user?->isAdmin()
                    || $subforum->moderators->contains('id', $user?->id),
            ])->values()->all(),
        ]);
    }

    /**
     * Store a newly created thread in storage.
     */
    public function store(StoreThreadRequest $request)
    {
        $subforum = Subforum::query()
            ->with('moderators:id')
            ->findOrFail((int) $request->subforum_id);

        if (
            $subforum->restricted_thread_creation
            && ! $request->user()->isAdmin()
            && ! $subforum->moderators->contains('id', $request->user()->id)
        ) {
            return back()->withErrors([
                'subforum_id' => 'You cannot create discussions in this category.',
            ]);
        }

        $imagePath = $request->hasFile('image')
            ? $request->file('image')->store('thread-images', 'public')
            : null;

        $thread = Thread::create([
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $imagePath,
            'creator_only_comments' => $request->boolean('creator_only_comments'),
            'user_id' => $request->user()->id,
            'subforum_id' => $subforum->id,
        ]);

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Discussion created successfully!');
    }

    /**
     * Display the specified thread with its posts.
     */
    public function show(string $slug, Request $request)
{
    $allowedOrders = ['oldest', 'latest', 'top_voted'];
    $order = trim((string) $request->query('order', 'oldest'));
    $resolvedOrder = in_array($order, $allowedOrders, true) ? $order : 'oldest';

    $thread = $this->forumQueryService->getThreadForShow($slug, $resolvedOrder);

    // Calculate vote scores for the thread
    $thread->score = $thread->score;

    // Calculate vote scores for each post (comment)
    $thread->posts->each(function ($post) {
        $post->score = $post->score;
    });

    $this->rememberRecentThread($request, $thread);

    return Inertia::render('Forum/ShowThread', [
        'thread' => (new ThreadDetailResource($thread))->resolve(),
        'filters' => [
            'order' => $resolvedOrder,
        ],
    ]);
}

    private function rememberRecentThread(Request $request, Thread $thread): void
    {
        $existing = collect($request->session()->get('recent_threads', []))
            ->reject(fn ($item) => (int) ($item['id'] ?? 0) === $thread->id)
            ->values();

        $updated = $existing
            ->prepend([
                'id' => $thread->id,
                'title' => $thread->title,
                'slug' => $thread->slug,
            ])
            ->take(5)
            ->values()
            ->all();

        $request->session()->put('recent_threads', $updated);
    }

    /**
     * Show the form for editing the specified thread.
     */
    public function edit(string $slug)
    {
        $thread = Thread::findThread($slug, true);


        // Check authorization
        if ($thread->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $subforums = $this->forumQueryService->getThreadCreateSubforums();

        return Inertia::render('Forum/EditThread', [
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'body' => $thread->body,
                'image_url' => $thread->image_url,
                'slug' => $thread->slug,
                'subforum_id' => $thread->subforum_id,
            ],
            'subforums' => $subforums,
        ]);
    }

    /**
     * Update the specified thread in storage.
     */
    public function update(UpdateThreadRequest $request, string $slug)
    {
        $thread = Thread::findThread($slug, true);
        // Check authorization
        if ($thread->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->boolean('remove_image') && $thread->image_path) {
            Storage::disk('public')->delete($thread->image_path);
            $thread->image_path = null;
        }

        if ($request->hasFile('image')) {
            if ($thread->image_path) {
                Storage::disk('public')->delete($thread->image_path);
            }
            $thread->image_path = $request->file('image')->store('thread-images', 'public');
        }

        $thread->update([
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $thread->image_path,
            'subforum_id' => $request->subforum_id,
            'edited_by_user_id' => $request->user()->id,
            'edited_at' => now(),
        ]);

        // Regenerate slug if title changed
        if ($thread->wasChanged('title')) {
            $thread->slug = Thread::generateSlug($request->title);
            $thread->save();
        }

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Discussion updated successfully!');
    }

    /**
     * Remove the specified thread from storage.
     */
    public function destroy(string $slug)
    {
        $thread = Thread::findThread($slug, true);

        // Check authorization - only author or admin can delete
        if ($thread->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $subforumSlug = $thread->subforum->slug;
        if ($thread->image_path) {
            Storage::disk('public')->delete($thread->image_path);
        }
        $thread->delete();

        return Redirect::route('subforums.show', $subforumSlug)
            ->with('success', 'Discussion deleted successfully!');
    }

}
