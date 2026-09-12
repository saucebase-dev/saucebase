import { useCallback, useEffect, useState } from 'react';

export type Theme = 'light' | 'dark' | 'auto';

const STORAGE_KEY = 'appearance';

function setCookie(value: Theme): void {
    document.cookie = `appearance=${value};path=/;max-age=${365 * 24 * 60 * 60};SameSite=Lax`;
}

function applyTheme(theme: Theme): void {
    const prefersDark = window.matchMedia(
        '(prefers-color-scheme: dark)',
    ).matches;
    const isDark = theme === 'dark' || (theme === 'auto' && prefersDark);
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
}

export type TransitionOrigin = { x: number; y: number };

/** Measure the rendered option before the menu handles selection and closes. */
export function transitionOrigin(event: {
    currentTarget: EventTarget | null;
}): TransitionOrigin {
    const rect = (event.currentTarget as HTMLElement).getBoundingClientRect();

    return {
        x: rect.left + rect.width / 2,
        y: rect.top + rect.height / 2,
    };
}

export function initializeTheme(): void {
    const stored = (localStorage.getItem(STORAGE_KEY) as Theme) || 'auto';
    applyTheme(stored);

    window
        .matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', () => {
            const current =
                (localStorage.getItem(STORAGE_KEY) as Theme) || 'auto';
            if (current === 'auto') applyTheme('auto');
        });
}

export function useTheme() {
    const [theme, setThemeState] = useState<Theme>(
        () => (localStorage.getItem(STORAGE_KEY) as Theme) || 'auto',
    );

    useEffect(() => {
        applyTheme(theme);
    }, [theme]);

    const setTheme = useCallback(
        (next: Theme, origin: TransitionOrigin, animate = true) => {
            localStorage.setItem(STORAGE_KEY, next);
            setCookie(next);

            const apply = () => {
                // Applied to the DOM here, not only through state: the effect runs
                // after the view transition has already captured the new snapshot.
                applyTheme(next);
                setThemeState(next);
            };

            if (
                !animate ||
                !document.startViewTransition ||
                window.matchMedia('(prefers-reduced-motion: reduce)').matches
            ) {
                apply();
                return;
            }

            const root = document.documentElement;
            const { x, y } = origin;
            const width = window.innerWidth;
            const height = window.innerHeight;
            const endRadius = Math.hypot(
                Math.max(x, width - x),
                Math.max(y, height - y),
            );

            /** Circle percentages use the reference box's normalized diagonal. */
            const radiusReference = Math.hypot(width, height) / Math.SQRT2;

            root.style.setProperty('--theme-reveal-x', `${(x / width) * 100}%`);
            root.style.setProperty(
                '--theme-reveal-y',
                `${(y / height) * 100}%`,
            );
            root.style.setProperty(
                '--theme-reveal-radius',
                `${(endRadius / radiusReference) * 100}%`,
            );
            root.setAttribute('data-theme-reveal', '');

            const transition = document.startViewTransition(apply);

            // A skipped transition still applies the theme and resolves finished.
            transition.finished.finally(() => {
                root.removeAttribute('data-theme-reveal');
                root.style.removeProperty('--theme-reveal-x');
                root.style.removeProperty('--theme-reveal-y');
                root.style.removeProperty('--theme-reveal-radius');
            });
        },
        [],
    );

    return { theme, setTheme };
}
