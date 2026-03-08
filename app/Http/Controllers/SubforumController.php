<?php

namespace App\Http\Controllers;

use App\Models\Subforum;
use App\Http\Requests\StoreSubforumRequest;
use App\Http\Requests\UpdateSubforumRequest;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SubforumController extends Controller
{
    /**
     * Display a listing of all subforums.
     */
    public function index()
    {
        $subforums = Subforum::withCount('threads')
            ->orderBy('name')
            ->get();

        return Inertia::render('Forum/Subforums', [
            'subforums' => $subforums,
        ]);
    }

    /**
     * Show the form for creating a new subforum.
     */
    public function create()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Forum/CreateSubforum');
    }

    /**
     * Store a newly created subforum in storage.
     */
    public function store(StoreSubforumRequest $request)
    {
        $subforum = Subforum::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return Redirect::route('subforums.show', $subforum->slug)
            ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified subforum with its threads.
     */
    public function show(string $slug, Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $subforums = Subforum::withCount('threads')
            ->orderBy('name')
            ->get();

        $subforum = Subforum::where('slug', $slug)
            ->with(['threads' => function ($query) use ($search) {
                $query->with('user')
                    ->withCount('posts')
                    ->when($search !== '', function ($innerQuery) use ($search) {
                        $innerQuery->where(function ($filterQuery) use ($search) {
                            $filterQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('body', 'like', "%{$search}%");
                        });
                    })
                    ->latest();
            }])
            ->firstOrFail();

        return Inertia::render('Forum/ShowSubforum', [
            'subforums' => $subforums->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'slug' => $item->slug,
                    'description' => $item->description,
                    'threads_count' => $item->threads_count,
                ];
            }),
            'subforum' => [
                'id' => $subforum->id,
                'name' => $subforum->name,
                'description' => $subforum->description,
                'slug' => $subforum->slug,
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
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified subforum.
     */
    public function edit(string $slug)
    {
        $subforum = Subforum::where('slug', $slug)->firstOrFail();

        return Inertia::render('Forum/EditSubforum', [
            'subforum' => [
                'id' => $subforum->id,
                'name' => $subforum->name,
                'description' => $subforum->description,
                'slug' => $subforum->slug,
            ],
        ]);
    }

    /**
     * Update the specified subforum in storage.
     */
    public function update(UpdateSubforumRequest $request, string $slug)
    {
        $subforum = Subforum::where('slug', $slug)->firstOrFail();

        $subforum->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        // Regenerate slug if name changed
        if ($subforum->wasChanged('name')) {
            $subforum->slug = Subforum::generateSlug($request->name);
            $subforum->save();
        }

        return Redirect::route('subforums.show', $subforum->slug)
            ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified subforum from storage.
     */
    public function destroy(string $slug)
    {
        $subforum = Subforum::where('slug', $slug)->firstOrFail();
        $subforum->delete();

        return Redirect::route('forum.index')
            ->with('success', 'Category deleted successfully!');
    }
}


