import { defineConfig } from 'vitest/config';

/**
 * Unit tests for framework-neutral code only: `lib/` in modules. Saucebase's own lib
 * is tested in saucebase/core (`resources/js`).
 *
 * Kept apart from vite.config.js so tests never load the Laravel, Inertia or
 * framework plugins, and run the same whichever stack is selected. Components and
 * DOM behaviour are covered by Playwright.
 */
export default defineConfig({
    test: {
        include: ['modules/*/resources/js/lib/**/*.test.ts'],
        environment: 'node',
    },
});
