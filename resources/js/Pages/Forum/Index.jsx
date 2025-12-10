// resources/js/Pages/Forum/Index.jsx

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

export default function ForumIndex({ auth, subforums }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Forum Dashboard</h2>}
        >
            <Head title="Forum" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">

                            <h3 className="text-2xl font-bold mb-6 border-b pb-2">🌐 Forum Subforums</h3>

                            {/* Link to create a new thread */}
                            <div className="flex justify-end mb-4">
                                <Link
                                    href={route('threads.create')}
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-150"
                                >
                                    + Create New Thread
                                </Link>
                            </div>

                            {/* Loop through the subforums object */}
                            {Object.entries(subforums).map(([subforumName, subforumData]) => (
                                <div key={subforumName} className="mb-8">
                                    <div className="bg-gray-100 dark:bg-gray-700 p-4 rounded-t-lg shadow-inner">
                                        <h4 className="text-xl font-semibold text-indigo-600 dark:text-indigo-400">{subforumName}</h4>
                                        <p className="text-sm text-gray-600 dark:text-gray-300">{subforumData.description}</p>
                                    </div>

                                    {/* Thread List Table/Container */}
                                    <div className="overflow-x-auto">
                                        {subforumData.threads.length > 0 ? (
                                            <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                <thead className="bg-gray-50 dark:bg-gray-700">
                                                    <tr>
                                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-3/5">
                                                            Thread Title
                                                        </th>
                                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">
                                                            Author
                                                        </th>
                                                        <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/5">
                                                            Last Post
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                                    {subforumData.threads.map((thread) => (
                                                        <tr key={thread.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-100">
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                                                <Link href={route('threads.show', thread.slug)} className="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">
                                                                    {thread.title}
                                                                </Link>
                                                            </td>
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                {/* Assumes thread.user is loaded via eager loading */}
                                                                {thread.user.name}
                                                            </td>
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                                {/* Uses date-fns for relative time formatting */}
                                                                {formatDistanceToNow(new Date(thread.created_at), { addSuffix: true })}
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        ) : (
                                            <div className="p-4 text-center bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                                                No threads in this subforum yet. Be the first to start a discussion!
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))}

                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}