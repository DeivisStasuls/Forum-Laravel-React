<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Subforum;
use App\Models\Post;
use App\Models\User;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

class ThreadController extends Controller
{
    /**
     * Display a listing of the threads, grouped by subforum.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        // Fetch all subforums for left sidebar
        $subforums = Subforum::withCount('threads')
            ->orderBy('name')
            ->get();

        // Fetch recent threads for main content
        $recentThreads = Thread::with(['user', 'subforum'])
            ->withCount('posts')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->take(20)
            ->get();

        // Fetch recent posts for right sidebar
        $recentPosts = Post::with(['user', 'thread'])
            ->latest()
            ->take(10)
            ->get();

        // Calculate forum statistics
        $stats = [
            'total_threads' => Thread::count(),
            'total_posts' => Post::count(),
            'total_subforums' => Subforum::count(),
            'total_users' => User::count(),
        ];

        return Inertia::render('Forum/Index', [
            'subforums' => $subforums->map(function ($subforum) {
                return [
                    'id' => $subforum->id,
                    'name' => $subforum->name,
                    'slug' => $subforum->slug,
                    'description' => $subforum->description,
                    'threads_count' => $subforum->threads_count,
                ];
            }),
            'recentThreads' => $recentThreads->map(function ($thread) {
                return [
                    'id' => $thread->id,
                    'title' => $thread->title,
                    'slug' => $thread->slug,
                    'user' => [
                        'id' => $thread->user->id,
                        'name' => $thread->user->name,
                    ],
                    'subforum' => [
                        'id' => $thread->subforum->id,
                        'name' => $thread->subforum->name,
                        'slug' => $thread->subforum->slug,
                    ],
                    'posts_count' => $thread->posts_count,
                    'created_at' => $thread->created_at,
                    'updated_at' => $thread->updated_at,
                ];
            }),
            'recentPosts' => $recentPosts->map(function ($post) {
                return [
                    'id' => $post->id,
                    'body' => \Str::limit($post->body, 100),
                    'user' => [
                        'id' => $post->user->id,
                        'name' => $post->user->name,
                    ],
                    'thread' => [
                        'id' => $post->thread->id,
                        'title' => $post->thread->title,
                        'slug' => $post->thread->slug,
                    ],
                    'created_at' => $post->created_at,
                ];
            }),
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
        $subforums = Subforum::all(['id', 'name', 'slug']);

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
        $thread = Thread::where('slug', $slug)
            ->with(['user', 'subforum', 'posts.user'])
            ->withCount('posts')
            ->firstOrFail();

        $this->rememberRecentThread($request, $thread);

        return Inertia::render('Forum/ShowThread', [
            'thread' => [
                'id' => $thread->id,
                'title' => $thread->title,
                'body' => $thread->body,
                'slug' => $thread->slug,
                'user' => [
                    'id' => $thread->user->id,
                    'name' => $thread->user->name,
                ],
                'subforum' => [
                    'id' => $thread->subforum->id,
                    'name' => $thread->subforum->name,
                    'slug' => $thread->subforum->slug,
                ],
                'posts_count' => $thread->posts_count,
                'creator_only_comments' => $thread->creator_only_comments,
                'created_at' => $thread->created_at,
                'updated_at' => $thread->updated_at,
                'posts' => $thread->posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'body' => $post->body,
                        'user' => [
                            'id' => $post->user->id,
                            'name' => $post->user->name,
                        ],
                        'created_at' => $post->created_at,
                        'updated_at' => $post->updated_at,
                    ];
                }),
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

        $subforums = Subforum::all(['id', 'name', 'slug']);

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
