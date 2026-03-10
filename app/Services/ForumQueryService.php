<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Collection;

class ForumQueryService
{
    public function getForumSubforums(): Collection
    {
        return Subforum::withCount('threads')
            ->orderBy('name')
            ->get();
    }

    public function getRecentThreads(string $search): Collection
    {
        return Thread::with(['user', 'subforum'])
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
    }

    public function getRecentPosts(): Collection
    {
        return Post::with(['user', 'thread'])
            ->latest()
            ->take(10)
            ->get();
    }

    public function getForumStats(): array
    {
        return [
            'total_threads' => Thread::count(),
            'total_posts' => Post::count(),
            'total_subforums' => Subforum::count(),
            'total_users' => User::count(),
        ];
    }

    public function getThreadCreateSubforums(): Collection
    {
        return Subforum::query()->get(['id', 'name', 'slug']);
    }

    public function getThreadForShow(string $slug): Thread
    {
        return Thread::where('slug', $slug)
            ->with(['user', 'editor', 'subforum', 'posts.user', 'posts.editor'])
            ->withCount('posts')
            ->firstOrFail();
    }

    public function getSubforumForShow(string $slug, string $search): Subforum
    {
        return Subforum::where('slug', $slug)
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
    }
}
