import { useTranslation } from '@/i18n';
import { useHttp, usePage } from '@inertiajs/react';

export function useLocalization() {
    const { locale, setLocale } = useTranslation();
    const page = usePage();
    const { post } = useHttp();

    const locales = (page.props?.locales as Record<string, string>) ?? {};

    const setLanguage = async (lang: string) => {
        try {
            await post(route('locale', { locale: lang }));
            setLocale(lang);
        } catch (error) {
            console.error('Error changing language', error);
            throw error;
        }
    };

    return { language: locale, locales, setLanguage };
}
