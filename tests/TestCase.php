<?php

namespace Tests;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Vite;

/**
 * Shared test setup for the application.
 *
 * A base test case in saucebase/core would let modules inherit this instead of
 * duplicating it, but the helpers worth sharing (createUser, AuthHelper,
 * TestFixtures) all manufacture App\Models\User, which core cannot name. Roles are
 * plain strings now (sc-690), so that is the only thing left in the way.
 *
 * @see https://app.shortcut.com/saucebase/story/701 Move core's tests into the package
 */
abstract class TestCase extends BaseTestCase
{
    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        // Point Vite at a hot file that never exists, so tests always resolve assets
        // through the build manifest — exactly as CI does.
        //
        // Laravel's Vite helper short-circuits to the dev server whenever public/hot
        // is present, skipping the manifest entirely. That file exists while
        // `npm run dev` is running, and the Playwright web server can leave a stale
        // one behind. Either way it silently hides missing-manifest-entry failures
        // locally and lets them through to CI.
        Vite::useHotFile(storage_path('framework/testing/vite-hot-file-that-never-exists'));
    }

    /** @return User&Authenticatable */
    protected function createUser(): User
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        return $user;
    }
}
