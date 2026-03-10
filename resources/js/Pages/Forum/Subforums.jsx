import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function Subforums({ auth, subforums }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Categories
                    </h2>
                    {auth.user.role === 'admin' && (
                        <Link
                            href={route('subforums.create')}
                            className="rounded bg-emerald-600 px-4 py-2 font-bold text-white transition duration-150 hover:bg-emerald-700"
                        >
                            + New Category
                        </Link>
                    )}
                </div>
            }
        >
            <Head title="Categories" />

            <div className="min-h-screen bg-gray-200 py-6">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        {subforums.length > 0 ? (
                            <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                {subforums.map((subforum) => (
                                    <div key={subforum.id} className="p-6">
                                        <Link
                                            href={route(
                                                'subforums.show',
                                                subforum.slug,
                                            )}
                                            className="text-lg font-semibold text-gray-900 hover:text-indigo-600 dark:text-gray-100 dark:hover:text-indigo-400"
                                        >
                                            {subforum.name}
                                        </Link>
                                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {subforum.threads_count}{' '}
                                            {subforum.threads_count === 1
                                                ? 'discussion'
                                                : 'discussions'}
                                        </p>
                                        {subforum.description && (
                                            <p className="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                                {subforum.description}
                                            </p>
                                        )}
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="p-12 text-center text-gray-500 dark:text-gray-400">
                                No categories yet.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
