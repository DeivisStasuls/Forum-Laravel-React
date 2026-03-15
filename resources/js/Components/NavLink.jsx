import { Link } from '@inertiajs/react';

export default function NavLink({
    active = false,
    className = '',
    children,
    ...props
}) {
    return (
        <Link
            {...props}
            className={
                'inline-flex items-center rounded-full px-3 py-1.5 text-sm font-medium transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'bg-white/15 text-white shadow-sm'
                    : 'text-slate-200 hover:bg-white/10 hover:text-white') +
                className
            }
        >
            {children}
        </Link>
    );
}
