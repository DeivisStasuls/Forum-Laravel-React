import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import useI18n from '@/hooks/useI18n';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useMemo, useState } from 'react';

export default function AdminUsers({
    auth,
    users,
    subforums,
    assignableUsers,
    forumStats,
}) {
    const { t } = useI18n();
    const [search, setSearch] = useState('');
    const [subforumsState, setSubforumsState] = useState(subforums);
    const [banTarget, setBanTarget] = useState(null);
    const [banReason, setBanReason] = useState('');
    const [banError, setBanError] = useState('');
    const [moderatorUserSearch, setModeratorUserSearch] = useState('');
    const {
        data: moderatorData,
        setData: setModeratorData,
        processing: assigningModerator,
        errors: moderatorErrors,
    } = useForm({
        subforum_id: '',
        user_id: '',
    });

    const openBanReasonPanel = (user) => {
        setBanTarget(user);
        setBanReason('');
        setBanError('');
    };

    const closeBanReasonPanel = () => {
        setBanTarget(null);
        setBanReason('');
        setBanError('');
    };

    const submitBan = () => {
        if (!banTarget) {
            return;
        }

        const trimmedReason = banReason.trim();

        if (!trimmedReason) {
            setBanError('Ban reason is required.');
            return;
        }

        router.patch(
            route('admin.users.ban', banTarget.id),
            { reason: trimmedReason },
            {
                preserveScroll: true,
                onSuccess: () => closeBanReasonPanel(),
                onError: (errors) => {
                    setBanError(
                        errors.reason ?? 'Unable to ban user right now.',
                    );
                },
            },
        );
    };

    const submitModerator = (e) => {
        e.preventDefault();

        if (!moderatorData.subforum_id || !moderatorData.user_id) {
            return;
        }

        const selectedUser = assignableUsers.find(
            (user) => String(user.id) === String(moderatorData.user_id),
        );

        router.post(route('admin.moderators.assign'), moderatorData, {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                if (selectedUser) {
                    setSubforumsState((previous) =>
                        previous.map((subforum) => {
                            if (
                                String(subforum.id) !==
                                String(moderatorData.subforum_id)
                            ) {
                                return subforum;
                            }

                            const alreadyExists = subforum.moderators.some(
                                (moderator) =>
                                    String(moderator.id) ===
                                    String(selectedUser.id),
                            );

                            if (alreadyExists) {
                                return subforum;
                            }

                            return {
                                ...subforum,
                                moderators: [
                                    ...subforum.moderators,
                                    selectedUser,
                                ],
                            };
                        }),
                    );
                }

                setModeratorData('user_id', '');
                setModeratorUserSearch('');
            },
        });
    };

    const handleRemoveModerator = (subforumId, moderatorId) => {
        router.delete(route('admin.moderators.remove', [subforumId, moderatorId]), {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                setSubforumsState((previous) =>
                    previous.map((subforum) => {
                        if (String(subforum.id) !== String(subforumId)) {
                            return subforum;
                        }

                        return {
                            ...subforum,
                            moderators: subforum.moderators.filter(
                                (moderator) =>
                                    String(moderator.id) !==
                                    String(moderatorId),
                            ),
                        };
                    }),
                );
            },
        });
    };

    const filteredUsers = useMemo(() => {
        const term = search.trim().toLowerCase();
        if (!term) {
            return users;
        }

        return users.filter((user) => {
            const status = user.banned_at ? 'banned' : 'active';

            return (
                user.name.toLowerCase().includes(term) ||
                user.email.toLowerCase().includes(term) ||
                user.role.toLowerCase().includes(term) ||
                status.includes(term)
            );
        });
    }, [users, search]);

    const filteredAssignableUsers = useMemo(() => {
        const term = moderatorUserSearch.trim().toLowerCase();
        if (!term) {
            return assignableUsers;
        }

        return assignableUsers.filter((user) => {
            return (
                user.name.toLowerCase().includes(term) ||
                user.email.toLowerCase().includes(term)
            );
        });
    }, [assignableUsers, moderatorUserSearch]);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {t('Administration')}
                </h2>
            }
        >
            <Head title={t('Administration')} />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 className="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                            {t('Forum Statistics')}
                        </h3>
                        <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                            <div className="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {t('Discussions')}
                                </div>
                                <div className="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {forumStats.total_threads.toLocaleString()}
                                </div>
                            </div>
                            <div className="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {t('Comments')}
                                </div>
                                <div className="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {forumStats.total_posts.toLocaleString()}
                                </div>
                            </div>
                            <div className="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {t('Categories')}
                                </div>
                                <div className="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {forumStats.total_subforums.toLocaleString()}
                                </div>
                            </div>
                            <div className="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/40">
                                <div className="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    {t('Members')}
                                </div>
                                <div className="mt-1 text-xl font-bold text-gray-900 dark:text-gray-100">
                                    {forumStats.total_users.toLocaleString()}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="mb-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                            {t('Category Moderators')}
                        </h3>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {t('Assign moderators to selected categories and view the current moderator list.')}
                        </p>

                        <form
                            onSubmit={submitModerator}
                            className="mt-4 grid grid-cols-1 items-start gap-3 md:grid-cols-3"
                        >
                            <select
                                value={moderatorData.subforum_id}
                                onChange={(e) =>
                                    setModeratorData(
                                        'subforum_id',
                                        e.target.value,
                                    )
                                }
                                className="block h-11 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">{t('Select category...')}</option>
                                {subforumsState.map((subforum) => (
                                    <option key={subforum.id} value={subforum.id}>
                                        {subforum.name}
                                    </option>
                                ))}
                            </select>

                            <div>
                                <input
                                    type="text"
                                    value={moderatorUserSearch}
                                    onChange={(e) =>
                                        setModeratorUserSearch(e.target.value)
                                    }
                                    className="mb-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder={t('Search user by name or email...')}
                                />
                                <div className="max-h-36 overflow-y-auto rounded-md border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-900">
                                    {filteredAssignableUsers.length > 0 ? (
                                        filteredAssignableUsers.map((user) => (
                                            <button
                                                key={user.id}
                                                type="button"
                                                onClick={() =>
                                                    setModeratorData(
                                                        'user_id',
                                                        String(user.id),
                                                    )
                                                }
                                                className={`mb-1 block w-full rounded px-2 py-1 text-left text-sm transition last:mb-0 ${
                                                    String(
                                                        moderatorData.user_id,
                                                    ) === String(user.id)
                                                        ? 'bg-indigo-100 text-indigo-700'
                                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                                }`}
                                            >
                                                {user.name} ({user.email})
                                            </button>
                                        ))
                                    ) : (
                                        <p className="px-2 py-1 text-sm text-gray-500 dark:text-gray-400">
                                            {t('No users match your search.')}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <button
                                type="submit"
                                disabled={
                                    assigningModerator ||
                                    !moderatorData.subforum_id ||
                                    !moderatorData.user_id
                                }
                                className="h-11 w-full self-start rounded-md bg-indigo-600 px-4 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                {t('Add Moderator')}
                            </button>
                        </form>

                        {(moderatorErrors.subforum_id ||
                            moderatorErrors.user_id) && (
                            <p className="mt-2 text-sm text-red-600">
                                {moderatorErrors.subforum_id ||
                                    moderatorErrors.user_id}
                            </p>
                        )}

                        <div className="mt-5 grid grid-cols-1 gap-3 md:grid-cols-2">
                            {subforumsState.map((subforum) => (
                                <div
                                    key={subforum.id}
                                    className="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                >
                                    <div className="mb-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        {subforum.name}
                                    </div>
                                    {subforum.moderators.length > 0 ? (
                                        <div className="space-y-2">
                                            {subforum.moderators.map(
                                                (moderator) => (
                                                    <div
                                                        key={moderator.id}
                                                        className="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 text-sm dark:bg-gray-900/40"
                                                    >
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            {moderator.name}
                                                        </span>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                handleRemoveModerator(
                                                                    subforum.id,
                                                                    moderator.id,
                                                                )
                                                            }
                                                            className="text-xs font-semibold text-red-600 transition hover:text-red-700"
                                                        >
                                                            {t('Remove')}
                                                        </button>
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    ) : (
                                        <p className="text-sm text-gray-500 dark:text-gray-400">
                                            {t('No moderators assigned.')}
                                        </p>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                {t('User Administration')}
                            </h3>
                            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {t('Promote users to admin and ban/unban accounts.')}
                            </p>
                            <div className="mt-4">
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder={t('Search users by name, email, role, or status...')}
                                />
                            </div>

                            {banTarget && (
                                <div className="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
                                    <div className="mb-2 text-sm font-semibold text-red-800">
                                        {t('Ban')} {banTarget.name}
                                    </div>
                                    <textarea
                                        value={banReason}
                                        onChange={(e) =>
                                            setBanReason(e.target.value)
                                        }
                                        className="w-full rounded-md border-red-300 text-sm text-gray-900 shadow-sm focus:border-red-500 focus:ring-red-500"
                                        rows={3}
                                        maxLength={500}
                                        placeholder={t('Required: explain why this user is being banned.')}
                                    />
                                    {banError && (
                                        <p className="mt-2 text-sm text-red-700">
                                            {banError}
                                        </p>
                                    )}
                                    <div className="mt-3 flex justify-end gap-2">
                                        <button
                                            type="button"
                                            onClick={closeBanReasonPanel}
                                            className="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800 transition hover:bg-gray-300"
                                        >
                                            {t('Cancel')}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={submitBan}
                                            className="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700"
                                        >
                                            {t('Confirm Ban')}
                                        </button>
                                    </div>
                                </div>
                            )}
                        </div>

                        <div className="max-h-[70vh] overflow-auto">
                            <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead className="sticky top-0 z-10 bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {t('User')}
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {t('Role')}
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {t('Status')}
                                        </th>
                                        <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {t('Joined')}
                                        </th>
                                        <th className="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {t('Warnings')}
                                        </th>
                                        <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">
                                            {t('Actions')}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                    {filteredUsers.map((user) => (
                                        <tr key={user.id}>
                                            <td className="px-6 py-4">
                                                <div className="font-medium text-gray-900 dark:text-gray-100">
                                                    {user.name}
                                                </div>
                                                <div className="text-sm text-gray-500 dark:text-gray-400">
                                                    {user.email}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${
                                                        user.role === 'admin'
                                                            ? 'bg-purple-100 text-purple-700'
                                                            : 'bg-gray-100 text-gray-700'
                                                    }`}
                                                >
                                                    {user.role}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <span
                                                    className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${
                                                        user.banned_at
                                                            ? 'bg-red-100 text-red-700'
                                                            : 'bg-green-100 text-green-700'
                                                    }`}
                                                    title={
                                                        user.banned_at &&
                                                        user.ban_reason
                                                            ? `Reason: ${user.ban_reason}`
                                                            : undefined
                                                    }
                                                >
                                                    {user.banned_at
                                                        ? t('Banned')
                                                        : t('Active')}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                                {formatDistanceToNow(
                                                    new Date(user.created_at),
                                                    { addSuffix: true },
                                                )}
                                            </td>
                                            <td className="px-6 py-4 text-center">
                                                <span
                                                    className={`inline-flex min-w-8 justify-center rounded-full px-2.5 py-1 text-xs font-semibold ${
                                                        user.warnings_count > 0
                                                            ? 'bg-amber-100 text-amber-800'
                                                            : 'bg-gray-100 text-gray-600'
                                                    }`}
                                                >
                                                    {user.warnings_count}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4">
                                                <div className="flex justify-end gap-2">
                                                    {user.id !== auth.user.id && (
                                                        <>
                                                            <Link
                                                                href={route(
                                                                    'admin.users.warn',
                                                                    user.id,
                                                                )}
                                                                method="patch"
                                                                as="button"
                                                                preserveState
                                                                preserveScroll
                                                                className="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-amber-600"
                                                            >
                                                                {t('Warn')}
                                                            </Link>
                                                            <Link
                                                                href={route(
                                                                    'admin.users.warnings.remove',
                                                                    user.id,
                                                                )}
                                                                method="patch"
                                                                as="button"
                                                                preserveState
                                                                preserveScroll
                                                                className="rounded-md bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-900 transition hover:bg-amber-200 disabled:cursor-not-allowed disabled:opacity-50"
                                                                disabled={
                                                                    user.warnings_count <=
                                                                    0
                                                                }
                                                            >
                                                                {t('Remove Warning')}
                                                            </Link>
                                                        </>
                                                    )}

                                                    {user.id !== auth.user.id &&
                                                        (user.role === 'admin' ? (
                                                            <Link
                                                                href={route(
                                                                    'admin.users.demote',
                                                                    user.id,
                                                                )}
                                                                method="patch"
                                                                as="button"
                                                                className="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800 transition hover:bg-gray-300"
                                                            >
                                                                {t('Remove Admin')}
                                                            </Link>
                                                        ) : (
                                                            <Link
                                                                href={route(
                                                                    'admin.users.promote',
                                                                    user.id,
                                                                )}
                                                                method="patch"
                                                                as="button"
                                                                className="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-indigo-700"
                                                            >
                                                                {t('Make Admin')}
                                                            </Link>
                                                        ))}

                                                    {user.id !== auth.user.id &&
                                                        (user.banned_at ? (
                                                            <Link
                                                                href={route(
                                                                    'admin.users.unban',
                                                                    user.id,
                                                                )}
                                                                method="patch"
                                                                as="button"
                                                                className="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-700"
                                                            >
                                                                {t('Unban')}
                                                            </Link>
                                                        ) : (
                                                            <button
                                                                type="button"
                                                                onClick={() => openBanReasonPanel(user)}
                                                                className="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700"
                                                            >
                                                                {t('Ban')}
                                                            </button>
                                                        ))}
                                                </div>
                                            </td>
                                        </tr>
                                    ))}
                                    {filteredUsers.length === 0 && (
                                        <tr>
                                            <td
                                                colSpan={6}
                                                className="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400"
                                            >
                                                {t('No users match your search.')}
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
