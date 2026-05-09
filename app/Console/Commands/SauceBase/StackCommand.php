<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class StackCommand extends Command
{
    protected $signature = 'saucebase:stack
                            {stack : The frontend stack to install (vue or react)}
                            {--dev : Contributor dev mode — copy config files only, keep both source dirs}';

    protected $description = 'Select the frontend framework stack (vue or react) for this Saucebase installation';

    private const CONFIG_FILES = ['package.json', 'vite.config.js', 'tsconfig.json', 'eslint.config.js', 'components.json'];

    private const SUPPORTED = ['vue', 'react'];

    private string $basePath;

    private string $jsRoot;

    public function __construct(
        private readonly Filesystem $files,
        ?string $basePath = null,
        ?string $jsRoot = null,
    ) {
        parent::__construct();
        $this->basePath = $basePath ?? base_path();
        $this->jsRoot = $jsRoot ?? resource_path('js');
    }

    public function handle(): int
    {
        $framework = strtolower($this->argument('stack'));

        if (! in_array($framework, self::SUPPORTED)) {
            $this->error("Invalid framework '{$framework}'. Supported: ".implode(', ', self::SUPPORTED).'.');

            return self::FAILURE;
        }

        return $this->option('dev')
            ? $this->runDevMode($framework)
            : $this->runInstallMode($framework);
    }

    private function runInstallMode(string $framework): int
    {
        $current = $this->getSelectedFramework();

        if ($current !== null) {
            $this->error("Framework already set to \"{$current}\". Switching after install is not supported. Start a new project to use a different framework.");

            return self::FAILURE;
        }

        $sourceDir = $this->jsRoot."/{$framework}";

        if (! $this->files->isDirectory($sourceDir)) {
            $this->error("Source directory not found: {$sourceDir}");

            return self::FAILURE;
        }

        $this->info("Setting up {$framework}...");

        $this->copySourceFiles($framework);
        $this->copyConfigFiles($framework, rewrite: true);
        $this->files->deleteDirectory($this->jsRoot.'/vue');
        $this->files->deleteDirectory($this->jsRoot.'/react');
        $this->files->deleteDirectory($this->basePath.'/stubs');
        $this->deployModuleFiles($framework);
        $this->writeFrontendJson($framework);

        $this->info("Framework set to {$framework}. Run: npm install && npm run dev");

        return self::SUCCESS;
    }

    private function runDevMode(string $framework): int
    {
        $this->copyConfigFiles($framework, rewrite: false);
        $this->files->put($this->jsRoot.'/app.ts', "import './{$framework}/app';\n");
        $this->files->put($this->jsRoot.'/ssr.ts', "import './{$framework}/ssr';\n");
        $this->writeFrontendJson($framework, dev: true);

        $this->info("Framework set to {$framework} (dev mode). Run: npm install && npm run dev");

        return self::SUCCESS;
    }

    private function copySourceFiles(string $framework): void
    {
        $sourceDir = $this->jsRoot."/{$framework}";

        foreach ($this->files->allFiles($sourceDir) as $file) {
            $relativePath = $file->getRelativePathname();

            if (in_array($relativePath, self::CONFIG_FILES)) {
                continue;
            }

            $destination = $this->jsRoot.'/'.$relativePath;
            $this->files->ensureDirectoryExists(dirname($destination));
            $this->files->copy($file->getPathname(), $destination);
        }
    }

    private function copyConfigFiles(string $framework, bool $rewrite): void
    {
        $sourceDir = $this->basePath."/stubs/saucebase/stack/{$framework}";

        foreach (self::CONFIG_FILES as $filename) {
            $source = $sourceDir.'/'.$filename;
            $destination = $this->basePath.'/'.$filename;

            if (! $this->files->exists($source)) {
                continue;
            }

            $content = $this->files->get($source);

            if ($rewrite) {
                $content = $this->rewritePaths($content, $framework);
            }

            $this->files->put($destination, $content);
        }
    }

    private function rewritePaths(string $content, string $framework): string
    {
        return str_replace(
            ["resources/js/{$framework}/", "resources/js/{$framework}'"],
            ['resources/js/', "resources/js'"],
            $content
        );
    }

    private function deployModuleFiles(string $framework): void
    {
        $moduleDirs = glob($this->basePath.'/modules/*/', GLOB_ONLYDIR);

        if (! $moduleDirs) {
            return;
        }

        foreach ($moduleDirs as $moduleDir) {
            $jsRoot = $moduleDir.'resources/js';

            if (! $this->files->isDirectory($jsRoot)) {
                continue;
            }

            $fwPath = $jsRoot.'/'.$framework;

            if (! $this->files->isDirectory($fwPath)) {
                $this->warn('Module '.basename($moduleDir)." does not support {$framework} — skipping.");

                continue;
            }

            foreach ($this->files->allFiles($fwPath) as $file) {
                $destination = $jsRoot.'/'.$file->getRelativePathname();
                $this->files->ensureDirectoryExists(dirname($destination));
                $this->files->copy($file->getPathname(), $destination);
            }

            $this->files->deleteDirectory($jsRoot.'/vue');
            $this->files->deleteDirectory($jsRoot.'/react');
        }
    }

    private function getSelectedFramework(): ?string
    {
        $path = $this->basePath.'/frontend.json';

        if (! $this->files->exists($path)) {
            return null;
        }

        $data = json_decode($this->files->get($path), true);

        return $data['framework'] ?? null;
    }

    private function writeFrontendJson(string $framework, bool $dev = false): void
    {
        $data = ['framework' => $framework];

        if ($dev) {
            $data['dev'] = true;
        }

        $this->files->put(
            $this->basePath.'/frontend.json',
            json_encode($data, JSON_PRETTY_PRINT).PHP_EOL
        );
    }
}
