import { revealTransition, type RevealOrigin } from '@js/lib/themeReveal';
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
        (next: Theme, origin: RevealOrigin, animate = true) => {
            localStorage.setItem(STORAGE_KEY, next);
            setCookie(next);

            const apply = () => {
                // Applied to the DOM here, not only through state: the effect runs
                // after the view transition has already captured the new snapshot.
                applyTheme(next);
                setThemeState(next);
            };

            if (!animate) {
                apply();
                return;
            }

            void revealTransition(origin, apply);
        },
        [],
    );

    return { theme, setTheme };
}
