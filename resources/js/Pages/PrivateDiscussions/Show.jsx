import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useMemo, useRef, useState } from 'react';

export default function PrivateDiscussionShow({ auth, group }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        body: '',
    });
    const [messages, setMessages] = useState(group.messages);
    const messagesContainerRef = useRef(null);
    const lastMessageId = useMemo(
        () => (messages.length > 0 ? messages[messages.length - 1].id : 0),
        [messages],
    );

    const submit = (e) => {
        e.preventDefault();

        post(route('private-discussions.messages.store', group.id), {
            preserveScroll: true,
            onSuccess: () => reset('body'),
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

            <div className="min-h-screen bg-gray-200 py-6">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('private-discussions.index')}
                            className="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            ← Back to Private Discussions
                        </Link>
                    </div>

                    <div className="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <p className="text-sm text-gray-500 dark:text-gray-400">
                            Members: {group.members.map((m) => m.name).join(', ')}
                        </p>
                    </div>

                    <div className="mt-4 rounded-lg bg-white shadow-sm dark:bg-gray-800">
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
                                                        { addSuffix: true },
                                                    )}
                                                </span>
                                            </div>
                                            <p className="whitespace-pre-wrap text-gray-800 dark:text-gray-200">
                                                {message.body}
                                            </p>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <div className="p-8 text-center text-gray-500 dark:text-gray-400">
                                    No messages yet. Start the conversation.
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="mt-4 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 className="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                            Send Message
                        </h3>
                        <form onSubmit={submit}>
                            <textarea
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                rows={4}
                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Write a private message..."
                            />
                            <InputError message={errors.body} className="mt-2" />

                            <div className="mt-4 flex justify-end">
                                <PrimaryButton disabled={processing}>
                                    Send
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
