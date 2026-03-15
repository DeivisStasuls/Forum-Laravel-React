import InputError from '@/Components/InputError';
import MarkdownEditor from '@/Components/MarkdownEditor';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function EditThread({ auth, thread, subforums }) {
    const { data, setData, patch, processing, errors } = useForm({
        title: thread.title ?? '',
        subforum_id: thread.subforum_id ? String(thread.subforum_id) : '',
        body: thread.body ?? '',
        image: null,
        remove_image: false,
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('threads.update', thread.slug), {
            forceFormData: true,
        });
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
                                <MarkdownEditor
                                    id="body"
                                    rows={8}
                                    value={data.body}
                                    onChange={(value) =>
                                        setData('body', value)
                                    }
                                    className="mt-1"
                                    textareaClassName="rounded-md border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                />
                                <InputError
                                    message={errors.body}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                <InputLabel
                                    htmlFor="image"
                                    value="Attachment (optional)"
                                />
                                {thread.image_url && !data.remove_image && (
                                    <img
                                        src={thread.image_url}
                                        alt="Current thread attachment"
                                        className="mb-3 mt-1 max-h-80 w-auto rounded-lg border border-slate-200"
                                    />
                                )}
                                <input
                                    id="image"
                                    type="file"
                                    accept="image/*"
                                    onChange={(e) =>
                                        setData(
                                            'image',
                                            e.target.files?.[0] ?? null,
                                        )
                                    }
                                    className="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <InputError
                                    message={errors.image}
                                    className="mt-2"
                                />
                                {thread.image_url && (
                                    <label className="mt-3 inline-flex items-center gap-2 text-sm text-slate-700">
                                        <input
                                            type="checkbox"
                                            checked={data.remove_image}
                                            onChange={(e) =>
                                                setData(
                                                    'remove_image',
                                                    e.target.checked,
                                                )
                                            }
                                            className="rounded border-slate-300 text-indigo-600"
                                        />
                                        Remove current image
                                    </label>
                                )}
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
