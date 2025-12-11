<?php

namespace App\Http\Controllers;

use App\Models\Thread;
use App\Models\Subforum;
use App\Http\Requests\StoreThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ThreadController extends Controller
{
    /**
     * Display a listing of the threads, grouped by subforum.
     */
    public function index(Request $request)
    {
        // Fetch all subforums with their threads
        $subforums = Subforum::with(['threads' => function ($query) {
            $query->with('user')
                  ->withCount('posts')
                  ->latest()
                  ->take(10); // Limit threads per subforum for performance
        }])->get();

        // Transform subforums data for the frontend
        $subforumsData = $subforums->mapWithKeys(function ($subforum) {
            return [
                $subforum->name => [
                    'id' => $subforum->id,
                    'slug' => $subforum->slug,
                    'description' => $subforum->description,
                    'threads' => $subforum->threads->map(function ($thread) {
                        return [
                            'id' => $thread->id,
                            'title' => $thread->title,
                            'slug' => $thread->slug,
                            'user' => [
                                'id' => $thread->user->id,
                                'name' => $thread->user->name,
                            ],
                            'posts_count' => $thread->posts_count,
                            'created_at' => $thread->created_at,
                            'updated_at' => $thread->updated_at,
                        ];
                    }),
                ],
            ];
        })->toArray();

        return Inertia::render('Forum/Index', [
            'subforums' => $subforumsData,
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
            'user_id' => $request->user()->id,
            'subforum_id' => $request->subforum_id,
        ]);

        return Redirect::route('threads.show', $thread->slug)
            ->with('success', 'Thread created successfully!');
    }

    /**
     * Display the specified thread with its posts.
     */
    public function show(string $slug)
    {
        $thread = Thread::where('slug', $slug)
            ->with(['user', 'subforum', 'posts.user'])
            ->withCount('posts')
            ->firstOrFail();

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
            ->with('success', 'Thread updated successfully!');
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
            ->with('success', 'Thread deleted successfully!');
    }

}
