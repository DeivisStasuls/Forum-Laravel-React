<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ForumQueryService
{
    private const CACHE_TTL_SECONDS = 60;
    private const CACHE_VERSION_KEY = 'forum_cache_version';

    public function bumpForumCacheVersion(): void
    {
        $currentVersion = $this->getCacheVersion();
        Cache::forever(self::CACHE_VERSION_KEY, $currentVersion + 1);
    }

    public function getForumSubforums(string $search = ''): Collection
    {
        $normalizedSearch = trim($search);
        $cacheKey = $this->makeCacheKey('forum_subforums', [$normalizedSearch]);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($normalizedSearch) {
            return Subforum::withCount('threads')
                ->with(['moderators:id,name'])
                ->when($normalizedSearch !== '', function ($query) use ($normalizedSearch) {
                    $query->where(function ($innerQuery) use ($normalizedSearch) {
                        $innerQuery->where('name', 'like', "%{$normalizedSearch}%")
                            ->orWhere('description', 'like', "%{$normalizedSearch}%");
                    });
                })
                ->orderBy('name')
                ->get();
        });
    }

    public function getRecentThreads(string $search): Collection
    {
        $normalizedSearch = trim($search);
        $cacheKey = $this->makeCacheKey('recent_threads', [$normalizedSearch]);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($normalizedSearch) {
            return Thread::with(['user', 'subforum'])
                ->withCount('posts')
                ->when($normalizedSearch !== '', function ($query) use ($normalizedSearch) {
                    $query->where(function ($innerQuery) use ($normalizedSearch) {
                        $innerQuery->where('title', 'like', "%{$normalizedSearch}%")
                            ->orWhere('body', 'like', "%{$normalizedSearch}%")
                            ->orWhereHas('user', function ($userQuery) use ($normalizedSearch) {
                                $userQuery->where('name', 'like', "%{$normalizedSearch}%");
                            });
                        });
                })
                ->latest()
                ->take(20)
                ->get();
        });
    }

    public function getRecentPosts(): Collection
    {
        $cacheKey = $this->makeCacheKey('recent_posts');

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () {
            return Post::with(['user', 'thread'])
                ->latest()
                ->take(10)
                ->get();
        });
    }

    public function getForumStats(): array
    {
        $cacheKey = $this->makeCacheKey('forum_stats');

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () {
            return [
                'total_threads' => Thread::count(),
                'total_posts' => Post::count(),
                'total_subforums' => Subforum::count(),
                'total_users' => User::count(),
            ];
        });
    }

    public function getThreadCreateSubforums(): Collection
    {
        $cacheKey = $this->makeCacheKey('thread_create_subforums');

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () {
            return Subforum::query()
                ->with(['moderators:id,name'])
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'restricted_thread_creation']);
        });
    }

    public function getThreadForShow(
        string $slug,
        string $order = 'oldest',
        string $replySearch = '',
    ): Thread
    {
        $allowedOrders = ['oldest', 'latest', 'top_voted'];
        $resolvedOrder = in_array($order, $allowedOrders, true) ? $order : 'oldest';

        return Thread::where('slug', $slug)
            ->with([
                'user',
                'editor',
                'subforum',
                'posts' => function ($query) use ($resolvedOrder, $replySearch) {
                    $query->with(['user', 'editor'])
                        ->when($replySearch !== '', function ($searchQuery) use ($replySearch) {
                            $searchQuery->where(function ($innerQuery) use ($replySearch) {
                                $innerQuery
                                    ->where('body', 'like', "%{$replySearch}%")
                                    ->orWhereHas('user', function ($userQuery) use ($replySearch) {
                                        $userQuery->where('name', 'like', "%{$replySearch}%");
                                    });
                            });
                        })
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
        $normalizedSearch = trim($search);
        $cacheKey = $this->makeCacheKey('subforum_show', [
            $slug,
            $normalizedSearch,
            $resolvedOrder,
        ]);

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($slug, $normalizedSearch, $resolvedOrder) {
            return Subforum::where('slug', $slug)
                ->with(['moderators:id,name'])
                ->with(['threads' => function ($query) use ($normalizedSearch, $resolvedOrder) {
                    $query->with('user')
                        ->withCount('posts')
                        ->when($normalizedSearch !== '', function ($innerQuery) use ($normalizedSearch) {
                            $innerQuery->where(function ($filterQuery) use ($normalizedSearch) {
                                $filterQuery->where('title', 'like', "%{$normalizedSearch}%")
                                    ->orWhere('body', 'like', "%{$normalizedSearch}%")
                                    ->orWhereHas('user', function ($userQuery) use ($normalizedSearch) {
                                        $userQuery->where('name', 'like', "%{$normalizedSearch}%");
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
        });
    }

    private function getCacheVersion(): int
    {
        return (int) Cache::get(self::CACHE_VERSION_KEY, 1);
    }

    /**
     * @param array<int, string> $segments
     */
    private function makeCacheKey(string $prefix, array $segments = []): string
    {
        $version = $this->getCacheVersion();
        $keyBase = implode('|', $segments);

        return "forum:v{$version}:{$prefix}:".md5($keyBase);
    }
}
