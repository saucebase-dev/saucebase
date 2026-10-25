/// <reference types="node" />
import { defineConfig, devices } from '@playwright/test';
import { laravelServer, type LaravelOptions } from '@saucebase/laravel-playwright';
import { config } from 'dotenv';
import { saucebaseProjects } from './vendor/saucebase/core/resources/js/modules.ts';

// The suite runs on its own server and database, never the app's (.env.e2e).
// Loading .env here instead would hand that server your database.
const e2e = config({ path: '.env.e2e', quiet: true }).parsed ?? {};
// From the file, not process.env: an APP_URL exported for the dev app would win there,
// and the suite would test it while laravelServer() serves .env.e2e's.
const BASE_URL = e2e.APP_URL ?? 'http://localhost:8100';

async function createConfig() {
    // database.setup, every module's setups and projects, per device, in order.
    const projects = await saucebaseProjects({
        devices: {
            'Desktop Chrome': devices['Desktop Chrome'],
            // 'Desktop Firefox': devices['Desktop Firefox'],
            // 'Desktop Safari': devices['Desktop Safari'],
            // 'iPhone 14': devices['iPhone 14'],
        },
        // The app's own tests; core adds the modules'.
        projects: [{ name: '@Core', testDir: './tests/e2e' }],
    });

    /**
     * @see https://playwright.dev/docs/test-configuration
     */
    return defineConfig<LaravelOptions>({
        /* Run tests in files in parallel */
        fullyParallel: true,
        /* Fail the build on CI if you accidentally left test.only in the source code. */
        forbidOnly: !!process.env.CI,
        /* Locally one retry, so a single timing flake under load doesn't fail the run.
           A test that only passes on retry is still reported as flaky. */
        retries: process.env.CI ? 2 : 1,
        /* Opt out of parallel tests on CI. Locally, more than two browsers
           loading pages at once outruns the PHP worker pool behind the app. */
        workers: process.env.CI ? 1 : 2,
        /* Reporter to use. See https://playwright.dev/docs/test-reporters */
        reporter: [['html', { outputFolder: 'playwright-report' }], ['list']],
        /* Folder for test artifacts such as screenshots, videos, traces, etc. */
        testDir: './tests/e2e',
        /* Shared settings for all the projects below. See https://playwright.dev/docs/api/class-testoptions. */
        use: {
            /* Base URL to use in actions like `await page.goto('/')`. */
            baseURL: BASE_URL,

            /* Collect trace when retrying the failed test. See https://playwright.dev/docs/trace-viewer */
            trace: 'on-first-retry',

            /* Take screenshot only on failures */
            screenshot: 'only-on-failure',

            /* Record video only on failures */
            video: 'retain-on-failure',

            /* Ignore HTTPS errors */
            ignoreHTTPSErrors: true,

            /** Laravel Playwright integration */
            laravelBaseUrl: `${BASE_URL}/playwright`,
            laravelSecret: e2e.PLAYWRIGHT_SECRET,
        },

        /* Configure projects for major browsers */
        projects,

        /* Migrates .env.e2e's database, then serves it on APP_URL's port. */
        webServer: laravelServer(),

        /**
         * Tests run against built assets, the way CI does.
         *
         * The dev server compiles on demand, and several browsers loading pages
         * at once starve PHP-FPM behind it: the auth suite lost 11 tests to
         * timeouts on the dev server and none on a build. Rebuild after changing
         * frontend code.
         */
        ...(!process.env.CI && { globalSetup: './tests/e2e/assets.setup.ts' }),
    });
}

export default createConfig();
