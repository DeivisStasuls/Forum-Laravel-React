<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Collection;

class ForumQueryService
{
    public function getForumSubforums(string $search = ''): Collection
    {
        return Subforum::withCount('threads')
            ->with(['moderators:id,name'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
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
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%");
                        });
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
        return Subforum::query()
            ->with(['moderators:id,name'])
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'restricted_thread_creation']);
    }

    public function getThreadForShow(string $slug, string $order = 'oldest'): Thread
    {
        $allowedOrders = ['oldest', 'latest', 'top_voted'];
        $resolvedOrder = in_array($order, $allowedOrders, true) ? $order : 'oldest';

        return Thread::where('slug', $slug)
            ->with([
                'user',
                'editor',
                'subforum',
                'posts' => function ($query) use ($resolvedOrder) {
                    $query->with(['user', 'editor'])
                        ->when(
                            $resolvedOrder === 'top_voted',
                            fn ($orderedQuery) => $orderedQuery
                                ->withSum('votes as score_sum', 'value')
                                ->orderByDesc('score_sum')
                                ->oldest(),
                        )
                        ->when(
                            $resolvedOrder === 'latest',
                            fn ($orderedQuery) => $orderedQuery->latest(),
                        )
                        ->when(
                            $resolvedOrder === 'oldest',
                            fn ($orderedQuery) => $orderedQuery->oldest(),
                        );
                },
            ])
            ->withCount('posts')
            ->firstOrFail();
    }

    public function getSubforumForShow(string $slug, string $search, string $order): Subforum
    {
        $allowedOrders = ['latest', 'oldest', 'most_commented'];
        $resolvedOrder = in_array($order, $allowedOrders, true) ? $order : 'latest';

        return Subforum::where('slug', $slug)
            ->with(['moderators:id,name'])
            ->with(['threads' => function ($query) use ($search, $resolvedOrder) {
                $query->with('user')
                    ->withCount('posts')
                    ->when($search !== '', function ($innerQuery) use ($search) {
                        $innerQuery->where(function ($filterQuery) use ($search) {
                            $filterQuery->where('title', 'like', "%{$search}%")
                                ->orWhere('body', 'like', "%{$search}%")
                                ->orWhereHas('user', function ($userQuery) use ($search) {
                                    $userQuery->where('name', 'like', "%{$search}%");
                                });
                        });
                    })
                    ->when(
                        $resolvedOrder === 'most_commented',
                        fn ($orderedQuery) => $orderedQuery->orderByDesc('posts_count')->latest(),
                    )
                    ->when(
                        $resolvedOrder === 'oldest',
                        fn ($orderedQuery) => $orderedQuery->oldest(),
                    )
                    ->when(
                        $resolvedOrder === 'latest',
                        fn ($orderedQuery) => $orderedQuery->latest(),
                    );
            }])
            ->firstOrFail();
    }
}
