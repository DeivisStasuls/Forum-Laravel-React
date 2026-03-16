import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useRef, useState } from 'react';

export default function ShowSubforum({ auth, subforum, subforums, filters }) {
    const [search, setSearch] = useState(filters?.search ?? '');
    const [order, setOrder] = useState(filters?.order ?? 'latest');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                route('subforums.show', subforum.slug),
                { search, order },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 250);

        return () => clearTimeout(timeout);
    }, [search, order, subforum.slug]);

    return (
        <AuthenticatedLayout user={auth.user}>
            <Head title={subforum.name} />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('forum.index')}
                            className="text-sm text-indigo-600 hover:text-indigo-800"
                        >
                            ← Back to Forum
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-12">
                        <aside className="order-2 lg:order-1 lg:col-span-3">
                            <div className="rounded-2xl border border-sky-200 bg-sky-50/90 p-6 shadow-sm backdrop-blur lg:sticky lg:top-4">
                                <h3 className="mb-4 border-b border-slate-200 pb-2 text-lg font-bold text-slate-900">
                                    Categories
                                </h3>
                                <nav className="space-y-2">
                                    {subforums.map((item) => (
                                        <Link
                                            key={item.id}
                                            href={route('subforums.show', item.slug)}
                                            className={`block rounded-lg p-3 transition-colors ${
                                                item.id === subforum.id
                                                    ? 'bg-indigo-50 text-indigo-700'
                                                    : 'hover:bg-slate-50'
                                            }`}
                                        >
                                            <div className="font-medium text-slate-900">
                                                {item.name}
                                            </div>
                                            <div className="text-sm text-slate-500">
                                                {item.threads_count}{' '}
                                                {item.threads_count === 1
                                                    ? 'discussion'
                                                    : 'discussions'}
                                            </div>
                                            {item.description && (
                                                <div className="mt-1 text-xs text-slate-400">
                                                    {item.description}
                                                </div>
                                            )}
                                        </Link>
                                    ))}
                                </nav>
                            </div>
                        </aside>

                        <main className="order-1 lg:order-2 lg:col-span-6">
                            <div className="overflow-hidden rounded-2xl border border-sky-200 bg-sky-50/90 shadow-sm backdrop-blur">
                                <div className="border-b border-slate-200 p-6">
                                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                        <div className="flex items-center gap-2">
                                            <label
                                                htmlFor="order"
                                                className="text-sm font-semibold text-slate-700"
                                            >
                                                Order by
                                            </label>
                                            <select
                                                id="order"
                                                value={order}
                                                onChange={(e) =>
                                                    setOrder(e.target.value)
                                                }
                                                className="rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            >
                                                <option value="latest">
                                                    Newest
                                                </option>
                                                <option value="oldest">
                                                    Oldest
                                                </option>
                                                <option value="most_commented">
                                                    Most Commented
                                                </option>
                                            </select>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={search}
                                                onChange={(e) =>
                                                    setSearch(e.target.value)
                                                }
                                                placeholder="Search discussions..."
                                                className="w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                                            />
                                        </div>
                                    </div>
                                </div>

                                {subforum.threads.length > 0 ? (
                                    <div className="divide-y divide-slate-200">
                                        {subforum.threads.map((thread) => (
                                            <div
                                                key={thread.id}
                                                className="p-6 transition-colors hover:bg-slate-50"
                                            >
                                                <Link
                                                    href={route('threads.show', thread.slug)}
                                                    className="text-lg font-semibold text-slate-900 hover:text-indigo-600"
                                                >
                                                    {thread.title}
                                                </Link>
                                                <div className="mt-2 flex flex-wrap items-center gap-4 text-sm text-slate-500">
                                                    <span>
                                                        by{' '}
                                                        <span className="font-medium text-slate-700">
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
                                    <div className="p-12 text-center text-slate-500">
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

                        <aside className="order-3 lg:col-span-3">
                            <div className="space-y-4 lg:sticky lg:top-4">
                                <div className="rounded-2xl border border-sky-200 bg-sky-50/90 p-6 shadow-sm backdrop-blur">
                                    <h3 className="mb-2 text-lg font-bold text-slate-900">
                                        {subforum.name}
                                    </h3>
                                    {subforum.is_moderator && (
                                        <div className="mb-2 inline-flex rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700">
                                            You are a moderator in this category
                                        </div>
                                    )}
                                    <p className="text-sm text-slate-600">
                                        {subforum.description || 'No description provided yet.'}
                                    </p>
                                    <div className="mt-3">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            Moderators
                                        </p>
                                        {subforum.moderators.length > 0 ? (
                                            <div className="mt-2 flex flex-wrap gap-2">
                                                {subforum.moderators.map(
                                                    (moderator) => (
                                                        <span
                                                            key={moderator.id}
                                                            className="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700"
                                                        >
                                                            {moderator.name}
                                                        </span>
                                                    ),
                                                )}
                                            </div>
                                        ) : (
                                            <p className="mt-1 text-xs text-slate-500">
                                                No moderators assigned.
                                            </p>
                                        )}
                                    </div>
                                    <div className="mt-4 text-xs text-slate-500">
                                        {subforum.threads.length}{' '}
                                        {subforum.threads.length === 1
                                            ? 'discussion'
                                            : 'discussions'}{' '}
                                        shown
                                    </div>
                                </div>

                                <div className="rounded-2xl border border-sky-200 bg-sky-50/90 p-4 shadow-sm backdrop-blur">
                                    <div className="flex flex-col gap-2">
                                        <Link
                                            href={route('threads.create')}
                                            className="w-full rounded-full bg-indigo-600 px-4 py-2 text-center text-sm font-semibold text-white transition hover:bg-indigo-500"
                                        >
                                            + Create Discussion
                                        </Link>
                                        {auth.user.role === 'admin' && (
                                            <Link
                                                href={route('subforums.edit', subforum.slug)}
                                                className="w-full rounded-full border border-slate-300 bg-white px-4 py-2 text-center text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Edit Category
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
