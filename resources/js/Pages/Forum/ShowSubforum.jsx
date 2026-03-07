import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

export default function ShowSubforum({ auth, subforum }) {
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
                    <Link
                        href={route('threads.create')}
                        className="rounded bg-indigo-600 px-4 py-2 font-bold text-white transition duration-150 hover:bg-indigo-700"
                    >
                        + New Discussion
                    </Link>
                </div>
            }
        >
            <Head title={subforum.name} />

            <div className="min-h-screen bg-gray-100 py-6 dark:bg-gray-900">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('forum.index')}
                            className="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            ← Back to Forum
                        </Link>
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                Discussions in {subforum.name}
                            </h3>
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
                                                    new Date(thread.created_at),
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
                                    Start the first one to get this category
                                    active.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
