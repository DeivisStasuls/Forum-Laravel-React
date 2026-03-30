import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import useI18n from '@/hooks/useI18n';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ children }) {
    const user = usePage().props.auth.user;
    const { t } = useI18n();

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-sky-100/70">
            <nav className="sticky top-0 z-50 border-b border-sky-200 bg-white/95 text-slate-900 shadow backdrop-blur">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div className="flex h-24 justify-between">
                        <div className="flex">
                            <div className="flex shrink-0 items-center">
                                <Link href="/">
                                    <div className="flex items-center gap-2">
                                        <ApplicationLogo className="block h-16 w-auto" />
                                    </div>
                                </Link>
                            </div>

                            <div className="hidden h-full items-center gap-6 sm:ms-10 sm:flex">
                                <NavLink
                                    href={route('forum.index')}
                                    active={route().current('forum.index')}
                                >
                                    {t('Forum')}
                                </NavLink>
                                <NavLink
                                    href={route('subforums.index')}
                                    active={route().current('subforums.index')}
                                >
                                    {t('Categories')}
                                </NavLink>
                                <NavLink
                                    href={route('private-discussions.index')}
                                    active={route().current('private-discussions.*')}
                                >
                                    {t('Private Discussions')}
                                </NavLink>
                                {user.role === 'admin' && (
                                    <NavLink
                                        href={route('admin.users.index')}
                                        active={route().current('admin.users.*')}
                                    >
                                        {t('Administration')}
                                    </NavLink>
                                )}
                            </div>
                        </div>

                        <div className="hidden sm:ms-6 sm:flex sm:items-center">
                            <LanguageSwitcher />
                            <div className="relative ms-3">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center rounded-md border border-sky-200 bg-sky-50 px-3 py-2 text-sm font-semibold leading-4 text-blue-700 transition duration-150 ease-in-out hover:border-amber-400 hover:bg-white hover:text-blue-800 focus:outline-none"
                                            >
                                                {t('My Account')}

                                                <svg
                                                    className="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link
                                            href={route('posts.mine')}
                                        >
                                            {t('My Posts')}
                                        </Dropdown.Link>
                                        <Dropdown.Link
                                            href={route('profile.edit')}
                                        >
                                            {t('Profile')}
                                        </Dropdown.Link>
                                        <Dropdown.Link
                                            href={route('logout')}
                                            method="post"
                                            as="button"
                                        >
                                            {t('Log Out')}
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-me-2 flex items-center sm:hidden">
                            <button
                                onClick={() =>
                                    setShowingNavigationDropdown(
                                        (previousState) => !previousState,
                                    )
                                }
                                className="inline-flex items-center justify-center rounded-md p-2 text-slate-600 transition duration-150 ease-in-out hover:bg-sky-100 hover:text-blue-700 focus:bg-sky-100 focus:text-blue-700 focus:outline-none"
                            >
                                <svg
                                    className="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        className={
                                            !showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={
                                            showingNavigationDropdown
                                                ? 'inline-flex'
                                                : 'hidden'
                                        }
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    className={
                        (showingNavigationDropdown ? 'block' : 'hidden') +
                        ' sm:hidden'
                    }
                >
                    <div className="space-y-1 border-t border-sky-200 bg-white px-3 pb-3 pt-2">
                        <div className="px-2 pb-2">
                            <LanguageSwitcher compact />
                        </div>
                        <ResponsiveNavLink
                            href={route('forum.index')}
                            active={route().current('forum.index')}
                        >
                            {t('Forum')}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('subforums.index')}
                            active={route().current('subforums.index')}
                        >
                            {t('Categories')}
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('private-discussions.index')}
                            active={route().current('private-discussions.*')}
                        >
                            {t('Private Discussions')}
                        </ResponsiveNavLink>
                        {user.role === 'admin' && (
                            <ResponsiveNavLink
                                href={route('admin.users.index')}
                                active={route().current('admin.users.*')}
                            >
                                {t('Administration')}
                            </ResponsiveNavLink>
                        )}
                    </div>

                    <div className="border-t border-sky-200 bg-white pb-1 pt-4">
                        <div className="px-4">
                            <div className="text-base font-medium text-slate-900">
                                {user.name}
                            </div>
                            <div className="text-sm font-medium text-slate-600">
                                {user.email}
                            </div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink
                                href={route('posts.mine')}
                                active={route().current('posts.mine')}
                            >
                                {t('My Posts')}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink href={route('profile.edit')}>
                                {t('Profile')}
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                            >
                                {t('Log Out')}
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <main>{children}</main>
        </div>
    );
}
