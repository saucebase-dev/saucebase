import { expect, test } from '@e2e/fixtures';
import { spawn, type ChildProcess } from 'node:child_process';

/**
 * What the server renderer produces, asked directly.
 *
 * A page looks the same in a browser whether the renderer worked or not: when it
 * fails, the app falls back to rendering in the browser. So this spec starts the
 * built renderer itself and reads the HTML it returns.
 */
const RENDERER = 'http://127.0.0.1:13714';

type MenuItem = { url?: string; route?: string; active?: boolean };

// One renderer on one port: the tests must share a worker.
test.describe.configure({ mode: 'serial' });

test.describe('Server rendering', () => {
    let renderer: ChildProcess;

    test.beforeAll(async () => {
        renderer = spawn('node', ['bootstrap/ssr/ssr.js'], { stdio: 'ignore' });

        await expect
            .poll(() =>
                fetch(`${RENDERER}/health`).then(
                    (response) => response.ok,
                    () => false,
                ),
            )
            .toBe(true);
    });

    test.afterAll(() => {
        renderer.kill();
    });

    test('knows which page it is rendering', async ({
        page,
        loginAs,
        credentials,
    }) => {
        await loginAs(credentials.user);
        await page.goto('/dashboard');

        const inertiaPage = JSON.parse(
            (await page.locator('script[data-page]').textContent()) ?? '',
        );

        // The server normally says which item is current. An item that only names
        // its route leaves that to the renderer, which has to work it out from the
        // routes and this request's URL.
        const items = Object.values<MenuItem[]>(
            inertiaPage.props.navigation,
        ).flat();
        const dashboard = items.find((item) =>
            item.url?.endsWith('/dashboard'),
        );
        expect(dashboard).toBeDefined();
        delete dashboard!.active;
        dashboard!.route = 'dashboard';

        const response = await page.request.post(`${RENDERER}/render`, {
            data: inertiaPage,
        });
        expect(response.status(), await response.text()).toBe(200);

        await page.setContent((await response.json()).body);

        await expect(
            page.locator('a[data-active="true"][href$="/dashboard"]'),
        ).toHaveCount(1);
    });
});
