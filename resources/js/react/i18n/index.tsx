import {
    createContext,
    useCallback,
    useContext,
    useMemo,
    useState,
} from 'react';

type Translations = Record<string, string>;

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
    const [translations, setTranslations] = useState<Translations>(() =>
        loadTranslations(initialLocale),
    );

    const handleSetLocale = useCallback((newLocale: string) => {
        setLocale(newLocale);
        setTranslations(loadTranslations(newLocale));
    }, []);

    const t = useCallback(
        (
            key: string,
            replacements?: Record<string, string | number>,
        ): string => {
            let value = translations[key] ?? key;

            if (replacements) {
                for (const [placeholder, replacement] of Object.entries(
                    replacements,
                )) {
                    value = value.replace(
                        new RegExp(`:${placeholder}`, 'g'),
                        String(replacement),
                    );
                }
            }

            return value;
        },
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
