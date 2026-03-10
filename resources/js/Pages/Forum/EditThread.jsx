import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function EditThread({ auth, thread, subforums }) {
    const { data, setData, patch, processing, errors } = useForm({
        title: thread.title ?? '',
        subforum_id: thread.subforum_id ? String(thread.subforum_id) : '',
        body: thread.body ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('threads.update', thread.slug));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit Discussion
                </h2>
            }
        >
            <Head title="Edit Discussion" />

            <div className="min-h-screen bg-gray-200 py-6">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <form onSubmit={submit}>
                            <div>
                                <InputLabel
                                    htmlFor="title"
                                    value="Discussion Title"
                                />
                                <input
                                    id="title"
                                    type="text"
                                    value={data.title}
                                    onChange={(e) =>
                                        setData('title', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                />
                                <InputError
                                    message={errors.title}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                <InputLabel
                                    htmlFor="subforum_id"
                                    value="Category"
                                />
                                <select
                                    id="subforum_id"
                                    value={data.subforum_id}
                                    onChange={(e) =>
                                        setData('subforum_id', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                >
                                    {subforums.map((subforum) => (
                                        <option
                                            key={subforum.id}
                                            value={subforum.id}
                                        >
                                            {subforum.name}
                                        </option>
                                    ))}
                                </select>
                                <InputError
                                    message={errors.subforum_id}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                <InputLabel
                                    htmlFor="body"
                                    value="Discussion Content"
                                />
                                <textarea
                                    id="body"
                                    rows="8"
                                    value={data.body}
                                    onChange={(e) =>
                                        setData('body', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:text-gray-100"
                                />
                                <InputError
                                    message={errors.body}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-6 flex items-center justify-end gap-3">
                                <Link
                                    href={route('threads.show', thread.slug)}
                                    className="rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton disabled={processing}>
                                    Save Changes
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
