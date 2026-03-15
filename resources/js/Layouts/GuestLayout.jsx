import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-slate-950 px-4 pt-6 sm:justify-center sm:pt-0">
            <div>
                <Link href="/">
                    <div className="flex items-center gap-3">
                        <ApplicationLogo className="h-10 w-10 fill-current text-indigo-300" />
                        <span className="text-lg font-semibold text-white">
                            Forum Hub
                        </span>
                    </div>
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-xl sm:max-w-md">
                {children}
            </div>
        </div>
    );
}
