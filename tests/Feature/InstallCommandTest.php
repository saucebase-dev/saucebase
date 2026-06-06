<?php

namespace Tests\Feature;

use App\Console\Commands\SauceBase\InstallCommand;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    // -------------------------------------------------------------------------
    // fetchPackageFrameworks
    // -------------------------------------------------------------------------

    public function test_fetch_package_frameworks_reads_saucebase_extra_field(): void
    {
        Http::fake([
            'raw.githubusercontent.com/saucebase-dev/auth/main/composer.json' => Http::response([
                'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
            ]),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue', 'react'], $cmd->exposedFetchPackageFrameworks('saucebase/auth'));
    }

    public function test_fetch_package_frameworks_defaults_to_vue_when_field_missing(): void
    {
        Http::fake([
            'raw.githubusercontent.com/saucebase-dev/billing/main/composer.json' => Http::response([
                'extra' => ['laravel' => ['providers' => []]],
            ]),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue'], $cmd->exposedFetchPackageFrameworks('saucebase/billing'));
    }

    public function test_fetch_package_frameworks_defaults_to_vue_on_api_failure(): void
    {
        Http::fake([
            'raw.githubusercontent.com/saucebase-dev/no-such-module/main/composer.json' => Http::response([], 500),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue'], $cmd->exposedFetchPackageFrameworks('saucebase/no-such-module'));
    }

    public function test_fetch_package_frameworks_reads_local_composer_json(): void
    {
        // No Http::fake() — Http::preventStrayRequests() will throw if HTTP is called
        // Use a fake slug that can never collide with a real installed module.
        $modulesDir = base_path('modules/test-fixture');
        @mkdir($modulesDir, 0755, true);
        file_put_contents($modulesDir.'/composer.json', json_encode([
            'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
        ]));

        try {
            $cmd = new TestableInstallCommand;
            $this->assertSame(['vue', 'react'], $cmd->exposedFetchPackageFrameworks('saucebase/test-fixture'));
        } finally {
            unlink($modulesDir.'/composer.json');
            @rmdir($modulesDir);
        }
    }

    public function test_fetch_package_frameworks_falls_back_to_github_when_no_local_file(): void
    {
        Http::fake([
            'raw.githubusercontent.com/saucebase-dev/billing/main/composer.json' => Http::response([
                'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
            ]),
        ]);

        // No modules/billing/composer.json on disk → must fall back to GitHub
        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue', 'react'], $cmd->exposedFetchPackageFrameworks('saucebase/billing'));
        Http::assertSent(fn ($req) => str_contains($req->url(), 'saucebase-dev/billing'));
    }

    // -------------------------------------------------------------------------
    // filterModulesByFramework
    // -------------------------------------------------------------------------

    public function test_filter_keeps_all_packages_for_vue(): void
    {
        $cmd = new TestableInstallCommand;
        $cmd->frameworkFixtures = [
            'saucebase/auth' => ['vue', 'react'],
            'saucebase/billing' => ['vue'],
            'saucebase/themes' => ['vue'],
        ];

        $result = $cmd->exposedFilterModulesByFramework(
            ['saucebase/auth', 'saucebase/billing', 'saucebase/themes'],
            'vue'
        );

        $this->assertSame(['saucebase/auth', 'saucebase/billing', 'saucebase/themes'], $result);
    }

    public function test_filter_removes_vue_only_packages_for_react(): void
    {
        $cmd = new TestableInstallCommand;
        $cmd->frameworkFixtures = [
            'saucebase/auth' => ['vue', 'react'],
            'saucebase/billing' => ['vue'],
            'saucebase/themes' => ['vue'],
        ];

        $result = $cmd->exposedFilterModulesByFramework(
            ['saucebase/auth', 'saucebase/billing', 'saucebase/themes'],
            'react'
        );

        $this->assertSame(['saucebase/auth'], $result);
    }

    public function test_filter_defaults_missing_field_to_vue_only(): void
    {
        $cmd = new TestableInstallCommand;

        Http::fake([
            'raw.githubusercontent.com/saucebase-dev/billing/main/composer.json' => Http::response([
                'extra' => [],
            ]),
        ]);

        $result = $cmd->exposedFilterModulesByFramework(['saucebase/billing'], 'react');

        $this->assertSame([], $result, 'Module without frameworks field should not appear for react');
    }

    // -------------------------------------------------------------------------
    // Deferred stack — saucebase:stack must NOT run during captureStack()
    // -------------------------------------------------------------------------

    public function test_stack_command_is_not_called_during_stack_capture(): void
    {
        $this->fakePackagistList();
        $spy = (object) ['stackCallCount' => 0];

        app()->bind(InstallCommand::class, function () use ($spy) {
            $cmd = new class extends InstallCommand
            {
                public object $spy;

                public function handle(): int
                {
                    $this->captureStack();

                    return self::SUCCESS;
                }

                public function call($command, array $arguments = [], $outputBuffer = null): int
                {
                    if ($command === 'saucebase:stack') {
                        $this->spy->stackCallCount++;
                    }

                    return 0;
                }
            };
            $cmd->spy = $spy;

            return $cmd;
        });

        $this->artisan('saucebase:install vue')->assertSuccessful();

        $this->assertSame(0, $spy->stackCallCount, 'saucebase:stack must not fire during captureStack()');
    }

    public function test_stack_command_is_called_exactly_once_during_install(): void
    {
        $this->fakePackagistList();
        $spy = (object) ['stackCallCount' => 0];

        app()->bind(InstallCommand::class, function () use ($spy) {
            $cmd = new class extends InstallCommand
            {
                public object $spy;

                protected function isCI(): bool { return false; }

                protected function ensureEnvFile(): bool { return true; }

                protected function generateApplicationKey(): void {}

                protected function setupDatabase(): void {}

                protected function setupModules(): void {}

                protected function createStorageLink(): void {}

                protected function clearCaches(): void {}

                protected function displaySuccess(): void {}

                public function call($command, array $arguments = [], $outputBuffer = null): int
                {
                    if ($command === 'saucebase:stack') {
                        $this->spy->stackCallCount++;
                    }

                    return 0;
                }
            };
            $cmd->spy = $spy;

            return $cmd;
        });

        $this->artisan('saucebase:install vue --all-modules')->assertSuccessful();

        $this->assertSame(1, $spy->stackCallCount, 'saucebase:stack must be called exactly once during install()');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function fakePackagistList(): void
    {
        Http::fake([
            'packagist.org/packages/list.json*' => Http::response([
                'packages' => [
                    'saucebase/auth' => ['abandoned' => false],
                ],
            ]),
            'raw.githubusercontent.com/saucebase-dev/auth/main/composer.json' => Http::response([
                'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
            ]),
        ]);
    }
}

/**
 * Exposes protected methods for direct testing.
 *
 * @internal
 */
class TestableInstallCommand extends InstallCommand
{
    /** @var array<string, string[]> Pre-built framework map (overrides HTTP for filtering tests). */
    public array $frameworkFixtures = [];

    public function exposedFetchPackageFrameworks(string $package): array
    {
        return $this->fetchPackageFrameworks($package);
    }

    /** @param  string[]  $packages */
    public function exposedFilterModulesByFramework(array $packages, string $framework): array
    {
        return $this->filterModulesByFramework($packages, $framework);
    }

    protected function fetchPackageFrameworks(string $package): array
    {
        if (isset($this->frameworkFixtures[$package])) {
            return $this->frameworkFixtures[$package];
        }

        return parent::fetchPackageFrameworks($package);
    }
}
