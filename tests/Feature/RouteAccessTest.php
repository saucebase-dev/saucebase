<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Which of this application's routes are behind the sign-in wall, and which are not.
 *
 * The pages themselves belong elsewhere — the settings modal to saucebase/core, the
 * dashboard to this application, the locale switcher to core — but the guards on them
 * are declared here, in routes/web.php, and that is a decision worth pinning. Both
 * directions matter: a guarded route quietly leaving the group exposes it, and a
 * public page joining it locks visitors out of the terms they have to agree to.
 *
 * Asserted on the middleware rather than by making requests: where an unauthenticated
 * visitor is *sent* is the auth module's business, and with no module installed there
 * is no login route to redirect to.
 */
class RouteAccessTest extends TestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function guardedRoutes(): array
    {
        return [
            'dashboard' => ['dashboard'],
            'settings' => ['settings'],
        ];
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicRoutes(): array
    {
        return [
            'index' => ['index'],
            'privacy' => ['privacy'],
            'terms' => ['terms'],
            'locale' => ['locale'],
        ];
    }

    #[DataProvider('guardedRoutes')]
    public function test_signed_in_pages_are_behind_authentication(string $name): void
    {
        $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();

        $this->assertContains('auth', $middleware);
        $this->assertContains('verified', $middleware);

        // Which roles, rather than that there are roles, is left unpinned: the set is
        // expected to change (see sc-690), and this test has nothing to say about it.
        $this->assertNotEmpty(
            array_filter($middleware, fn (string $m): bool => str_starts_with($m, 'role:')),
            "Route [{$name}] should carry a role guard.",
        );
    }

    #[DataProvider('publicRoutes')]
    public function test_public_pages_are_reachable_without_signing_in(string $name): void
    {
        $middleware = Route::getRoutes()->getByName($name)->gatherMiddleware();

        $this->assertNotContains('auth', $middleware);
    }
}
