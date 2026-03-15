import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth }) {
    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-slate-950 text-slate-100">
                <div className="mx-auto flex max-w-6xl flex-col px-6 py-10">
                    <header className="mb-14 flex items-center justify-between">
                        <h1 className="text-2xl font-bold tracking-tight text-white">
                            Forum Hub
                        </h1>
                        <div className="flex items-center gap-3">
                            {auth.user ? (
                                <Link
                                    href={route('forum.index')}
                                    className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500"
                                >
                                    Go to forum
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:border-slate-500 hover:text-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-500"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </div>
                    </header>

                    <section className="grid gap-6 rounded-2xl border border-slate-800 bg-slate-900/70 p-8 shadow-2xl md:grid-cols-2">
                        <div>
                            <p className="mb-3 inline-flex rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-indigo-300">
                                Community Platform
                            </p>
                            <h2 className="text-4xl font-extrabold leading-tight text-white">
                                Discuss. Vote. Connect.
                            </h2>
                            <p className="mt-4 text-sm leading-7 text-slate-300">
                                A focused forum experience with categories,
                                threaded discussions, voting, and private
                                groups. Built for fast conversations and clear
                                moderation.
                            </p>
                        </div>
                        <div className="grid gap-4 text-sm">
                            <div className="rounded-xl border border-slate-700 bg-slate-800/70 p-4">
                                <h3 className="mb-1 font-semibold text-white">
                                    Structured discussions
                                </h3>
                                <p className="text-slate-300">
                                    Browse by category and follow conversations
                                    with clean thread pages.
                                </p>
                            </div>
                            <div className="rounded-xl border border-slate-700 bg-slate-800/70 p-4">
                                <h3 className="mb-1 font-semibold text-white">
                                    Voting system
                                </h3>
                                <p className="text-slate-300">
                                    Upvote and downvote threads and replies with
                                    instant visual feedback.
                                </p>
                            </div>
                            <div className="rounded-xl border border-slate-700 bg-slate-800/70 p-4">
                                <h3 className="mb-1 font-semibold text-white">
                                    Private groups
                                </h3>
                                <p className="text-slate-300">
                                    Create invite-only spaces for focused team
                                    conversations.
                                </p>
                            </div>
                        </div>
                    </section>

                    <footer className="mt-10 text-center text-xs text-slate-400">
                        Built with Laravel + React
                    </footer>
                </div>
            </div>
        </>
    );
}
