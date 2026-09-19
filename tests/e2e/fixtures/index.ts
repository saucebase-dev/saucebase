import { loginAs as doLoginAs } from '@e2e/helpers/auth';
import { expect } from '@playwright/test';
import { test as base } from '@saucebase/laravel-playwright';

export type UserCredential = { email: string; password: string };

export type TestCredentials = {
    admin: UserCredential;
    user: UserCredential;
    subscriber: UserCredential;
    cancelled: UserCredential;
    [key: string]: UserCredential;
};

export const test = base.extend<{
    credentials: TestCredentials;
    loginAs: (user: UserCredential) => Promise<void>;
    allowedPageErrors: RegExp[];
    failOnPageError: void;
}>({
    /**
     * Page errors a spec causes on purpose, as patterns.
     *
     * A spec that aborts a request to prove the UI copes will make the client
     * throw; that is the thing under test, not a defect. Everything else still
     * fails. Set with `test.use({ allowedPageErrors: [/Network error/] })`.
     */
    allowedPageErrors: [[], { option: true }],

    /**
     * An uncaught exception in the page fails the test.
     *
     * Playwright ignores them otherwise, which is how a dialog that overflowed
     * the stack every time it opened sat behind a passing "opens and closes"
     * spec.
     */
    failOnPageError: [
        async ({ page, allowedPageErrors }, use) => {
            const errors: string[] = [];
            page.on('pageerror', (error) => errors.push(error.message));

            await use();

            expect(
                errors.filter(
                    (message) =>
                        !allowedPageErrors.some((allowed) =>
                            allowed.test(message),
                        ),
                ),
                'uncaught page errors',
            ).toEqual([]);
        },
        { auto: true },
    ],
    credentials: async ({ laravel }, use) => {
        const creds = await laravel.callFunction<TestCredentials>(
            'Tests\\Support\\TestFixtures::credentials',
        );
        await use(creds);
    },
    loginAs: async ({ page, laravel }, use) => {
        await use((user) => doLoginAs(page, laravel, user));
    },
});

export { expect };
