import InputError from '@/Components/InputError';
import MarkdownEditor from '@/Components/MarkdownEditor';
import MarkdownText from '@/Components/MarkdownText';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useRef, useState } from 'react';
import VoteButtons from '@/Components/VoteButtons';
import useI18n from '@/hooks/useI18n';

export default function ShowThread({ auth, thread, filters }) {
    const { t } = useI18n();
    const { data, setData, post, processing, errors, reset } = useForm({
        body: '',
        image: null,
    });
    const [deleteTarget, setDeleteTarget] = useState(null);
    const [selectedOrder, setSelectedOrder] = useState(
        filters?.order ?? 'oldest',
    );
    const [replySearch, setReplySearch] = useState(
        filters?.reply_search ?? '',
    );
    const [isCommentComposerOpen, setIsCommentComposerOpen] = useState(false);
    const isFirstFilterRender = useRef(true);
    const canComment =
        !thread.creator_only_comments || auth.user.id === thread.user.id;

    useEffect(() => {
        if (isFirstFilterRender.current) {
            isFirstFilterRender.current = false;
            return;
        }

        const timeout = setTimeout(() => {
            router.get(
                route('threads.show', thread.slug),
                {
                    order: selectedOrder,
                    reply_search: replySearch,
                },
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                },
            );
        }, 250);

        return () => clearTimeout(timeout);
    }, [selectedOrder, replySearch, thread.slug]);

    const submit = (e) => {
        e.preventDefault();

        post(route('posts.store', thread.slug), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                reset('body', 'image');
                setIsCommentComposerOpen(false);
            },
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
                            {t('in')}{' '}
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
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <Link
                                href={route('subforums.show', thread.subforum.slug)}
                                className="text-sm text-indigo-600 hover:text-indigo-800"
                            >
                                ← {t('Back to')} {thread.subforum.name}
                            </Link>

                            {(auth.user.id === thread.user.id ||
                                auth.user.role === 'admin') && (
                                <div className="flex flex-wrap items-center gap-2">
                                    <Link
                                        href={route('threads.edit', thread.slug)}
                                        className="rounded-lg px-3 py-1.5 text-xs font-medium text-indigo-600 transition hover:bg-indigo-50 hover:text-indigo-700"
                                    >
                                        {t('Edit Discussion')}
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
                                        {t('Delete Discussion')}
                                    </button>
                                </div>
                            )}
                        </div>
                    </div>

                    <article className="overflow-hidden rounded-2xl border border-sky-200 bg-sky-50/90 shadow-sm backdrop-blur">
                        <div className="p-6">
    <div className="flex flex-col gap-4 sm:flex-row">

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
                    {t('by')}{' '}
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

            <MarkdownText
                content={thread.body}
                className="text-slate-800"
            />
            {thread.image_url && (
                <img
                    src={thread.image_url}
                    alt="Discussion attachment"
                    className="mt-3 max-h-96 w-auto rounded-lg border border-slate-200"
                />
            )}

            {thread.edited_at && (
                <p className="mt-3 text-xs text-slate-500">
                    {t('Edited by')}{' '}
                    {thread.edited_by?.role === 'admin'
                        ? t('admin')
                        : t('user')}{' '}
                    {thread.edited_by?.name ?? t('unknown')}{' '}
                    {formatDistanceToNow(
                        new Date(thread.edited_at),
                        { addSuffix: true },
                    )}
                </p>
            )}

        </div>
    </div>
</div>
                        

                        <div className="mt-8 px-6 pb-6">
                            <h3 className="mb-4 text-lg font-bold text-slate-900">
                                {t('Add a Comment')}
                            </h3>
                            {canComment ? (
                                <form onSubmit={submit}>
                                    {isCommentComposerOpen ? (
                                        <>
                                            <MarkdownEditor
                                                value={data.body}
                                                onChange={(value) =>
                                                    setData('body', value)
                                                }
                                                rows={5}
                                                textareaClassName="border-slate-300"
                                                placeholder={t('Join the conversation')}
                                                autoFocus
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
                                                        setData(
                                                            'image',
                                                            e.target.files?.[0] ?? null,
                                                        )
                                                    }
                                                    className="block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                                                />
                                                <InputError
                                                    message={errors.image}
                                                    className="mt-2"
                                                />
                                            </div>

                                            <div className="mt-4 flex justify-end gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() => {
                                                        setIsCommentComposerOpen(
                                                            false,
                                                        );
                                                        if (!processing) {
                                                            reset(
                                                                'body',
                                                                'image',
                                                            );
                                                        }
                                                    }}
                                                    className="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200"
                                                >
                                                    {t('Cancel')}
                                                </button>
                                                <PrimaryButton
                                                    disabled={processing}
                                                >
                                                    {t('Comment')}
                                                </PrimaryButton>
                                            </div>
                                        </>
                                    ) : (
                                        <button
                                            type="button"
                                            onClick={() =>
                                                setIsCommentComposerOpen(true)
                                            }
                                            className="block w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-left text-sm text-slate-500 shadow-sm transition hover:border-slate-400"
                                        >
                                            {t('Join the conversation')}
                                        </button>
                                    )}
                                </form>
                            ) : (
                                <p className="text-sm text-slate-600">
                                    {t('This discussion is creator-only. Only')}{' '}
                                    {thread.user.name} {t('can comment.')}
                                </p>
                            )}
                        </div>

                        <div className="border-b border-slate-200 p-6">
                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <h3 className="text-lg font-bold text-slate-900">
                                    {t('Comments')} ({thread.posts_count})
                                </h3>
                                <div className="flex flex-wrap items-center gap-2">
                                    <input
                                        type="text"
                                        value={replySearch}
                                        onChange={(e) =>
                                            setReplySearch(e.target.value)
                                        }
                                        placeholder={t('Search replies...')}
                                        className="w-full rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-56"
                                    />
                                    <label
                                        htmlFor="reply-order"
                                        className="text-sm font-medium text-slate-600"
                                    >
                                        {t('Order by')}
                                    </label>
                                    <select
                                        id="reply-order"
                                        value={selectedOrder}
                                        onChange={(e) =>
                                            setSelectedOrder(e.target.value)
                                        }
                                        className="rounded-lg border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="oldest">{t('Oldest')}</option>
                                        <option value="latest">{t('Newest')}</option>
                                        <option value="top_voted">
                                            {t('Top voted')}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {thread.posts.length > 0 ? (
                            <div className="divide-y divide-slate-200">
                                {thread.posts.map((reply) => (
                                    <div key={reply.id} className="flex flex-col gap-4 p-6 sm:flex-row">

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
                        {t('Edit')}
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
                        {t('Delete')}
                    </button>
                </div>
            )}
        </div>

        <MarkdownText
            content={reply.body}
            className="text-slate-800"
        />
        {reply.image_url && (
            <img
                src={reply.image_url}
                alt="Comment attachment"
                className="mt-3 max-h-80 w-auto rounded-lg border border-slate-200"
            />
        )}

    </div>
</div>
                                ))}
                            </div>
                        ) : (
                            <div className="p-6 text-sm text-slate-500">
                                {t('No replies yet. Be the first to comment.')}
                            </div>
                        )}
                    </article>
                </div>
            </div>

            {deleteTarget && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
                    <div className="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl">
                        <h4 className="text-lg font-semibold text-slate-900">
                            {t('Confirm Deletion')}
                        </h4>
                        <p className="mt-2 text-sm text-slate-600">
                            {deleteTarget.type === 'discussion'
                                ? t('Are you sure you want to delete this discussion? This action cannot be undone.')
                                : t('Are you sure you want to delete this comment? This action cannot be undone.')}
                        </p>

                        <div className="mt-6 flex justify-end gap-3">
                            <button
                                type="button"
                                onClick={() => setDeleteTarget(null)}
                                className="rounded-lg px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                            >
                                {t('Cancel')}
                            </button>
                            <button
                                type="button"
                                onClick={confirmDelete}
                                className="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700"
                            >
                                {t('Delete')}
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
