import { expect, test } from '@playwright/test';

/**
 * The brand as a browser actually sees it.
 *
 * Deliberately read-only. An earlier version of this file wrote to the `settings` table
 * to prove that a configured name reached the frontend — which made every other spec
 * flaky, because Playwright runs spec files in parallel workers and those settings are
 * global to the application. `describe.serial` does not help: it orders tests within one
 * file, not across files.
 *
 * That coverage was not lost, it moved: core's GeneralSettingsTest asserts the same
 * thing against Inertia's props, where it costs nothing and cannot race. What is left
 * here is what only a browser can answer — that the artwork is reachable and the head
 * offers the right variant for the right colour scheme.
 */
test.describe('Branding', () => {
    const assets = [
        'logo-on-light',
        'logo-on-dark',
        'icon-on-light',
        'icon-on-dark',
    ];

    for (const asset of assets) {
        test(`ships ${asset}.svg`, async ({ request }) => {
            const response = await request.get(`/images/${asset}.svg`);

            // The settings default to these paths, so a 404 here is a broken image on
            // every page rather than a missing test fixture.
            expect(response.status(), `/images/${asset}.svg`).toBe(200);
            expect(response.headers()['content-type']).toContain(
                'image/svg+xml',
            );
        });
    }

    test('offers a light and a dark favicon', async ({ page }) => {
        await page.goto('/');

        await expect(
            page.locator('link[rel="icon"]:not([media])'),
        ).toHaveAttribute('href', '/images/icon-on-light.svg');

        // The server cannot pick: `appearance` may be `system`, which only the client
        // resolves. `media` hands the choice to the browser.
        await expect(
            page.locator(
                'link[rel="icon"][media="(prefers-color-scheme: dark)"]',
            ),
        ).toHaveAttribute('href', '/images/icon-on-dark.svg');
    });

    test('titles the page with the configured site name', async ({ page }) => {
        await page.goto('/');

        // No tagline is set by default, so the name stands alone. Asserting the shipped
        // value rather than writing one keeps this spec safe to run beside any other.
        await expect(page).toHaveTitle('Saucebase');
    });
});
