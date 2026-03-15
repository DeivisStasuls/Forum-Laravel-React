import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import MarkdownText from '@/Components/MarkdownText';
import { Head, Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

export default function MyPosts({ auth, posts }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        My Posts
                    </h2>
                    <Link
                        href={route('forum.index')}
                        className="rounded bg-indigo-600 px-4 py-2 text-sm font-bold text-white transition duration-150 hover:bg-indigo-700"
                    >
                        Back to Forum
                    </Link>
                </div>
            }
        >
            <Head title="My Posts" />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    {posts.length > 0 ? (
                        <div className="space-y-4">
                            {posts.map((post) => (
                                <article
                                    key={post.id}
                                    className="rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800"
                                >
                                    <div className="mb-2 flex flex-wrap items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                        <Link
                                            href={route('subforums.show', post.subforum.slug)}
                                            className="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            {post.subforum.name}
                                        </Link>
                                        <span>-</span>
                                        <Link
                                            href={route('threads.show', post.thread.slug)}
                                            className="font-medium text-gray-700 hover:text-indigo-700 dark:text-gray-200 dark:hover:text-indigo-300"
                                        >
                                            {post.thread.title}
                                        </Link>
                                    </div>

                                           <MarkdownText
                                               content={post.preview}
                                               className="mb-3 text-sm text-gray-800 dark:text-gray-200"
                                           />

                                    <div className="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                                        <span>
                                            Posted{' '}
                                            {formatDistanceToNow(
                                                new Date(post.created_at),
                                                {
                                                    addSuffix: true,
                                                },
                                            )}
                                        </span>
                                        {post.edited_at && (
                                            <span>
                                                Edited{' '}
                                                {formatDistanceToNow(
                                                    new Date(post.edited_at),
                                                    { addSuffix: true },
                                                )}
                                            </span>
                                        )}
                                        <span>
                                            Score:{' '}
                                            <span className="font-semibold text-gray-700 dark:text-gray-200">
                                                {post.score}
                                            </span>
                                        </span>
                                        <Link
                                            href={route('posts.edit', [
                                                post.thread.slug,
                                                post.id,
                                            ])}
                                            className="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            Edit
                                        </Link>
                                        <Link
                                            href={route('threads.show', post.thread.slug)}
                                            className="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                                        >
                                            Open Thread
                                        </Link>
                                    </div>
                                </article>
                            ))}
                        </div>
                    ) : (
                        <div className="rounded-lg bg-white p-10 text-center shadow-sm dark:bg-gray-800">
                            <p className="text-lg font-semibold text-gray-800 dark:text-gray-100">
                                You have not posted anything yet.
                            </p>
                            <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                Join a discussion and your comments will appear
                                here.
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
