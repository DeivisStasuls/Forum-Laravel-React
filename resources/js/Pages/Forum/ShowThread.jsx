import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useState } from 'react';
import VoteButtons from '@/Components/VoteButtons';

export default function ShowThread({ auth, thread }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        body: '',
    });
    const [deleteTarget, setDeleteTarget] = useState(null);
    const canComment =
        !thread.creator_only_comments || auth.user.id === thread.user.id;

    const submit = (e) => {
        e.preventDefault();

        post(route('posts.store', thread.slug), {
            preserveScroll: true,
            onSuccess: () => reset('body'),
        });
    };

    const confirmDelete = () => {
        if (!deleteTarget) {
            return;
        }

        const target = deleteTarget;
        setDeleteTarget(null);

        if (target.type === 'discussion') {
            router.delete(route('threads.destroy', thread.slug), {
                onFinish: () => setDeleteTarget(null),
            });
            return;
        }

        router.delete(route('posts.destroy', [thread.slug, target.replyId]), {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <h2 className="text-xl font-semibold leading-tight text-slate-900">
                            {thread.title}
                        </h2>
                        <p className="mt-1 text-sm text-slate-500">
                            in{' '}
                            <Link
                                href={route('subforums.show', thread.subforum.slug)}
                                className="font-medium text-indigo-600 hover:text-indigo-800"
                            >
                                {thread.subforum.name}
                            </Link>
                        </p>
                    </div>
                </div>
            }
        >
            <Head title={thread.title} />

            <div className="min-h-screen bg-sky-100/60 py-6">
                <div className="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                    <div className="mb-4">
                        <div className="flex items-center justify-between gap-4">
                            <Link
                                href={route('subforums.show', thread.subforum.slug)}
                                className="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                ← Back to {thread.subforum.name}
                            </Link>

                            {(auth.user.id === thread.user.id ||
                                auth.user.role === 'admin') && (
                                <div className="flex items-center gap-2">
                                    <Link
                                        href={route('threads.edit', thread.slug)}
                                        className="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-700"
                                    >
                                        Edit Discussion
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            setDeleteTarget({
                                                type: 'discussion',
                                            })
                                        }
                                        className="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50 hover:text-red-700"
                                    >
                                        Delete Discussion
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>

                    <article className="overflow-hidden rounded-2xl border border-sky-200 bg-sky-50/90 shadow-sm backdrop-blur">
                        <div className="border-b border-slate-200 p-6">
    <div className="flex gap-4">

        {/* Votes */}
       <VoteButtons
    routeName="threads.vote"
    routeParams={{ thread: thread.id }}
    initialScore={thread.score}
    userVote={thread.user_vote}
    selfVote={auth.user.id === thread.user.id} // prevents voting on own thread
/>
        {/* Thread content */}
        <div className="flex-1">

            <div className="mb-3 flex items-center gap-3 text-sm text-slate-500">
                <span>
                    by{' '}
                    <span className="font-medium text-slate-700">
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

            <p className="whitespace-pre-wrap text-slate-800">
                {thread.body}
            </p>

            {thread.edited_at && (
                <p className="mt-3 text-xs text-slate-500">
                    Edited by{' '}
                    {thread.edited_by?.role === 'admin'
                        ? 'admin'
                        : 'user'}{' '}
                    {thread.edited_by?.name ?? 'unknown'}{' '}
                    {formatDistanceToNow(
                        new Date(thread.edited_at),
                        { addSuffix: true },
                    )}
                </p>
            )}

        </div>
    </div>
</div>
                        

                        <div className="border-b border-slate-200 p-6">
                            <h3 className="text-lg font-bold text-slate-900">
                                Comments ({thread.posts_count})
                            </h3>
                        </div>

                        {thread.posts.length > 0 ? (
                            <div className="divide-y divide-slate-200">
                                {thread.posts.map((reply) => (
                                    <div key={reply.id} className="flex gap-4 p-6">

    <VoteButtons
    routeName="posts.vote"
    routeParams={{ threadSlug: thread.slug, post: reply.id }}
    initialScore={reply.score}
    userVote={reply.user_vote}
    selfVote={auth.user.id === reply.user.id} // prevents voting on own post
/>

    <div className="flex-1">

        <div className="mb-2 flex items-center justify-between text-sm text-slate-500">
            <div className="flex items-center gap-3">
                <span className="font-medium text-slate-700">
                    {reply.user.name}
                </span>

                <span>
                    {formatDistanceToNow(
                        new Date(reply.created_at),
                        { addSuffix: true },
                    )}
                </span>
            </div>

            {(auth.user.id === reply.user.id ||
                auth.user.role === 'admin') && (
                <div className="flex items-center gap-2">
                    <Link
                        href={route('posts.edit', [thread.slug, reply.id])}
                        className="text-xs text-indigo-600 hover:text-indigo-800"
                    >
                        Edit
                    </Link>

                    <button
                        onClick={() =>
                            setDeleteTarget({
                                type: 'comment',
                                replyId: reply.id,
                            })
                        }
                        className="text-xs text-red-600 hover:text-red-700"
                    >
                        Delete
                    </button>
                </div>
            )}
        </div>

        <p className="whitespace-pre-wrap text-slate-800">
            {reply.body}
        </p>

    </div>
</div>
                                ))}
                            </div>
                        ) : (
                            <div className="p-6 text-sm text-slate-500">
                                No replies yet. Be the first to comment.
                            </div>
                        )}
                    </article>

                    <div className="mt-6 rounded-2xl border border-sky-200 bg-sky-50/90 p-6 shadow-sm backdrop-blur">
                        <h3 className="mb-4 text-lg font-bold text-slate-900">
                            Add a Comment
                        </h3>
                        {canComment ? (
                            <form onSubmit={submit}>
                                <textarea
                                    value={data.body}
                                    onChange={(e) =>
                                        setData('body', e.target.value)
                                    }
                                    rows={5}
                                    className="block w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Write your comment..."
                                />
                                <InputError
                                    message={errors.body}
                                    className="mt-2"
                                />

                                <div className="mt-4 flex justify-end">
                                    <PrimaryButton disabled={processing}>
                                        Post Comment
                                    </PrimaryButton>
                                </div>
                            </form>
                        ) : (
                            <p className="text-sm text-slate-600">
                                This discussion is creator-only. Only{' '}
                                {thread.user.name} can comment.
                            </p>
                        )}
                    </div>
                </div>
            </div>

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                    <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                        <h4 className="text-lg font-semibold text-slate-900">
                            Confirm Deletion
                        </h4>
                        <p className="mt-2 text-sm text-slate-600">
                            {deleteTarget.type === 'discussion'
                                ? 'Are you sure you want to delete this discussion? This action cannot be undone.'
                                : 'Are you sure you want to delete this comment? This action cannot be undone.'}
                        </p>

                        <div className="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => setDeleteTarget(null)}
                                className="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={confirmDelete}
                                className="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
