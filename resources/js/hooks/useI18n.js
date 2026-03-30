import { usePage } from '@inertiajs/react';

export default function useI18n() {
    const props = usePage().props;
    const translations = props.translations || {};
    const locale = props.locale || 'en';

    const t = (key) => translations[key] ?? key;

    return { t, locale };
}

