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
        // Only admins can create subforums (handled by middleware)
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
            ->with('success', 'Subforum created successfully!');
    }

    /**
     * Display the specified subforum with its threads.
     */
    public function show(string $slug, Request $request)
    {
        $subforum = Subforum::where('slug', $slug)
            ->with(['threads' => function ($query) {
                $query->with('user')
                      ->withCount('posts')
                      ->latest();
            }])
            ->firstOrFail();

        return Inertia::render('Forum/ShowSubforum', [
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
            ->with('success', 'Subforum updated successfully!');
    }

    /**
     * Remove the specified subforum from storage.
     */
    public function destroy(string $slug)
    {
        $subforum = Subforum::where('slug', $slug)->firstOrFail();
        $subforum->delete();

        return Redirect::route('forum.index')
            ->with('success', 'Subforum deleted successfully!');
    }
}


