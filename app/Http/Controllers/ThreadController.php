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
use App\Services\MediaStorageService;
use App\Services\RecentItemsService;
use App\Services\SlugService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ThreadController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly ForumQueryService $forumQueryService,
        private readonly MediaStorageService $mediaStorageService,
        private readonly SlugService $slugService,
        private readonly RecentItemsService $recentItemsService
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
                    || ($user && $user->isModeratorOf($subforum)),
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
        $this->authorize('createInSubforum', [Thread::class, $subforum]);

        $imagePath = $this->mediaStorageService->storeFromRequest($request, 'image', 'thread-images');

        $thread = Thread::create([
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $imagePath,
            'creator_only_comments' => $request->boolean('creator_only_comments'),
            'user_id' => $request->user()->id,
            'subforum_id' => $subforum->id,
        ]);
        $this->forumQueryService->bumpForumCacheVersion();

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
    $replySearch = trim((string) $request->query('reply_search', ''));

    $thread = $this->forumQueryService->getThreadForShow(
        $slug,
        $resolvedOrder,
        $replySearch,
    );

    // Calculate vote scores for the thread
    $thread->score = $thread->score;

    // Calculate vote scores for each post (comment)
    $thread->posts->each(function ($post) {
        $post->score = $post->score;
    });

    $this->recentItemsService->remember(
        $request,
        'recent_threads',
        [
            'id' => $thread->id,
            'title' => $thread->title,
            'slug' => $thread->slug,
        ],
        $thread->id
    );

    return Inertia::render('Forum/ShowThread', [
        'thread' => (new ThreadDetailResource($thread))->resolve(),
        'filters' => [
            'order' => $resolvedOrder,
            'reply_search' => $replySearch,
        ],
    ]);
}

    /**
     * Show the form for editing the specified thread.
     */
    public function edit(string $slug)
    {
        $thread = Thread::findThread($slug, true);
        $this->authorize('update', $thread);

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
        $this->authorize('update', $thread);
        $thread->image_path = $this->mediaStorageService->resolveImagePathForUpdate(
            $request,
            'image',
            'thread-images',
            $thread->image_path
        );

        $thread->update([
            'title' => $request->title,
            'body' => $request->body,
            'image_path' => $thread->image_path,
            'subforum_id' => $request->subforum_id,
            'edited_by_user_id' => $request->user()->id,
            'edited_at' => now(),
        ]);

        $this->slugService->refreshSlugIfChanged(
            $thread,
            'title',
            Thread::generateSlug($request->title)
        );
        $this->forumQueryService->bumpForumCacheVersion();

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Discussion updated successfully!');
    }

    /**
     * Remove the specified thread from storage.
     */
    public function destroy(string $slug)
    {
        $thread = Thread::findThread($slug, true);
        $this->authorize('delete', $thread);

        $subforumSlug = $thread->subforum->slug;
        $this->mediaStorageService->deletePublicFile($thread->image_path);
        $thread->delete();
        $this->forumQueryService->bumpForumCacheVersion();

        return Redirect::route('subforums.show', $subforumSlug)
            ->with('success', 'Discussion deleted successfully!');
    }

}
