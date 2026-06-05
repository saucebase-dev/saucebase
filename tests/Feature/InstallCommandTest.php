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
            'packagist.org/packages/saucebase/auth.json' => Http::response([
                'package' => [
                    'versions' => [
                        'v2.1.0' => [
                            'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
                        ],
                    ],
                ],
            ]),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue', 'react'], $cmd->exposedFetchPackageFrameworks('saucebase/auth'));
    }

    public function test_fetch_package_frameworks_defaults_to_vue_when_field_missing(): void
    {
        Http::fake([
            'packagist.org/packages/saucebase/billing.json' => Http::response([
                'package' => [
                    'versions' => [
                        'v1.0.0' => [
                            'extra' => ['laravel' => ['providers' => []]],
                        ],
                    ],
                ],
            ]),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue'], $cmd->exposedFetchPackageFrameworks('saucebase/billing'));
    }

    public function test_fetch_package_frameworks_defaults_to_vue_on_api_failure(): void
    {
        Http::fake([
            'packagist.org/packages/saucebase/auth.json' => Http::response([], 500),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue'], $cmd->exposedFetchPackageFrameworks('saucebase/auth'));
    }

    public function test_fetch_package_frameworks_skips_dev_versions(): void
    {
        Http::fake([
            'packagist.org/packages/saucebase/auth.json' => Http::response([
                'package' => [
                    'versions' => [
                        'dev-main' => [
                            'extra' => ['saucebase' => ['frameworks' => ['react']]],
                        ],
                        'v1.0.0' => [
                            'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
                        ],
                    ],
                ],
            ]),
        ]);

        $cmd = new TestableInstallCommand;
        $this->assertSame(['vue', 'react'], $cmd->exposedFetchPackageFrameworks('saucebase/auth'));
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
        // No fixtures — fetchPackageFrameworks will return ['vue'] by default when no HTTP match

        Http::fake([
            'packagist.org/packages/saucebase/billing.json' => Http::response([
                'package' => ['versions' => ['v1.0.0' => ['extra' => []]]],
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
            'packagist.org/packages/saucebase/auth.json' => Http::response([
                'package' => [
                    'versions' => [
                        'v2.1.0' => [
                            'extra' => ['saucebase' => ['frameworks' => ['vue', 'react']]],
                        ],
                    ],
                ],
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
