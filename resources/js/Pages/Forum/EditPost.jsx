import InputError from '@/Components/InputError';
import MarkdownEditor from '@/Components/MarkdownEditor';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';

export default function EditPost({ auth, thread, post }) {
    const { data, setData, processing, errors } = useForm({
        body: post.body ?? '',
        image: null,
        remove_image: false,
    });

    const submit = (e) => {
        e.preventDefault();
        router.post(route('posts.update', [thread.slug, post.id]), {
            ...data,
            _method: 'patch',
        }, {
            forceFormData: true,
        });
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Edit Comment
                </h2>
            }
        >
            <Head title="Edit Comment" />

            <div className="min-h-screen bg-gray-200 py-6">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <form onSubmit={submit}>
                            <div>
                                <MarkdownEditor
                                    value={data.body}
                                    onChange={(value) =>
                                        setData('body', value)
                                    }
                                    rows={7}
                                    textareaClassName="rounded-md border-gray-300 dark:bg-gray-900 dark:text-gray-100"
                                />
                                <InputError
                                    message={errors.body}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                {post.image_url && !data.remove_image && (
                                    <img
                                        src={post.image_url}
                                        alt="Current attachment"
                                        className="mb-3 max-h-80 w-auto rounded-lg border border-slate-200"
                                    />
                                )}
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
                                {post.image_url && (
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
