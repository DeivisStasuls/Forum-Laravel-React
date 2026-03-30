import { router } from '@inertiajs/react';
import useI18n from '@/hooks/useI18n';

export default function LanguageSwitcher({ compact = false }) {
    const { locale, t } = useI18n();

    const switchLocale = (nextLocale) => {
        if (nextLocale === locale) {
            return;
        }

        router.post(
            route('locale.update'),
            { locale: nextLocale },
            {
                preserveState: true,
                preserveScroll: true,
            },
        );
    };

    if (compact) {
        return (
            <div className="flex items-center gap-1 rounded-md border border-sky-200 bg-white px-1 py-1 text-xs">
                <button
                    type="button"
                    onClick={() => switchLocale('en')}
                    className={`rounded px-2 py-1 ${locale === 'en' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}`}
                >
                    EN
                </button>
                <button
                    type="button"
                    onClick={() => switchLocale('lv')}
                    className={`rounded px-2 py-1 ${locale === 'lv' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}`}
                >
                    LV
                </button>
            </div>
        );
    }

    return (
        <div className="flex items-center gap-2 text-sm">
            <span className="text-slate-600">{t('Language')}:</span>
            <button
                type="button"
                onClick={() => switchLocale('en')}
                className={`rounded px-2 py-1 ${locale === 'en' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}`}
            >
                EN
            </button>
            <button
                type="button"
                onClick={() => switchLocale('lv')}
                className={`rounded px-2 py-1 ${locale === 'lv' ? 'bg-indigo-100 text-indigo-700' : 'text-slate-600 hover:bg-slate-100'}`}
            >
                LV
            </button>
        </div>
    );
}

