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
                    ? 'bg-blue-50 text-blue-700'
                    : 'text-slate-700 hover:bg-blue-50 hover:text-blue-700'
            } text-sm font-semibold transition duration-150 ease-in-out focus:outline-none ${className}`}
        >
            {children}
        </Link>
    );
}
