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
                'inline-flex h-full items-center border-b-2 border-transparent px-2 text-sm font-semibold uppercase tracking-wide transition duration-150 ease-in-out focus:outline-none ' +
                (active
                    ? 'border-blue-700 text-blue-700'
                    : 'text-slate-700 hover:border-amber-500 hover:text-blue-700') +
                className
            }
        >
            {children}
        </Link>
    );
}
