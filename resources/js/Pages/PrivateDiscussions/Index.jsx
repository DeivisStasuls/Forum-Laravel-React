import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useMemo, useState } from 'react';

export default function PrivateDiscussionsIndex({ auth, groups, users }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        member_ids: [],
    });
    const [search, setSearch] = useState('');

    const toggleMember = (userId) => {
        if (data.member_ids.includes(userId)) {
            setData(
                'member_ids',
                data.member_ids.filter((id) => id !== userId),
            );
            return;
        }

        setData('member_ids', [...data.member_ids, userId]);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('private-discussions.store'));
    };

    const filteredUsers = useMemo(() => {
        const term = search.trim().toLowerCase();
        if (!term) {
            return users;
        }

        return users.filter((user) => {
            return (
                user.name.toLowerCase().includes(term) ||
                user.email.toLowerCase().includes(term)
            );
        });
    }, [users, search]);

    const categorizedUsers = useMemo(() => {
        const admins = filteredUsers.filter((user) => user.role === 'admin');
        const members = filteredUsers.filter((user) => user.role !== 'admin');

        return { admins, members };
    }, [filteredUsers]);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Private Discussions
                </h2>
            }
        >
            <Head title="Private Discussions" />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 lg:grid-cols-12">
                    <aside className="lg:col-span-4">
                        <div className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                Create Group
                            </h3>
                            <form onSubmit={submit} className="mt-4 space-y-4">
                                <div>
                                    <label className="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Group Name (optional)
                                    </label>
                                    <input
                                        type="text"
                                        value={data.name}
                                        onChange={(e) =>
                                            setData('name', e.target.value)
                                        }
                                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Study Team / Project Group"
                                    />
                                    <InputError
                                        message={errors.name}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <p className="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        Add Members
                                    </p>
                                    <input
                                        type="text"
                                        value={search}
                                        onChange={(e) =>
                                            setSearch(e.target.value)
                                        }
                                        className="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Search users by name or email..."
                                    />
                                    <div className="mt-2 max-h-64 space-y-2 overflow-y-auto rounded border border-gray-200 p-3 dark:border-gray-700">
                                        {filteredUsers.length > 0 ? (
                                            <>
                                                {categorizedUsers.admins.length >
                                                    0 && (
                                                    <div>
                                                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-purple-600">
                                                            Admins
                                                        </p>
                                                        <div className="space-y-2">
                                                            {categorizedUsers.admins.map(
                                                                (user) => (
                                                                    <label
                                                                        key={
                                                                            user.id
                                                                        }
                                                                        className="flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-200"
                                                                    >
                                                                        <input
                                                                            type="checkbox"
                                                                            checked={data.member_ids.includes(
                                                                                user.id,
                                                                            )}
                                                                            onChange={() =>
                                                                                toggleMember(
                                                                                    user.id,
                                                                                )
                                                                            }
                                                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                                        />
                                                                        <span>
                                                                            {
                                                                                user.name
                                                                            }{' '}
                                                                            (
                                                                            {
                                                                                user.email
                                                                            }
                                                                            )
                                                                        </span>
                                                                    </label>
                                                                ),
                                                            )}
                                                        </div>
                                                    </div>
                                                )}

                                                {categorizedUsers.members
                                                    .length > 0 && (
                                                    <div className="pt-2">
                                                        <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                            Members
                                                        </p>
                                                        <div className="space-y-2">
                                                            {categorizedUsers.members.map(
                                                                (user) => (
                                                                    <label
                                                                        key={
                                                                            user.id
                                                                        }
                                                                        className="flex cursor-pointer items-center gap-2 text-sm text-gray-700 dark:text-gray-200"
                                                                    >
                                                                        <input
                                                                            type="checkbox"
                                                                            checked={data.member_ids.includes(
                                                                                user.id,
                                                                            )}
                                                                            onChange={() =>
                                                                                toggleMember(
                                                                                    user.id,
                                                                                )
                                                                            }
                                                                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                                        />
                                                                        <span>
                                                                            {
                                                                                user.name
                                                                            }{' '}
                                                                            (
                                                                            {
                                                                                user.email
                                                                            }
                                                                            )
                                                                        </span>
                                                                    </label>
                                                                ),
                                                            )}
                                                        </div>
                                                    </div>
                                                )}
                                            </>
                                        ) : (
                                            <p className="text-sm text-gray-500 dark:text-gray-400">
                                                No users match your search.
                                            </p>
                                        )}
                                    </div>
                                    <InputError
                                        message={errors.member_ids}
                                        className="mt-2"
                                    />
                                </div>

                                <PrimaryButton
                                    disabled={processing || users.length === 0}
                                >
                                    Create Private Group
                                </PrimaryButton>
                            </form>
                        </div>
                    </aside>

                    <main className="lg:col-span-8">
                        <div className="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                            <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                                <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Your Groups
                                </h3>
                            </div>
                            {groups.length > 0 ? (
                                <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {groups.map((group) => (
                                        <Link
                                            key={group.id}
                                            href={route(
                                                'private-discussions.show',
                                                group.id,
                                            )}
                                            className="block p-6 transition hover:bg-gray-50 dark:hover:bg-gray-700"
                                        >
                                            <h4 className="font-semibold text-gray-900 dark:text-gray-100">
                                                {group.name ||
                                                    `Group with ${group.members
                                                        .map((m) => m.name)
                                                        .join(', ')}`}
                                            </h4>
                                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                Members:{' '}
                                                {group.members
                                                    .map((m) => m.name)
                                                    .join(', ')}
                                            </p>
                                            {group.latest_message ? (
                                                <p className="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                                    <span className="font-medium">
                                                        {
                                                            group.latest_message
                                                                .user_name
                                                        }
                                                        :
                                                    </span>{' '}
                                                    {group.latest_message.body}
                                                </p>
                                            ) : (
                                                <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                                    No messages yet.
                                                </p>
                                            )}
                                            <p className="mt-2 text-xs text-gray-400">
                                                Updated{' '}
                                                {formatDistanceToNow(
                                                    new Date(group.updated_at),
                                                    { addSuffix: true },
                                                )}
                                            </p>
                                        </Link>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-10 text-center text-gray-500 dark:text-gray-400">
                                    No private groups yet.
                                </div>
                            )}
                        </div>
                    </main>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
