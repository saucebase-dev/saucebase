export type Translations = Record<string, string>;

/**
 * The active language's strings, shared by every caller.
 *
 * Kept outside any component so code that runs outside one, such as a module's
 * `setup()` or an action it registers, translates too. The stack's i18n provider
 * replaces them whenever the language changes.
 */
let current: Translations = {};

export function setTranslations(translations: Translations): void {
    current = translations;
}

/** `key` in the active language, with each `:placeholder` filled in. */
export function trans(
    key: string,
    replacements: Record<string, string | number> = {},
): string {
    return Object.entries(replacements).reduce(
        (value, [placeholder, replacement]) =>
            value.replaceAll(`:${placeholder}`, String(replacement)),
        current[key] ?? key,
    );
}
