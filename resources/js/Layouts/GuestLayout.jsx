import ApplicationLogo from '@/Components/ApplicationLogo';
import LanguageSwitcher from '@/Components/LanguageSwitcher';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-slate-950 px-4 pt-6 sm:justify-center sm:pt-0">
            <div className="absolute right-4 top-4">
                <LanguageSwitcher compact />
            </div>
            <div>
                <Link href="/">
                    <div className="flex items-center">
                        <ApplicationLogo className="h-16 w-auto" />
                    </div>
                </Link>
            </div>

            <div className="mt-6 w-full overflow-hidden rounded-2xl border border-slate-200 bg-white px-6 py-5 shadow-xl sm:max-w-md">
                {children}
            </div>
        </div>
    );
}
