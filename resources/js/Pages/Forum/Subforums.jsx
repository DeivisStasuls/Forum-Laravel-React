import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';

export default function Subforums({ auth, subforums, filters }) {
    const [search, setSearch] = useState(filters?.search ?? '');
    const isFirstRender = useRef(true);

    useEffect(() => {
        if (isFirstRender.current) {
            isFirstRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                route('subforums.index'),
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
            <Head title="Categories" />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-2xl border border-sky-200 bg-sky-50/90 shadow-sm backdrop-blur">
                        <div className="border-b border-slate-200 p-6">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 className="text-lg font-bold text-slate-900">
                                        Categories
                                    </h2>
                                    <p className="mt-1 text-sm text-slate-600">
                                        Explore all discussion sections.
                                    </p>
                                </div>
                                <div className="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-center">
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(e) => setSearch(e.target.value)}
                                        placeholder="Search categories..."
                                        className="w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-64"
                                    />
                                    {auth.user.role === 'admin' && (
                                        <Link
                                            href={route('subforums.create')}
                                            className="whitespace-nowrap rounded-full bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-500"
                                        >
                                            + New Category
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </div>
                        {subforums.length > 0 ? (
                            <div className="divide-y divide-slate-200">
                                {subforums.map((subforum) => (
                                    <div key={subforum.id} className="flex flex-col gap-4 p-6 transition-colors hover:bg-slate-50 sm:flex-row sm:items-start sm:justify-between">
                                        <div className="min-w-0">
                                            <Link
                                                href={route(
                                                    'subforums.show',
                                                    subforum.slug,
                                                )}
                                                className="text-lg font-semibold text-slate-900 hover:text-indigo-600"
                                            >
                                                {subforum.name}
                                            </Link>
                                            {subforum.is_moderator && (
                                                <span className="ml-2 inline-flex rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700">
                                                    Moderator
                                                </span>
                                            )}
                                            <p className="mt-1 text-sm text-slate-500">
                                                {subforum.threads_count}{' '}
                                                {subforum.threads_count === 1
                                                    ? 'discussion'
                                                    : 'discussions'}
                                            </p>
                                            {subforum.description && (
                                                <p className="mt-2 text-sm text-slate-600">
                                                    {subforum.description}
                                                </p>
                                            )}
                                        </div>
                                        <Link
                                            href={route('subforums.show', subforum.slug)}
                                            className="shrink-0 rounded-full border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-50"
                                        >
                                            Open
                                        </Link>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="p-12 text-center text-slate-500">
                                No categories yet.
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
