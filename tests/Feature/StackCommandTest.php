<?php

namespace Tests\Feature;

use App\Console\Commands\SauceBase\StackCommand;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class StackCommandTest extends TestCase
{
    private Filesystem $files;

    private string $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->files = new Filesystem;
        $this->tmpDir = sys_get_temp_dir().'/saucebase-fw-test-'.uniqid();
        $this->files->makeDirectory($this->tmpDir.'/resources/js', 0755, true);
        $this->files->makeDirectory($this->tmpDir.'/resources/views', 0755, true);

        // Seed initial tracked files so git checkout works in reset tests
        file_put_contents($this->tmpDir.'/frontend.json', json_encode(['framework' => null]).PHP_EOL);
        file_put_contents($this->tmpDir.'/package.json', '{}');
        file_put_contents($this->tmpDir.'/vite.config.js', '// vite');
        file_put_contents($this->tmpDir.'/tsconfig.json', '{}');
        file_put_contents($this->tmpDir.'/eslint.config.js', '// eslint');
        file_put_contents($this->tmpDir.'/components.json', '{}');
        file_put_contents($this->tmpDir.'/resources/views/app.blade.php', '<!-- blade -->');

        // Init a real git repo so git update-index and git checkout work
        exec("git -C {$this->tmpDir} init -q 2>/dev/null");
        exec("git -C {$this->tmpDir} config user.email 'test@test.com' 2>/dev/null");
        exec("git -C {$this->tmpDir} config user.name 'Test' 2>/dev/null");
        exec("git -C {$this->tmpDir} add -A 2>/dev/null");
        exec("git -C {$this->tmpDir} commit -q -m 'initial' 2>/dev/null");

        $tmpDir = $this->tmpDir;
        app()->bind(StackCommand::class, fn () => new class(new Filesystem, $tmpDir, $tmpDir.'/resources/js') extends StackCommand {
            protected function runNpmInstall(): void {}
        });
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->tmpDir);
        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public function test_rejects_invalid_framework(): void
    {
        $this->artisan('saucebase:stack svelte')
            ->assertFailed()
            ->expectsOutputToContain('Invalid framework');
    }

    // -------------------------------------------------------------------------
    // Dev mode
    // -------------------------------------------------------------------------

    public function test_dev_mode_writes_frontend_json(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $data = json_decode(file_get_contents($this->tmpDir.'/frontend.json'), true);
        $this->assertSame('vue', $data['framework']);
        $this->assertTrue($data['dev']);
    }

    public function test_dev_mode_writes_app_and_ssr_shims(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->assertStringContainsString("import './vue/app'", file_get_contents($this->tmpDir.'/resources/js/app.ts'));
        $this->assertStringContainsString("import './vue/ssr'", file_get_contents($this->tmpDir.'/resources/js/ssr.ts'));
    }

    public function test_dev_mode_react_writes_tsx_entry_points(): void
    {
        $this->seedFakeStubs('react');

        $this->artisan('saucebase:stack react --dev')->assertSuccessful();

        $this->assertStringContainsString("import './react/app'", file_get_contents($this->tmpDir.'/resources/js/app.tsx'));
        $this->assertStringContainsString("import './react/ssr'", file_get_contents($this->tmpDir.'/resources/js/ssr.tsx'));
    }

    public function test_dev_mode_runs_npm_install(): void
    {
        $this->seedFakeStubs('vue');
        $spy = (object) ['called' => false];

        $tmpDir = $this->tmpDir;
        app()->bind(StackCommand::class, function () use ($tmpDir, $spy) {
            return new class(new Filesystem, $tmpDir, $tmpDir.'/resources/js', $spy) extends StackCommand {
                private object $spy;

                public function __construct(Filesystem $files, string $basePath, string $jsRoot, object $spy)
                {
                    parent::__construct($files, $basePath, $jsRoot);
                    $this->spy = $spy;
                }

                protected function runNpmInstall(): void
                {
                    $this->spy->called = true;
                }
            };
        });

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();
        $this->assertTrue($spy->called);
    }

    public function test_dev_mode_does_not_copy_source_files(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->assertFileDoesNotExist($this->tmpDir.'/resources/js/pages/Index.vue');
    }

    public function test_dev_mode_copies_config_files_to_root(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->assertFileExists($this->tmpDir.'/vite.config.js');
        $this->assertFileExists($this->tmpDir.'/tsconfig.json');
        $this->assertFileExists($this->tmpDir.'/package.json');
        $this->assertFileExists($this->tmpDir.'/eslint.config.js');
        $this->assertFileExists($this->tmpDir.'/components.json');
    }

    public function test_dev_mode_copies_package_lock_to_root(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->assertFileExists($this->tmpDir.'/package-lock.json');
    }

    public function test_dev_mode_copies_view_files_from_stubs(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeViewStub('vue', 'app.ts');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $blade = file_get_contents($this->tmpDir.'/resources/views/app.blade.php');
        $this->assertStringContainsString('app.ts', $blade);
    }

    public function test_dev_mode_does_not_remove_source_dirs(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');
        $this->seedFakeSourceDir('react');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->assertDirectoryExists($this->tmpDir.'/resources/js/vue');
        $this->assertDirectoryExists($this->tmpDir.'/resources/js/react');
    }

    public function test_dev_mode_does_not_rewrite_paths_in_config_files(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $viteConfig = file_get_contents($this->tmpDir.'/vite.config.js');
        $this->assertStringContainsString('resources/js/vue/', $viteConfig);
    }

    public function test_dev_mode_writes_module_entry_point(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeModule('testmodule', 'vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $entryPoint = file_get_contents($this->tmpDir.'/modules/testmodule/resources/js/app.ts');
        $this->assertStringContainsString("from './vue/app'", $entryPoint);
    }

    public function test_dev_mode_skips_module_without_framework_dir(): void
    {
        $this->seedFakeStubs('vue');
        // module exists but only has react/, not vue/
        $this->seedFakeModule('reactonly', 'react');

        $this->artisan('saucebase:stack vue --dev')
            ->assertSuccessful();

        // app.ts should NOT be written for a module missing the target framework dir
        $this->assertFileDoesNotExist($this->tmpDir.'/modules/reactonly/resources/js/app.ts');
    }

    // -------------------------------------------------------------------------
    // Dev mode guard
    // -------------------------------------------------------------------------

    public function test_dev_mode_guards_against_running_twice(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->artisan('saucebase:stack react --dev')
            ->assertFailed()
            ->expectsOutputToContain('already set to "vue"');
    }

    public function test_dev_mode_same_framework_twice_also_fails(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->artisan('saucebase:stack vue --dev')
            ->assertFailed()
            ->expectsOutputToContain('already set to "vue"');
    }

    // -------------------------------------------------------------------------
    // --no-skip-worktree
    // -------------------------------------------------------------------------

    public function test_no_skip_worktree_option_is_accepted(): void
    {
        $this->seedFakeStubs('vue');

        // should succeed without errors even with the flag
        $this->artisan('saucebase:stack vue --dev --no-skip-worktree')
            ->assertSuccessful();

        $data = json_decode(file_get_contents($this->tmpDir.'/frontend.json'), true);
        $this->assertSame('vue', $data['framework']);
    }

    // -------------------------------------------------------------------------
    // Warnings
    // -------------------------------------------------------------------------

    public function test_dev_mode_warns_for_untracked_entry_points(): void
    {
        $this->seedFakeStubs('react');

        // app.tsx and ssr.tsx are created by the command but not in git — warn for each
        $this->artisan('saucebase:stack react --dev')
            ->assertSuccessful()
            ->expectsOutputToContain('Could not skip-worktree resources/js/app.tsx')
            ->expectsOutputToContain('Could not skip-worktree resources/js/ssr.tsx');
    }

    public function test_dev_mode_warns_for_untracked_module_entry_points(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeModule('testmodule', 'vue');
        // module entry point not committed — should warn

        $this->artisan('saucebase:stack vue --dev')
            ->assertSuccessful()
            ->expectsOutputToContain('Could not skip-worktree modules/testmodule/resources/js/app.ts');
    }

    public function test_dev_mode_no_warnings_for_tracked_config_files(): void
    {
        $this->seedFakeStubs('vue');

        // config files are committed in setUp — should not warn
        $this->artisan('saucebase:stack vue --dev')
            ->assertSuccessful()
            ->doesntExpectOutputToContain('Could not skip-worktree package.json')
            ->doesntExpectOutputToContain('Could not skip-worktree vite.config.js')
            ->doesntExpectOutputToContain('Could not skip-worktree frontend.json');
    }

    public function test_reset_warns_when_file_cannot_be_restored(): void
    {
        $this->seedFakeStubs('vue');
        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        // Manually delete the entry point before reset so git checkout fails AND file is gone
        unlink($this->tmpDir.'/resources/js/app.ts');

        $this->artisan('saucebase:stack --reset')
            ->assertSuccessful()
            ->expectsOutputToContain('Could not restore resources/js/app.ts');
    }

    public function test_no_skip_worktree_suppresses_all_warnings(): void
    {
        $this->seedFakeStubs('react');

        $this->artisan('saucebase:stack react --dev --no-skip-worktree')
            ->assertSuccessful()
            ->doesntExpectOutputToContain('Could not skip-worktree');
    }

    // -------------------------------------------------------------------------
    // Install mode
    // -------------------------------------------------------------------------

    public function test_install_mode_guards_when_framework_already_set(): void
    {
        file_put_contents($this->tmpDir.'/frontend.json', json_encode(['framework' => 'vue']));

        $this->artisan('saucebase:stack react')
            ->assertFailed()
            ->expectsOutputToContain('already set to "vue"');
    }

    public function test_install_mode_fails_when_source_dir_missing(): void
    {
        $this->artisan('saucebase:stack vue')
            ->assertFailed()
            ->expectsOutputToContain('Source directory not found');
    }

    public function test_install_mode_copies_source_files_flat(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertFileExists($this->tmpDir.'/resources/js/pages/Index.vue');
    }

    public function test_install_mode_rewrites_paths_in_config_files(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $viteConfig = file_get_contents($this->tmpDir.'/vite.config.js');
        $this->assertStringNotContainsString('resources/js/vue/', $viteConfig);
        $this->assertStringNotContainsString("resources/js/vue'", $viteConfig);
        $this->assertStringContainsString('resources/js/', $viteConfig);
    }

    public function test_install_mode_removes_both_framework_subdirs(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');
        $this->seedFakeSourceDir('react');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertDirectoryDoesNotExist($this->tmpDir.'/resources/js/vue');
        $this->assertDirectoryDoesNotExist($this->tmpDir.'/resources/js/react');
    }

    public function test_install_mode_removes_stack_stubs_directory(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertDirectoryDoesNotExist($this->tmpDir.'/stubs/saucebase/stack');
    }

    public function test_install_mode_rewrites_cross_module_framework_imports(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $consumerDir = $this->tmpDir.'/modules/consumer/resources/js/vue/pages';
        $this->files->ensureDirectoryExists($consumerDir);
        file_put_contents(
            $consumerDir.'/Index.vue',
            "import Foo from '@modules/other/resources/js/vue/components/Foo.vue';\n"
        );

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $content = file_get_contents($this->tmpDir.'/modules/consumer/resources/js/pages/Index.vue');
        $this->assertStringNotContainsString('/vue/', $content);
        $this->assertStringContainsString('@modules/other/resources/js/components/Foo.vue', $content);
    }

    public function test_install_mode_removes_framework_subdirs_from_modules(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');
        $this->seedFakeModule('testmodule', 'vue');

        $reactDir = $this->tmpDir.'/modules/testmodule/resources/js/react';
        $this->files->ensureDirectoryExists($reactDir);
        file_put_contents($reactDir.'/app.tsx', "export default {};\n");

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertDirectoryDoesNotExist($this->tmpDir.'/modules/testmodule/resources/js/vue');
        $this->assertDirectoryDoesNotExist($this->tmpDir.'/modules/testmodule/resources/js/react');
    }

    public function test_install_mode_copies_package_lock_to_root(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertFileExists($this->tmpDir.'/package-lock.json');
    }

    public function test_install_mode_writes_framework_to_frontend_json(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $data = json_decode(file_get_contents($this->tmpDir.'/frontend.json'), true);
        $this->assertSame('vue', $data['framework']);
        $this->assertArrayNotHasKey('dev', $data);
    }

    public function test_install_mode_react_removes_stale_recipe_proxy(): void
    {
        $this->seedFakeStubs('react');
        $this->seedFakeSourceDir('react');
        $this->seedFakeRecipeStubs();

        $this->artisan('saucebase:stack react')->assertSuccessful();

        $this->assertFileDoesNotExist($this->tmpDir.'/stubs/saucebase/recipes/basic/resources/js/app.ts');
    }

    public function test_install_mode_vue_keeps_recipe_proxy(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');
        $this->seedFakeRecipeStubs();

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertFileExists($this->tmpDir.'/stubs/saucebase/recipes/basic/resources/js/app.ts');
        // Confirm flattenRecipeStubs() actually ran (it deletes subdirs).
        $this->assertDirectoryDoesNotExist($this->tmpDir.'/stubs/saucebase/recipes/basic/resources/js/vue');
    }

    // -------------------------------------------------------------------------
    // Reset
    // -------------------------------------------------------------------------

    public function test_reset_does_nothing_when_no_framework_selected(): void
    {
        $this->files->delete($this->tmpDir.'/frontend.json');

        $this->artisan('saucebase:stack --reset')
            ->assertSuccessful()
            ->expectsOutputToContain('nothing to reset');
    }

    public function test_reset_restores_config_files_via_git_checkout(): void
    {
        $this->seedFakeStubs('vue');
        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        // config files were overwritten with stub content
        $this->assertStringContainsString('resources/js/vue/', file_get_contents($this->tmpDir.'/vite.config.js'));

        $this->artisan('saucebase:stack --reset')->assertSuccessful();

        // git checkout restored originals
        $this->assertEquals('// vite', file_get_contents($this->tmpDir.'/vite.config.js'));
        $data = json_decode(file_get_contents($this->tmpDir.'/frontend.json'), true);
        $this->assertNull($data['framework']);
    }

    public function test_reset_deletes_generated_entry_points(): void
    {
        $this->seedFakeStubs('vue');
        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();

        $this->assertFileExists($this->tmpDir.'/resources/js/app.ts');
        $this->assertFileExists($this->tmpDir.'/resources/js/ssr.ts');

        $this->artisan('saucebase:stack --reset')->assertSuccessful();

        $this->assertFileDoesNotExist($this->tmpDir.'/resources/js/app.ts');
        $this->assertFileDoesNotExist($this->tmpDir.'/resources/js/ssr.ts');
    }

    public function test_reset_preserves_module_entry_points(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeModule('testmodule', 'vue');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();
        $this->assertFileExists($this->tmpDir.'/modules/testmodule/resources/js/app.ts');

        $this->artisan('saucebase:stack --reset')->assertSuccessful();

        // Module app.ts is tracked in the module's own repo — reset must not delete it.
        $this->assertFileExists($this->tmpDir.'/modules/testmodule/resources/js/app.ts');
    }

    public function test_reset_deletes_package_lock(): void
    {
        $this->seedFakeStubs('vue');
        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();
        $this->assertFileExists($this->tmpDir.'/package-lock.json');

        $this->artisan('saucebase:stack --reset')->assertSuccessful();

        $this->assertFileDoesNotExist($this->tmpDir.'/package-lock.json');
    }

    public function test_reset_allows_selecting_framework_again(): void
    {
        $this->seedFakeStubs('vue');
        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();
        $this->artisan('saucebase:stack --reset')->assertSuccessful();

        $this->seedFakeStubs('react');
        $this->artisan('saucebase:stack react --dev')->assertSuccessful();

        $data = json_decode(file_get_contents($this->tmpDir.'/frontend.json'), true);
        $this->assertSame('react', $data['framework']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function seedFakeViewStub(string $framework, string $entry): void
    {
        $viewDir = $this->tmpDir."/stubs/saucebase/stack/{$framework}/views";
        $this->files->ensureDirectoryExists($viewDir);
        file_put_contents($viewDir.'/app.blade.php', "@vite(['{$entry}'])");
    }

    private function seedFakeStubs(string $framework): void
    {
        $stubDir = $this->tmpDir."/stubs/saucebase/stack/{$framework}";
        $this->files->ensureDirectoryExists($stubDir);

        file_put_contents($stubDir.'/vite.config.js', "input: ['resources/js/{$framework}/app.ts'], alias: { '@': 'resources/js/{$framework}' }");
        file_put_contents($stubDir.'/tsconfig.json', "{\"paths\": {\"@/*\": [\"resources/js/{$framework}/*\"]}}");
        file_put_contents($stubDir.'/package.json', '{}');
        file_put_contents($stubDir.'/package-lock.json', '{}');
        file_put_contents($stubDir.'/eslint.config.js', '// eslint');
        file_put_contents($stubDir.'/components.json', '{}');
        $this->seedFakeViewStub($framework, $framework === 'react' ? 'app.tsx' : 'app.ts');
    }

    private function seedFakeSourceDir(string $framework): void
    {
        $jsRoot = $this->tmpDir."/resources/js/{$framework}";
        $this->files->ensureDirectoryExists($jsRoot.'/pages');

        $ext = $framework === 'react' ? 'tsx' : 'vue';
        file_put_contents($jsRoot."/pages/Index.{$ext}", '<!-- fake -->');
    }

    private function seedFakeModule(string $name, string $framework): void
    {
        $fwDir = $this->tmpDir."/modules/{$name}/resources/js/{$framework}";
        $this->files->ensureDirectoryExists($fwDir);
        file_put_contents($fwDir.'/app.'.($framework === 'react' ? 'tsx' : 'ts'), "export default {};\n");
    }

    private function seedFakeRecipeStubs(): void
    {
        $jsRoot = $this->tmpDir.'/stubs/saucebase/recipes/basic/resources/js';
        $this->files->ensureDirectoryExists($jsRoot.'/vue');
        $this->files->ensureDirectoryExists($jsRoot.'/react');
        file_put_contents($jsRoot.'/app.ts', "export * from './vue/app';\n");
        file_put_contents($jsRoot.'/vue/app.ts', "export function setup() {}\n");
        file_put_contents($jsRoot.'/react/app.tsx', "export function setup() {}\n");
    }
}
