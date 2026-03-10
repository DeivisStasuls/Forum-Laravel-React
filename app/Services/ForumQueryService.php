<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Subforum;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
            ->with(['user', 'subforum', 'posts.user'])
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

    public function mapSubforums(Collection $subforums): array
    {
        return $subforums->map(function ($subforum) {
            return [
                'id' => $subforum->id,
                'name' => $subforum->name,
                'slug' => $subforum->slug,
                'description' => $subforum->description,
                'threads_count' => $subforum->threads_count,
            ];
        })->all();
    }

    public function mapRecentThreads(Collection $threads): array
    {
        return $threads->map(function ($thread) {
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
        })->all();
    }

    public function mapRecentPosts(Collection $posts): array
    {
        return $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'body' => Str::limit($post->body, 100),
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
        })->all();
    }

    public function mapThread(Thread $thread): array
    {
        return [
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
            })->all(),
        ];
    }

    public function mapSubforumForShow(Subforum $subforum): array
    {
        return [
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
            })->all(),
        ];
    }
}
