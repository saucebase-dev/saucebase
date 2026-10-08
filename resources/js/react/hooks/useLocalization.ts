import { useHttp } from '@/hooks/useHttp';
import { useTranslation } from '@/i18n';
import { usePage } from '@inertiajs/react';

export function useLocalization() {
    const { locale, setLocale } = useTranslation();
    const page = usePage();
    const { post } = useHttp();

    const locales = (page.props?.locales as Record<string, string>) ?? {};

    const setLanguage = async (lang: string) => {
        // A failure is shown and the language stays as it was.
        await post(route('locale', { locale: lang }), {
            onSuccess: () => setLocale(lang),
        });
    };

    return { language: locale, locales, setLanguage };
}
