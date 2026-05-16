import { useCallback, useEffect, useState } from 'react';

export type Theme = 'light' | 'dark' | 'auto';

const STORAGE_KEY = 'appearance';

function setCookie(value: Theme): void {
    document.cookie = `appearance=${value};path=/;max-age=${365 * 24 * 60 * 60};SameSite=Lax`;
}

function applyTheme(theme: Theme): void {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    const isDark = theme === 'dark' || (theme === 'auto' && prefersDark);
    document.documentElement.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
}

export function initializeTheme(): void {
    const stored = (localStorage.getItem(STORAGE_KEY) as Theme) || 'auto';
    applyTheme(stored);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        const current = (localStorage.getItem(STORAGE_KEY) as Theme) || 'auto';
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

    const setTheme = useCallback((next: Theme, triggerEl?: HTMLElement) => {
        localStorage.setItem(STORAGE_KEY, next);
        setCookie(next);

        if (!document.startViewTransition || window.matchMedia('(prefers-reduced-motion: reduce)').matches || !triggerEl) {
            setThemeState(next);
            return;
        }

        const rect = triggerEl.getBoundingClientRect();
        const x = rect.left + rect.width / 2;
        const y = rect.top + rect.height / 2;
        const endRadius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));

        const transition = document.startViewTransition(() => setThemeState(next));
        transition.ready.then(() => {
            document.documentElement.animate(
                { clipPath: [`circle(0px at ${x}px ${y}px)`, `circle(${endRadius}px at ${x}px ${y}px)`] },
                { duration: 500, easing: 'ease-in-out', pseudoElement: '::view-transition-new(root)' },
            );
        });
    }, []);

    return { theme, setTheme };
}
