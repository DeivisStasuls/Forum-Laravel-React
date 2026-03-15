import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={`flex w-full items-center rounded-md px-3 py-2 ${
                active
                    ? 'bg-slate-700 text-white'
                    : 'text-slate-200 hover:bg-slate-800 hover:text-white'
            } text-sm font-medium transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
