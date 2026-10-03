import {
    setTranslations,
    trans,
    type Translations,
} from '@js/lib/translations';
import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
} from 'react';

export { trans };

interface I18nContextValue {
    t: (key: string, replacements?: Record<string, string | number>) => string;
    locale: string;
    setLocale: (locale: string) => void;
}

const I18nContext = createContext<I18nContextValue>({
    t: (key) => key,
    locale: 'en',
    setLocale: () => {},
});

const langGlobs = import.meta.glob<{ default: Translations }>('/lang/*.json', {
    eager: true,
});

const moduleLangGlobs = import.meta.glob<{ default: Translations }>(
    '/modules/*/lang/*.json',
    {
        eager: true,
    },
);

function loadTranslations(lang: string): Translations {
    const jsonData = langGlobs[`/lang/${lang}.json`]?.default ?? {};
    const phpData = langGlobs[`/lang/php_${lang}.json`]?.default ?? {};

    const moduleData: Translations = {};
    for (const [filePath, mod] of Object.entries(moduleLangGlobs)) {
        const match = filePath.match(
            /\/modules\/[^/]+\/lang\/(php_)?(.+)\.json$/,
        );
        if (match && match[2] === lang) {
            Object.assign(moduleData, mod.default ?? {});
        }
    }

    return { ...jsonData, ...phpData, ...moduleData };
}

interface I18nProviderProps {
    children: React.ReactNode;
    initialLocale?: string;
}

export function I18nProvider({
    children,
    initialLocale = 'en',
}: I18nProviderProps) {
    const [locale, setLocale] = useState(initialLocale);
    // Set during render, not in an effect, so the first render and the server
    // render already read the right language through `trans()`.
    const [translations, setLoaded] = useState<Translations>(() => {
        const loaded = loadTranslations(initialLocale);
        setTranslations(loaded);

        return loaded;
    });

    const handleSetLocale = useCallback((newLocale: string) => {
        const loaded = loadTranslations(newLocale);
        setTranslations(loaded);
        setLocale(newLocale);
        setLoaded(loaded);
    }, []);

    // A new function per language, so components using it re-render on a switch.
    // eslint-disable-next-line react-hooks/exhaustive-deps
    const t = useCallback(
        (key: string, replacements?: Record<string, string | number>) =>
            trans(key, replacements),
        [translations],
    );

    const value = useMemo(
        () => ({ t, locale, setLocale: handleSetLocale }),
        [t, locale, handleSetLocale],
    );

    return (
        <I18nContext.Provider value={value}>{children}</I18nContext.Provider>
    );
}

export function useT() {
    return useContext(I18nContext).t;
}

export function useTranslation() {
    return useContext(I18nContext);
}
