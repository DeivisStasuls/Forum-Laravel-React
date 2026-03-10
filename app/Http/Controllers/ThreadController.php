<?php

namespace App\Http\Controllers;

use App\Http\Resources\Forum\RecentPostResource;
use App\Http\Resources\Forum\RecentThreadResource;
use App\Http\Resources\Forum\SubforumResource;
use App\Http\Resources\Forum\ThreadDetailResource;
use App\Models\Thread;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Services\ForumQueryService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

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
        $stats = $this->forumQueryService->getForumStats();

        return Inertia::render('Forum/Index', [
            'subforums' => SubforumResource::collection($subforums)->resolve(),
            'recentThreads' => RecentThreadResource::collection($recentThreads)->resolve(),
            'recentPosts' => RecentPostResource::collection($recentPosts)->resolve(),
            'stats' => $stats,
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

        return Inertia::render('Forum/CreateThread', [
            'subforums' => $subforums,
        ]);
    }

    /**
     * Store a newly created thread in storage.
     */
    public function store(StoreThreadRequest $request)
    {
        $thread = Thread::create([
            'title' => $request->title,
            'body' => $request->body,
            'creator_only_comments' => $request->boolean('creator_only_comments'),
            'user_id' => $request->user()->id,
            'subforum_id' => $request->subforum_id,
        ]);

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Discussion created successfully!');
    }

    /**
     * Display the specified thread with its posts.
     */
    public function show(string $slug, Request $request)
    {
        $thread = $this->forumQueryService->getThreadForShow($slug);

        $this->rememberRecentThread($request, $thread);

        return Inertia::render('Forum/ShowThread', [
            'thread' => (new ThreadDetailResource($thread))->resolve(),
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

        $thread->update([
            'title' => $request->title,
            'body' => $request->body,
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
        $thread->delete();

        return Redirect::route('subforums.show', $subforumSlug)
            ->with('success', 'Discussion deleted successfully!');
    }

}
