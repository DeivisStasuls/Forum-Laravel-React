// resources/js/Pages/Forum/Index.jsx

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useRef, useState } from 'react';

export default function ForumIndex({
    auth,
    subforums,
    recentThreads,
    recentPosts,
    filters,
}) {
    const [search, setSearch] = useState(filters?.search ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                route('forum.index'),
                { search },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 250);

        return () => clearTimeout(timeout);
    }, [search]);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title="Forum" />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="container mx-auto px-4 py-6">
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <aside className="lg:col-span-3">
                            <div className="sticky top-4 rounded-2xl border border-sky-200 bg-sky-50/90 p-6 shadow-sm backdrop-blur">
                                <h3 className="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-900">
                                    Categories
                                </h3>
                                <nav className="space-y-2">
                                    {subforums.map((subforum) => (
                                        <Link
                                            key={subforum.id}
                                            href={route('subforums.show', subforum.slug)}
                                            className="block rounded-xl border border-transparent p-3 transition-colors hover:border-slate-200 hover:bg-slate-50"
                                        >
                                            <div className="font-medium text-slate-900">
                                                {subforum.name}
                                            </div>
                                            <div className="text-sm text-slate-500">
                                                {subforum.threads_count}{' '}
                                                {subforum.threads_count === 1
                                                    ? 'discussion'
                                                    : 'discussions'}
                                            </div>
                                            {subforum.description && (
                                                <div className="mt-1 text-xs text-slate-400">
                                                    {subforum.description}
                                                </div>
                                            )}
                                        </Link>
                                    ))}
                                </nav>
                            </div>
                        </aside>

                        <main className="lg:col-span-6">
                            <div className="overflow-hidden rounded-2xl border border-sky-200 bg-sky-50/90 shadow-sm backdrop-blur">
                                <div className="border-b border-slate-200 p-6">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <h3 className="text-xl font-bold text-slate-900">
                                            Recent Discussions
                                        </h3>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={search}
                                                onChange={(e) =>
                                                    setSearch(e.target.value)
                                                }
                                                placeholder="Search discussions..."
                                                className="w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                                            />
                                        </div>
                                    </div>
                                </div>
                                
                                {recentThreads.length > 0 ? (
                                    <div className="divide-y divide-slate-200">
                                        {recentThreads.map((thread) => (
                                            <div
                                                key={thread.id}
                                                className="p-6 transition-colors hover:bg-slate-50"
                                            >
                                                <div className="flex items-start justify-between">
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2 mb-2">
                                                            <Link
                                                                href={route('subforums.show', thread.subforum.slug)}
                                                                className="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                                                            >
                                                                {thread.subforum.name}
                                                            </Link>
                                                        </div>
                                                        <Link
                                                            href={route('threads.show', thread.slug)}
                                                            className="mb-2 block text-lg font-semibold text-slate-900 hover:text-indigo-600"
                                                        >
                                                            {thread.title}
                                                        </Link>
                                                        <div className="flex items-center gap-4 text-sm text-slate-500">
                                                            <span>
                                                                by{' '}
                                                                <span className="font-medium text-slate-700">
                                                                    {thread.user.name}
                                                                </span>
                                                            </span>
                                                            <span>
                                                                {thread.posts_count} {thread.posts_count === 1 ? 'reply' : 'replies'}
                                                            </span>
                                                            <span>
                                                                {formatDistanceToNow(new Date(thread.created_at), { addSuffix: true })}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="p-12 text-center text-slate-500">
                                        <p className="text-lg mb-2">
                                            No discussions yet
                                        </p>
                                        <p className="text-sm">Be the first to start a discussion!</p>
                                    </div>
                                )}
                            </div>
                        </main>

                        <aside className="lg:col-span-3">
                            <div className="rounded-2xl border border-sky-200 bg-sky-50/90 p-6 shadow-sm backdrop-blur">
                                <h3 className="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-900">
                                    Recent Activity
                                </h3>
                                <div className="space-y-4">
                                    {recentPosts.map((post) => (
                                        <div key={post.id} className="rounded-lg border border-slate-200 bg-slate-50/70 p-3">
                                            <Link
                                                href={route('threads.show', post.thread.slug)}
                                                className="mb-1 block text-sm font-medium text-slate-900 hover:text-indigo-600"
                                            >
                                                {post.thread.title}
                                            </Link>
                                            <p className="mb-1 text-xs text-slate-600">
                                                {post.body}
                                            </p>
                                            <div className="text-xs text-slate-500">
                                                by {post.user.name} • {formatDistanceToNow(new Date(post.created_at), { addSuffix: true })}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
