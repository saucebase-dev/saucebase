import { test, expect } from '@e2e/fixtures';

test.describe.parallel('Sidebar layout', () => {
    test('renders tenant switcher in sidebar header', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');
        await expect(page).toHaveURL('/dashboard');

        await expect(page.getByTestId('tenant-switcher')).toBeVisible();
    });

    test('user dropdown contains language and theme selectors', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await page.getByTestId('user-menu-trigger').click();

        await expect(page.getByTestId('language-selector-trigger')).toBeVisible();
        await expect(page.getByTestId('theme-selector-trigger')).toBeVisible();
    });

    test('language selector submenu opens', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await page.getByTestId('user-menu-trigger').click();
        await page.getByTestId('language-selector-trigger').click();

        await expect(page.locator('[data-slot="dropdown-menu-sub-content"]')).toBeVisible();
    });

    test('theme selector submenu opens', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await page.getByTestId('user-menu-trigger').click();
        await page.getByTestId('theme-selector-trigger').click();

        await expect(page.locator('[data-slot="dropdown-menu-sub-content"]')).toBeVisible();
    });
});

test.describe('Theme persistence', () => {
    test('dark theme persists across navigation via cookie', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/dashboard/);

        // Select dark via the UI — this writes localStorage + cookie
        await page.getByTestId('user-menu-trigger').click();
        await page.getByTestId('theme-selector-trigger').click();
        await page.getByRole('menuitem', { name: 'Dark' }).click();

        await expect(page.locator('html')).toHaveClass(/dark/);

        // Navigate to another page and back — server should render class="dark" from cookie
        await page.goto(page.url());

        await expect(page.locator('html')).toHaveClass(/dark/);
    });

    test('light theme does not add dark class after navigation', async ({ page, credentials, loginAs }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/dashboard/);

        // Select light via the UI
        await page.getByTestId('user-menu-trigger').click();
        await page.getByTestId('theme-selector-trigger').click();
        await page.getByRole('menuitem', { name: 'Light' }).click();

        await expect(page.locator('html')).not.toHaveClass(/dark/);

        await page.goto(page.url());

        await expect(page.locator('html')).not.toHaveClass(/dark/);
    });

    test('system preference dark mode applied before hydration', async ({ page, credentials, loginAs }) => {
        await page.emulateMedia({ colorScheme: 'dark' });
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        await expect(page.locator('html')).toHaveClass(/dark/);
    });
});
