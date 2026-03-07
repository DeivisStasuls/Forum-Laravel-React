import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';

export default function ShowThread({ auth, thread }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        body: '',
    });

    const submit = (e) => {
        e.preventDefault();

        post(route('posts.store', thread.slug), {
            preserveScroll: true,
            onSuccess: () => reset('body'),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                            {thread.title}
                        </h2>
                        <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            in{' '}
                            <Link
                                href={route('subforums.show', thread.subforum.slug)}
                                className="font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                {thread.subforum.name}
                            </Link>
                        </p>
                    </div>
                </div>
            }
        >
            <Head title={thread.title} />

            <div className="min-h-screen bg-gray-100 py-6 dark:bg-gray-900">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <Link
                            href={route('subforums.show', thread.subforum.slug)}
                            className="text-sm text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300"
                        >
                            ← Back to {thread.subforum.name}
                        </Link>
                    </div>

                    <article className="overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
                        <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                            <div className="mb-3 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                <span>
                                    by{' '}
                                    <span className="font-medium text-gray-700 dark:text-gray-300">
                                        {thread.user.name}
                                    </span>
                                </span>
                                <span>
                                    {formatDistanceToNow(
                                        new Date(thread.created_at),
                                        { addSuffix: true },
                                    )}
                                </span>
                            </div>
                            <p className="whitespace-pre-wrap text-gray-800 dark:text-gray-200">
                                {thread.body}
                            </p>
                        </div>

                        <div className="border-b border-gray-200 p-6 dark:border-gray-700">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-gray-100">
                                Replies ({thread.posts_count})
                            </h3>
                        </div>

                        {thread.posts.length > 0 ? (
                            <div className="divide-y divide-gray-200 dark:divide-gray-700">
                                {thread.posts.map((reply) => (
                                    <div key={reply.id} className="p-6">
                                        <div className="mb-2 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                                            <span className="font-medium text-gray-700 dark:text-gray-300">
                                                {reply.user.name}
                                            </span>
                                            <span>
                                                {formatDistanceToNow(
                                                    new Date(reply.created_at),
                                                    { addSuffix: true },
                                                )}
                                            </span>
                                        </div>
                                        <p className="whitespace-pre-wrap text-gray-800 dark:text-gray-200">
                                            {reply.body}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="p-6 text-sm text-gray-500 dark:text-gray-400">
                                No replies yet. Be the first to comment.
                            </div>
                        )}
                    </article>

                    <div className="mt-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 className="mb-4 text-lg font-bold text-gray-900 dark:text-gray-100">
                            Add a Comment
                        </h3>
                        <form onSubmit={submit}>
                            <textarea
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                rows={5}
                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                placeholder="Write your comment..."
                            />
                            <InputError message={errors.body} className="mt-2" />

                            <div className="mt-4 flex justify-end">
                                <PrimaryButton disabled={processing}>
                                    Post Comment
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
