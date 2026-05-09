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

        // Write a null frontend.json in the temp dir
        file_put_contents($this->tmpDir.'/frontend.json', json_encode(['framework' => null]));

        // Bind the command to use the temp dir so no real project files are touched
        $tmpDir = $this->tmpDir;
        app()->bind(StackCommand::class, fn () => new StackCommand(
            new Filesystem,
            $tmpDir,
            $tmpDir.'/resources/js',
        ));
    }

    protected function tearDown(): void
    {
        $this->files->deleteDirectory($this->tmpDir);
        parent::tearDown();
    }

    // --- Validation ---

    public function test_rejects_invalid_framework(): void
    {
        $this->artisan('saucebase:stack svelte')
            ->assertFailed()
            ->expectsOutputToContain('Invalid framework');
    }

    // --- Dev mode ---

    public function test_dev_mode_writes_frontend_json(): void
    {
        $this->seedFakeStubs('vue');

        $this->artisan('saucebase:stack vue --dev')
            ->assertSuccessful();

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

    public function test_dev_mode_does_not_copy_source_files(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue --dev')
            ->assertSuccessful();

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

    public function test_dev_mode_allows_switching_frameworks(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeStubs('react');

        $this->artisan('saucebase:stack vue --dev')->assertSuccessful();
        $this->artisan('saucebase:stack react --dev')->assertSuccessful();

        $data = json_decode(file_get_contents($this->tmpDir.'/frontend.json'), true);
        $this->assertSame('react', $data['framework']);
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

    // --- Install mode ---

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

    public function test_install_mode_removes_stubs_directory(): void
    {
        $this->seedFakeStubs('vue');
        $this->seedFakeSourceDir('vue');

        $this->artisan('saucebase:stack vue')->assertSuccessful();

        $this->assertDirectoryDoesNotExist($this->tmpDir.'/stubs');
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

    // --- Helpers ---

    private function seedFakeStubs(string $framework): void
    {
        $stubDir = $this->tmpDir."/stubs/saucebase/stack/{$framework}";
        $this->files->ensureDirectoryExists($stubDir);

        file_put_contents($stubDir.'/vite.config.js', "input: ['resources/js/{$framework}/app.ts']");
        file_put_contents($stubDir.'/tsconfig.json', "{\"paths\": {\"@/*\": [\"resources/js/{$framework}/*\"]}}");
        file_put_contents($stubDir.'/package.json', '{}');
        file_put_contents($stubDir.'/eslint.config.js', '// eslint');
        file_put_contents($stubDir.'/components.json', '{}');
    }

    private function seedFakeSourceDir(string $framework): void
    {
        $jsRoot = $this->tmpDir."/resources/js/{$framework}";
        $this->files->ensureDirectoryExists($jsRoot.'/pages');

        $ext = $framework === 'react' ? 'tsx' : 'vue';
        file_put_contents($jsRoot."/pages/Index.{$ext}", '<!-- fake -->');
    }
}
