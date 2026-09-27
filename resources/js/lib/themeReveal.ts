/**
 * The circle reveal used whenever the page's look changes: light/dark, and the themes
 * module's colour themes. One implementation so every switch animates the same way.
 *
 * `transitions.css` animates `::view-transition-new(root)` while `html[data-theme-reveal]`
 * is set; the flag also tells overlays that a click landing on the transition layer is
 * not a click outside them.
 */
export type RevealOrigin = { x: number; y: number };

/** The centre of the element that was clicked, measured before any menu closes. */
export function revealOrigin(element: Element): RevealOrigin {
    const rect = element.getBoundingClientRect();

    return { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
}

/** Whether a reveal is on screen right now. */
export function isRevealing(): boolean {
    return (
        typeof document !== 'undefined' &&
        document.documentElement.dataset.themeReveal !== undefined
    );
}

/**
 * Run `apply` inside a circle reveal from `origin`, or directly when the browser has no
 * view transitions or the user prefers reduced motion.
 */
export async function revealTransition(
    origin: RevealOrigin | null,
    apply: () => void | Promise<void>,
): Promise<void> {
    if (
        !document.startViewTransition ||
        window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        await apply();
        return;
    }

    const root = document.documentElement;
    const width = window.innerWidth;
    const height = window.innerHeight;
    const { x, y } = origin ?? { x: width / 2, y: height / 2 };
    const endRadius = Math.hypot(
        Math.max(x, width - x),
        Math.max(y, height - y),
    );

    /** Circle percentages use the reference box's normalized diagonal. */
    const radiusReference = Math.hypot(width, height) / Math.SQRT2;

    root.style.setProperty('--theme-reveal-x', `${(x / width) * 100}%`);
    root.style.setProperty('--theme-reveal-y', `${(y / height) * 100}%`);
    root.style.setProperty(
        '--theme-reveal-radius',
        `${(endRadius / radiusReference) * 100}%`,
    );
    root.dataset.themeReveal = '';

    try {
        // A skipped transition still applies the change and resolves finished.
        await document.startViewTransition(apply).finished;
    } finally {
        delete root.dataset.themeReveal;
        root.style.removeProperty('--theme-reveal-x');
        root.style.removeProperty('--theme-reveal-y');
        root.style.removeProperty('--theme-reveal-radius');
    }
}
