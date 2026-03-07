// resources/js/Pages/Forum/Index.jsx

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

export default function ForumIndex({ auth, subforums, recentThreads, recentPosts, stats }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Forum
                    </h2>
                    <Link
                        href={route('threads.create')}
                        className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-150"
                    >
                        + New Discussion
                    </Link>
                </div>
            }
        >
            <Head title="Forum" />

            {/* Holy Grail Layout */}
            <div className="min-h-screen bg-gray-200">
                <div className="container mx-auto px-4 py-6">
                    {/* Holy Grail Grid */}
                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        {/* Left Sidebar - Subforums */}
                        <aside className="lg:col-span-3">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-4">
                                <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                    Categories
                                </h3>
                                <nav className="space-y-2">
                                    {subforums.map((subforum) => (
                                        <Link
                                            key={subforum.id}
                                            href={route('subforums.show', subforum.slug)}
                                            className="block p-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                        >
                                            <div className="font-medium text-gray-900 dark:text-gray-100">
                                                {subforum.name}
                                            </div>
                                            <div className="text-sm text-gray-500 dark:text-gray-400">
                                                {subforum.threads_count}{' '}
                                                {subforum.threads_count === 1
                                                    ? 'discussion'
                                                    : 'discussions'}
                                            </div>
                                            {subforum.description && (
                                                <div className="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                                    {subforum.description}
                                                </div>
                                            )}
                                        </Link>
                                    ))}
                                </nav>
                            </div>
                        </aside>

                        {/* Main Content - Recent Threads */}
                        <main className="lg:col-span-6">
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                                <div className="p-6 border-b border-gray-200 dark:border-gray-700">
                                    <h3 className="text-xl font-bold text-gray-900 dark:text-gray-100">
                                        Recent Discussions
                                    </h3>
                                </div>
                                
                                {recentThreads.length > 0 ? (
                                    <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                        {recentThreads.map((thread) => (
                                            <div
                                                key={thread.id}
                                                className="p-6 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                                            >
                                                <div className="flex items-start justify-between">
                                                    <div className="flex-1 min-w-0">
                                                        <div className="flex items-center gap-2 mb-2">
                                                            <Link
                                                                href={route('subforums.show', thread.subforum.slug)}
                                                                className="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300"
                                                            >
                                                                {thread.subforum.name}
                                                            </Link>
                                                        </div>
                                                        <Link
                                                            href={route('threads.show', thread.slug)}
                                                            className="text-lg font-semibold text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 block mb-2"
                                                        >
                                                            {thread.title}
                                                        </Link>
                                                        <div className="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                                                            <span>
                                                                by{' '}
                                                                <span className="font-medium text-gray-700 dark:text-gray-300">
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
                                    <div className="p-12 text-center text-gray-500 dark:text-gray-400">
                                        <p className="text-lg mb-2">
                                            No discussions yet
                                        </p>
                                        <p className="text-sm">Be the first to start a discussion!</p>
                                    </div>
                                )}
                            </div>
                        </main>

                        {/* Right Sidebar - Stats & Recent Posts */}
                        <aside className="lg:col-span-3 space-y-6">
                            {/* Forum Statistics */}
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                    Forum Statistics
                                </h3>
                                <div className="space-y-3">
                                    <div className="flex justify-between items-center">
                                        <span className="text-gray-600 dark:text-gray-400">
                                            Discussions
                                        </span>
                                        <span className="font-bold text-gray-900 dark:text-gray-100">
                                            {stats.total_threads.toLocaleString()}
                                        </span>
                                    </div>
                                    <div className="flex justify-between items-center">
                                        <span className="text-gray-600 dark:text-gray-400">
                                            Comments
                                        </span>
                                        <span className="font-bold text-gray-900 dark:text-gray-100">
                                            {stats.total_posts.toLocaleString()}
                                        </span>
                                    </div>
                                    <div className="flex justify-between items-center">
                                        <span className="text-gray-600 dark:text-gray-400">
                                            Categories
                                        </span>
                                        <span className="font-bold text-gray-900 dark:text-gray-100">
                                            {stats.total_subforums.toLocaleString()}
                                        </span>
                                    </div>
                                    <div className="flex justify-between items-center">
                                        <span className="text-gray-600 dark:text-gray-400">Members</span>
                                        <span className="font-bold text-gray-900 dark:text-gray-100">
                                            {stats.total_users.toLocaleString()}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {/* Recent Posts */}
                            <div className="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                                <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4 pb-2 border-b border-gray-200 dark:border-gray-700">
                                    Recent Activity
                                </h3>
                                <div className="space-y-4">
                                    {recentPosts.map((post) => (
                                        <div key={post.id} className="border-l-2 border-indigo-500 pl-3">
                                            <Link
                                                href={route('threads.show', post.thread.slug)}
                                                className="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-indigo-400 block mb-1"
                                            >
                                                {post.thread.title}
                                            </Link>
                                            <p className="text-xs text-gray-500 dark:text-gray-400 mb-1">
                                                {post.body}
                                            </p>
                                            <div className="text-xs text-gray-400 dark:text-gray-500">
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
