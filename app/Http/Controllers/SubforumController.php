<?php

namespace App\Http\Controllers;

use App\Http\Resources\Forum\SubforumDetailResource;
use App\Http\Resources\Forum\SubforumResource;
use App\Models\Subforum;
use App\Http\Requests\StoreSubforumRequest;
use App\Http\Requests\UpdateSubforumRequest;
use App\Services\ForumQueryService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SubforumController extends Controller
{
    public function __construct(
        private readonly ForumQueryService $forumQueryService
    ) {
    }

    /**
     * Display a listing of all subforums.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $subforums = $this->forumQueryService->getForumSubforums($search);

        return Inertia::render('Forum/Subforums', [
            'subforums' => SubforumResource::collection($subforums)->resolve(),
            'filters' => [
                'search' => $search,
            ],
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
            'restricted_thread_creation' => $request->boolean('restricted_thread_creation'),
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
        $order = trim((string) $request->query('order', 'latest'));
        $subforums = $this->forumQueryService->getForumSubforums();
        $subforum = $this->forumQueryService->getSubforumForShow($slug, $search, $order);

        $this->rememberRecentSubforum($request, $subforum);

        return Inertia::render('Forum/ShowSubforum', [
            'subforums' => SubforumResource::collection($subforums)->resolve(),
            'subforum' => (new SubforumDetailResource($subforum))->resolve(),
            'filters' => [
                'search' => $search,
                'order' => $order,
            ],
        ]);
    }

    private function rememberRecentSubforum(Request $request, Subforum $subforum): void
    {
        $existing = collect($request->session()->get('recent_subforums', []))
            ->reject(fn ($item) => (int) ($item['id'] ?? 0) === $subforum->id)
            ->values();

        $updated = $existing
            ->prepend([
                'id' => $subforum->id,
                'name' => $subforum->name,
                'slug' => $subforum->slug,
            ])
            ->take(5)
            ->values()
            ->all();

        $request->session()->put('recent_subforums', $updated);
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
                'restricted_thread_creation' => (bool) $subforum->restricted_thread_creation,
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
            'restricted_thread_creation' => $request->boolean('restricted_thread_creation'),
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


