import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import MarkdownEditor from '@/Components/MarkdownEditor';
import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';

export default function CreateSubforum({ auth }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        restricted_thread_creation: false,
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('subforums.store'));
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Create Category
                </h2>
            }
        >
            <Head title="Create Category" />

            <div className="min-h-screen bg-gray-200 py-6">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="overflow-hidden bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                        <form onSubmit={submit}>
                            <div>
                                <InputLabel htmlFor="name" value="Category Name" />
                                <input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) =>
                                        setData('name', e.target.value)
                                    }
                                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="e.g. Sports, Education, Campus Life"
                                />
                                <InputError
                                    message={errors.name}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                <InputLabel
                                    htmlFor="description"
                                    value="Description (optional)"
                                />
                                <MarkdownEditor
                                    id="description"
                                    rows={5}
                                    value={data.description}
                                    onChange={(value) =>
                                        setData('description', value)
                                    }
                                    className="mt-1"
                                    textareaClassName="rounded-md border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 dark:bg-gray-900 dark:text-gray-100"
                                    placeholder="Describe what belongs in this category..."
                                />
                                <InputError
                                    message={errors.description}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-4">
                                <label className="inline-flex items-center gap-2">
                                    <input
                                        type="checkbox"
                                        checked={data.restricted_thread_creation}
                                        onChange={(e) =>
                                            setData(
                                                'restricted_thread_creation',
                                                e.target.checked,
                                            )
                                        }
                                        className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span className="text-sm text-gray-700 dark:text-gray-200">
                                        Restrict discussion creation (only admins
                                        and category moderators can post)
                                    </span>
                                </label>
                                <InputError
                                    message={errors.restricted_thread_creation}
                                    className="mt-2"
                                />
                            </div>

                            <div className="mt-6 flex items-center justify-end gap-3">
                                <Link
                                    href={route('forum.index')}
                                    className="rounded-md px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton disabled={processing}>
                                    Create Category
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
