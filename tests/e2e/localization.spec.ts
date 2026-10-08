import { expect, test } from '@e2e/fixtures';

/**
 * Adding a language takes its translation file and the admin's switch, nothing else: the
 * selector offers it and the choice sticks. English is the only language that ships, so the
 * spec adds one for itself.
 *
 * The file is only seen by the server: the frontend bundles `lang/*.json` at build time, so
 * strings stay English here. What is ours is discovery, the admin switch, the selector and
 * the stored choice.
 */
test.describe('Adding a language', () => {
    test.beforeAll(async ({ laravel }) => {
        await laravel.callFunction(
            'Tests\\Support\\LocalizationSupport::addLanguage',
            ['pt_BR'],
        );
    });

    test.afterAll(async ({ laravel }) => {
        await laravel.callFunction(
            'Tests\\Support\\LocalizationSupport::removeLanguage',
            ['pt_BR'],
        );
    });

    test('the selector offers it and the choice sticks', async ({
        page,
        credentials,
        loginAs,
    }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await page.getByTestId('user-menu-trigger').click();
        await page.getByTestId('language-selector-trigger').click();
        const switched = page.waitForResponse(
            (response) =>
                response.url().endsWith('/locale/pt_BR') && response.ok(),
        );
        await page.getByTestId('language-option-pt_BR').click();
        await switched;

        // The server's answer, read before the frontend's i18n rewrites `<html lang>` to the
        // bundled language it fell back to: a new language reaches the bundle on the next build.
        const reloaded = await page.reload();
        expect(await reloaded?.text()).toContain('<html lang="pt-BR"');
    });
});
