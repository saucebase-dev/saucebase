<?php

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use InterNACHI\Modular\Support\ModuleConfig;
use InterNACHI\Modular\Support\ModuleRegistry;
use Laravel\Boost\BoostServiceProvider;
use Laravel\Roster\ProjectManager;
use Tests\TestCase;

/**
 * A module's `resources/boost` reaches the files Laravel Boost writes for agents.
 *
 * Runs `modules:boost` and Boost's own `boost:update` against a throwaway application —
 * its own composer.json, composer.lock, modules and boost.json — so the real CLAUDE.md,
 * AGENTS.md and vendor directory are never touched.
 */
class ModuleBoostIntegrationTest extends TestCase
{
    private string $root;

    private string $originalBasePath;

    private string $originalWorkingDirectory;

    private string $realGuidelines;

    /** @var list<string> */
    private array $installed = ['fixture'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir().'/module-boost-'.bin2hex(random_bytes(6));
        $module = $this->root.'/modules/fixture';

        mkdir($module.'/resources/boost/guidelines', 0777, true);
        mkdir($module.'/resources/boost/skills/fixture-development', 0777, true);
        mkdir($this->root.'/.ai/skills', 0777, true);

        file_put_contents($module.'/composer.json', json_encode(['name' => 'acme/fixture']));
        file_put_contents($module.'/resources/boost/guidelines/core.md', "## Fixture module\n\nFIXTURE-GUIDELINE-MARKER\n");
        file_put_contents(
            $module.'/resources/boost/skills/fixture-development/SKILL.md',
            "---\nname: fixture-development\ndescription: Work on the fixture module.\n---\n\n# Fixture\n",
        );

        file_put_contents($this->root.'/composer.json', json_encode(['require' => ['acme/fixture' => '*']]));
        file_put_contents($this->root.'/composer.lock', json_encode([
            'packages' => [['name' => 'acme/fixture', 'version' => 'v1.0.0']],
            'packages-dev' => [],
        ]));
        file_put_contents($this->root.'/boost.json', json_encode([
            'agents' => ['claude_code'],
            'guidelines' => true,
            'mcp' => false,
        ]));

        // Boost switches itself off under unit tests; this is its own provider with that
        // one check lifted, so `boost:update` runs exactly as it does for a developer.
        $this->app->register(new class($this->app) extends BoostServiceProvider
        {
            protected function shouldRun(): bool
            {
                return true;
            }
        }, force: true);

        // Artisan discovers the real modules' commands through the registry when it
        // first boots, so it has to boot before the registry is swapped for the fixture.
        Artisan::all();

        $this->originalBasePath = $this->app->basePath();
        $this->realGuidelines = (string) @md5_file($this->originalBasePath.'/CLAUDE.md');
        $this->app->setBasePath($this->root);

        // Boost writes CLAUDE.md relative to the working directory, not the base path.
        $this->originalWorkingDirectory = (string) getcwd();
        chdir($this->root);

        $this->app->instance(ModuleRegistry::class, new ModuleRegistry(
            $this->root.'/modules',
            fn (): Collection => collect($this->installed)->mapWithKeys(fn (string $name): array => [
                $name => new ModuleConfig($name, $this->root.'/modules/'.$name),
            ]),
        ));
    }

    protected function tearDown(): void
    {
        chdir($this->originalWorkingDirectory);
        $this->app->setBasePath($this->originalBasePath);
        (new Filesystem)->deleteDirectory($this->root);

        parent::tearDown();

        $this->assertSame($this->realGuidelines, (string) @md5_file($this->originalBasePath.'/CLAUDE.md'), 'The real CLAUDE.md was modified.');
    }

    public function test_a_module_guideline_and_skill_are_written_for_agents(): void
    {
        $this->syncBoost();

        $this->assertStringContainsString('FIXTURE-GUIDELINE-MARKER', (string) file_get_contents($this->root.'/CLAUDE.md'));
        $this->assertFileExists($this->root.'/.claude/skills/fixture-development/SKILL.md');
    }

    public function test_a_removed_module_disappears_from_the_written_files(): void
    {
        $this->syncBoost();

        $this->installed = [];
        $this->app->make(ModuleRegistry::class)->reload();
        $this->syncBoost();

        $this->assertStringNotContainsString('FIXTURE-GUIDELINE-MARKER', (string) file_get_contents($this->root.'/CLAUDE.md'));
        $this->assertDirectoryDoesNotExist($this->root.'/.claude/skills/fixture-development');
    }

    /**
     * The same two steps as the `boost:update` Composer script.
     */
    private function syncBoost(): void
    {
        $this->artisan('modules:boost')->assertSuccessful();

        // Roster memoises its scan; a module coming or going has to be seen afresh.
        $this->app->forgetInstance(ProjectManager::class);

        $this->artisan('boost:update', ['--no-discover' => true])->assertSuccessful();
    }
}
