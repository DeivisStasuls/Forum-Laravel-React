import InputError from '@/Components/InputError';
import MarkdownEditor from '@/Components/MarkdownEditor';
import MarkdownText from '@/Components/MarkdownText';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useMemo, useRef, useState } from 'react';

export default function PrivateDiscussionShow({ auth, group }) {
    const {
        data: messageData,
        setData: setMessageData,
        post,
        processing,
        errors,
        reset,
    } = useForm({
        body: '',
        image: null,
    });
    const {
        data: groupData,
        setData: setGroupData,
        patch,
        processing: updatingGroup,
        errors: groupErrors,
    } = useForm({
        name: group.name || '',
    });
    const {
        data: memberData,
        setData: setMemberData,
        post: postMember,
        processing: addingMember,
        errors: memberErrors,
    } = useForm({
        user_id: '',
    });
    const [messages, setMessages] = useState(group.messages);
    const [memberSearch, setMemberSearch] = useState('');
    const messagesContainerRef = useRef(null);
    const lastMessageId = useMemo(
        () => (messages.length > 0 ? messages[messages.length - 1].id : 0),
        [messages],
    );
    const filteredAvailableUsers = useMemo(() => {
        const users = group.available_users || [];
        const term = memberSearch.trim().toLowerCase();
        if (!term) {
            return users;
        }

        return users.filter((user) => {
            return (
                user.name.toLowerCase().includes(term) ||
                user.email.toLowerCase().includes(term)
            );
        });
    }, [group.available_users, memberSearch]);

    const submit = (e) => {
        e.preventDefault();

        post(route('private-discussions.messages.store', group.id), {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => reset('body', 'image'),
        });
    };

    const submitGroupSettings = (e) => {
        e.preventDefault();
        patch(route('private-discussions.update', group.id));
    };

    const submitAddMember = (e) => {
        e.preventDefault();
        postMember(route('private-discussions.members.add', group.id), {
            onSuccess: () => {
                setMemberData('user_id', '');
                setMemberSearch('');
            },
        });
    };

    useEffect(() => {
        let isMounted = true;

        const pollMessages = async () => {
            try {
                const response = await window.axios.get(
                    route('private-discussions.messages.index', group.id),
                    {
                        params: {
                            after_id: lastMessageId,
                        },
                    },
                );

                const newMessages = response?.data?.messages ?? [];
                if (!isMounted || newMessages.length === 0) {
                    return;
                }

                setMessages((previous) => [...previous, ...newMessages]);
            } catch (error) {
                // Silent failure during polling to avoid interrupting chat UX.
            }
        };

        const interval = window.setInterval(pollMessages, 3000);

        return () => {
            isMounted = false;
            window.clearInterval(interval);
        };
    }, [group.id, lastMessageId]);

    useEffect(() => {
        if (!messagesContainerRef.current) {
            return;
        }

        messagesContainerRef.current.scrollTop =
            messagesContainerRef.current.scrollHeight;
    }, [messages.length]);

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    {group.name || 'Private Group'}
                </h2>
            }
        >
            <Head title={group.name || 'Private Group'} />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('private-discussions.index')}
                            className="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            ← Back to Private Discussions
                        </Link>
                    </div>

                    <div className="grid grid-cols-1 gap-4 lg:grid-cols-12">
                        <div className="space-y-4 lg:col-span-8">
                            <div className="rounded-lg bg-white shadow-sm dark:bg-gray-800">
                                <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                                    <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                        Messages
                                    </h3>
                                </div>
                                <div
                                    ref={messagesContainerRef}
                                    className="max-h-[55vh] overflow-y-auto"
                                >
                                    {messages.length > 0 ? (
                                        <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                            {messages.map((message) => (
                                                <div
                                                    key={message.id}
                                                    className="p-6"
                                                >
                                                    <div className="mb-1 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                                        <span className="font-medium text-gray-700 dark:text-gray-300">
                                                            {message.user.name}
                                                        </span>
                                                        <span>
                                                            {formatDistanceToNow(
                                                                new Date(
                                                                    message.created_at,
                                                                ),
                                                                {
                                                                    addSuffix: true,
                                                                },
                                                            )}
                                                        </span>
                                                    </div>
                                                    {message.body && (
                                                        <MarkdownText
                                                            content={message.body}
                                                            className="text-gray-800 dark:text-gray-200"
                                                        />
                                                    )}
                                                    {message.image_url && (
                                                        <img
                                                            src={
                                                                message.image_url
                                                            }
                                                            alt="Message attachment"
                                                            className="mt-3 max-h-80 w-auto rounded-lg border border-slate-200"
                                                        />
                                                    )}
                                                </div>
                                            ))}
                                        </div>
                                    ) : (
                                        <div className="p-8 text-center text-gray-500 dark:text-gray-400">
                                            No messages yet. Start the
                                            conversation.
                                        </div>
                                    )}
                                </div>
                            </div>

                            <div className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                                <h3 className="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Send Message
                                </h3>
                                <form onSubmit={submit}>
                                    <MarkdownEditor
                                        value={messageData.body}
                                        onChange={(value) =>
                                            setMessageData('body', value)
                                        }
                                        rows={4}
                                        textareaClassName="rounded-md border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                        placeholder="Write a private message..."
                                    />
                                    <InputError
                                        message={errors.body}
                                        className="mt-2"
                                    />
                                    <div className="mt-3">
                                        <input
                                            type="file"
                                            accept="image/*"
                                            onChange={(e) =>
                                                setMessageData(
                                                    'image',
                                                    e.target.files?.[0] ?? null,
                                                )
                                            }
                                            className="block w-full text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:font-medium file:text-slate-700 hover:file:bg-slate-200"
                                        />
                                        <InputError
                                            message={errors.image}
                                            className="mt-2"
                                        />
                                    </div>

                                    <div className="mt-4 flex justify-end">
                                        <PrimaryButton disabled={processing}>
                                            Send
                                        </PrimaryButton>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <aside className="lg:col-span-4">
                            <div className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                                <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                    Options
                                </h3>

                                {group.can_manage ? (
                                    <div className="mt-4 space-y-4">
                                        <form onSubmit={submitGroupSettings}>
                                            <label className="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                Group Name
                                            </label>
                                            <input
                                                type="text"
                                                value={groupData.name}
                                                onChange={(e) =>
                                                    setGroupData(
                                                        'name',
                                                        e.target.value,
                                                    )
                                                }
                                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                                placeholder="Group name"
                                            />
                                            <InputError
                                                message={groupErrors.name}
                                                className="mt-2"
                                            />
                                            <div className="mt-3">
                                                <PrimaryButton
                                                    disabled={updatingGroup}
                                                >
                                                    Save Name
                                                </PrimaryButton>
                                            </div>
                                        </form>

                                        <form onSubmit={submitAddMember}>
                                            <label className="text-sm font-medium text-gray-700 dark:text-gray-200">
                                                Add Member
                                            </label>
                                            <input
                                                type="text"
                                                value={memberSearch}
                                                onChange={(e) =>
                                                    setMemberSearch(
                                                        e.target.value,
                                                    )
                                                }
                                                className="mt-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                                placeholder="Search users by name or email..."
                                            />
                                            <div className="mt-1 max-h-40 overflow-y-auto rounded-md border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-900">
                                                {filteredAvailableUsers.length >
                                                0 ? (
                                                    filteredAvailableUsers.map(
                                                        (user) => (
                                                            <button
                                                                key={user.id}
                                                                type="button"
                                                                onClick={() =>
                                                                    setMemberData(
                                                                        'user_id',
                                                                        String(
                                                                            user.id,
                                                                        ),
                                                                    )
                                                                }
                                                                className={`mb-1 block w-full rounded px-2 py-1 text-left text-sm transition last:mb-0 ${
                                                                    String(
                                                                        memberData.user_id,
                                                                    ) ===
                                                                    String(
                                                                        user.id,
                                                                    )
                                                                        ? 'bg-indigo-100 text-indigo-700'
                                                                        : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                                                                }`}
                                                            >
                                                                {user.name} (
                                                                {user.email})
                                                            </button>
                                                        ),
                                                    )
                                                ) : (
                                                    <p className="px-2 py-1 text-sm text-gray-500 dark:text-gray-400">
                                                        No users match your
                                                        search.
                                                    </p>
                                                )}
                                            </div>
                                            <InputError
                                                message={memberErrors.user_id}
                                                className="mt-2"
                                            />
                                            <div className="mt-3">
                                                <PrimaryButton
                                                    disabled={
                                                        addingMember ||
                                                        !memberData.user_id
                                                    }
                                                >
                                                    Add Member
                                                </PrimaryButton>
                                            </div>
                                        </form>
                                    </div>
                                ) : (
                                    <p className="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        Only the group creator can change group
                                        options.
                                    </p>
                                )}

                                <div className="mt-6 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <h4 className="mb-2 text-sm font-semibold uppercase tracking-wide text-gray-500">
                                        Members
                                    </h4>
                                    <div className="space-y-2">
                                        {group.members.map((member) => (
                                            <div
                                                key={member.id}
                                                className="flex items-center justify-between rounded-md bg-gray-50 px-3 py-2 dark:bg-gray-900/40"
                                            >
                                                <span className="text-sm text-gray-700 dark:text-gray-200">
                                                    {member.name}
                                                </span>
                                                {group.can_manage &&
                                                    member.id !==
                                                        auth.user.id && (
                                                        <Link
                                                            href={route(
                                                                'private-discussions.members.remove',
                                                                [
                                                                    group.id,
                                                                    member.id,
                                                                ],
                                                            )}
                                                            method="delete"
                                                            as="button"
                                                            className="text-xs font-semibold text-red-600 hover:text-red-700"
                                                        >
                                                            Remove
                                                        </Link>
                                                    )}
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <Link
                                        href={route(
                                            'private-discussions.leave',
                                            group.id,
                                        )}
                                        method="post"
                                        as="button"
                                        className="rounded-md bg-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-800 transition hover:bg-gray-300"
                                    >
                                        Leave Group
                                    </Link>
                                    {group.can_manage && (
                                        <Link
                                            href={route(
                                                'private-discussions.destroy',
                                                group.id,
                                            )}
                                            method="delete"
                                            as="button"
                                            className="rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-red-700"
                                        >
                                            Delete Group
                                        </Link>
                                    )}
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
