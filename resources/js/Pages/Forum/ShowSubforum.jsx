import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useRef, useState } from 'react';

export default function ShowSubforum({ auth, subforum, subforums, filters }) {
    const [search, setSearch] = useState(filters?.search ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                route('subforums.show', subforum.slug),
                { search },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 250);

        return () => clearTimeout(timeout);
    }, [search, subforum.slug]);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                            {subforum.name}
                        </h2>
                        {subforum.description && (
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {subforum.description}
                            </p>
                        )}
                    </div>
                    <div className="flex items-center gap-2">
                        {auth.user.role === 'admin' && (
                            <Link
                                href={route('subforums.edit', subforum.slug)}
                                className="rounded bg-emerald-600 px-4 py-2 font-bold text-white transition duration-150 hover:bg-emerald-700"
                            >
                                Edit Category
                            </Link>
                        )}
                        <Link
                            href={route('threads.create')}
                            className="rounded bg-indigo-600 px-4 py-2 font-bold text-white transition duration-150 hover:bg-indigo-700"
                        >
                            + New Discussion
                        </Link>
                    </div>
                </div>
            }
        >
            <Head title={subforum.name} />

            <div className="min-h-screen bg-gray-200 py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('forum.index')}
                            className="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            ← Back to Forum
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                        <aside className="lg:col-span-3">
                            <div className="sticky top-4 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                                <h3 className="mb-4 border-b border-gray-200 pb-2 text-lg font-bold text-gray-900 dark:border-gray-700 dark:text-gray-100">
                                    Categories
                                </h3>
                                <nav className="space-y-2">
                                    {subforums.map((item) => (
                                        <Link
                                            key={item.id}
                                            href={route('subforums.show', item.slug)}
                                            className={`block rounded-lg p-3 transition-colors ${
                                                item.id === subforum.id
                                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300'
                                                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                                            }`}
                                        >
                                            <div className="font-medium text-gray-900 dark:text-gray-100">
                                                {item.name}
                                            </div>
                                            <div className="text-sm text-gray-500 dark:text-gray-400">
                                                {item.threads_count}{' '}
                                                {item.threads_count === 1
                                                    ? 'discussion'
                                                    : 'discussions'}
                                            </div>
                                            {item.description && (
                                                <div className="mt-1 text-xs text-gray-400 dark:text-gray-500">
                                                    {item.description}
                                                </div>
                                            )}
                                        </Link>
                                    ))}
                                </nav>
                            </div>
                        </aside>

                        <main className="lg:col-span-9">
                            <div className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                                <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                            Discussions in {subforum.name}
                                        </h3>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={search}
                                                onChange={(e) =>
                                                    setSearch(e.target.value)
                                                }
                                                placeholder="Search discussions..."
                                                className="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {subforum.threads.length > 0 ? (
                                    <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                        {subforum.threads.map((thread) => (
                                            <div
                                                key={thread.id}
                                                className="p-6 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                <Link
                                                    href={route('threads.show', thread.slug)}
                                                    className="text-lg font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                                >
                                                    {thread.title}
                                                </Link>
                                                <div className="mt-2 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                                    <span>
                                                        by{' '}
                                                        <span className="font-medium text-gray-700 dark:text-gray-300">
                                                            {thread.user.name}
                                                        </span>
                                                    </span>
                                                    <span>
                                                        {thread.posts_count}{' '}
                                                        {thread.posts_count === 1
                                                            ? 'comment'
                                                            : 'comments'}
                                                    </span>
                                                    <span>
                                                        {formatDistanceToNow(
                                                            new Date(
                                                                thread.created_at,
                                                            ),
                                                            { addSuffix: true },
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="p-12 text-center text-gray-500 dark:text-gray-400">
                                        <p className="text-lg">
                                            No discussions in this category yet
                                        </p>
                                        <p className="mt-2 text-sm">
                                            Start the first one to get this
                                            category active.
                                        </p>
                                    </div>
                                )}
                            </div>
                        </main>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
