<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class RecipeToModuleCommand extends Command
{
    protected $signature = 'saucebase:recipe {module?} {template?} {--vendor= : Composer vendor prefix (e.g. saucebase)} {--type=library : Composer package type}';

    protected $description = 'Create starter module from a recipe';

    protected string $moduleName = '';

    protected string $moduleFolder = '';

    protected string $composerVendor = '';

    protected string $composerType = '';

    protected string $template = '';

    protected string $templatePath = '';

    protected string $tempFolder = '';

    protected string $moduleConfigPath = '';

    private SymfonyFilesystem $fs;

    public function __construct()
    {
        parent::__construct();
        $this->fs = new SymfonyFilesystem;
    }

    public function handle(): bool
    {
        $this->moduleName = $this->getModuleName();
        $this->composerVendor = $this->option('vendor')
            ?: config('app-modules.modules_vendor')
            ?: Str::kebab(config('app-modules.modules_namespace', 'Modules'));
        $this->composerType = $this->option('type') ?: 'library';
        $this->moduleConfigPath = base_path(config('app-modules.modules_directory', 'modules')).'/';

        if (file_exists($this->moduleConfigPath.$this->moduleFolder)) {
            error("{$this->moduleFolder} module already exists.");

            return true;
        }

        $this->template = $this->getTemplate();
        $this->templatePath = base_path($this->template);
        $this->tempFolder = base_path('saucebase-temp');

        if (! file_exists($this->templatePath)) {
            error("{$this->templatePath} path does not exist. Check your config/saucebase.php.");

            return true;
        }

        $this->generate();
        $this->registerInTaskfile();

        info("Starter {$this->moduleName} module generated successfully.");
        info('Next steps:');
        info("  composer require {$this->composerVendor}/{$this->moduleFolder}");
        info('  php artisan modules:sync');
        info('  npm run build');

        return true;
    }

    protected function getModuleName(): string
    {
        $input = $this->argument('module') ?? '';

        if ($input !== '') {
            if (Str::contains($input, ' ')) {
                throw new RuntimeException('The module name must not contain spaces.');
            }

            $this->setModuleNames($input);

            return $this->moduleName;
        }

        $this->setModuleNames(
            text(
                label: 'Please enter a name for the module to be created (e.g. invoice-test)',
                required: true,
                validate: fn (string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    default => null,
                }
            )
        );

        return $this->moduleName;
    }

    protected function setModuleNames(string $input): void
    {
        $this->moduleName = Str::studly($input);
        $this->moduleFolder = Str::kebab($this->moduleName);
    }

    protected function getTemplate(): string
    {
        $templates = config('saucebase.template');
        $input = $this->argument('template') ?? '';

        if ($input !== '') {
            if (array_key_exists($input, $templates)) {
                return $templates[$input];
            }

            error("Invalid template: {$input}");
        }

        $selected = select('Which recipe would you like to use?', array_keys($templates));

        return $templates[$selected];
    }

    protected function generate(): void
    {
        $this->deleteDir($this->tempFolder);
        $this->copyDir($this->templatePath, $this->tempFolder.'/Module');

        $finder = (new Finder)->files()->in($this->tempFolder);

        $this->renameFiles($finder);
        $this->updateFilesContent($finder);

        $this->copyDir($this->tempFolder.'/'.$this->moduleName, $this->moduleConfigPath.$this->moduleFolder);
        $this->deleteDir($this->tempFolder);
    }

    protected function renameFiles(Finder $finder): void
    {
        foreach ($finder as $file) {
            $type = Str::endsWith($file->getPath(), ['migrations', 'Migrations']) ? 'migration' : '';
            $this->alterFilename($file->getPathname(), $type);
        }
    }

    protected function alterFilename(string $sourceFile, string $type = ''): void
    {
        $name = $this->moduleName;
        $model = Str::singular($name);

        $projectPath = base_path();
        $relativePath = substr($sourceFile, strlen($projectPath));

        $targetFile = $projectPath.str_replace(
            ['Module', 'module', strtolower($name).'_plural', 'Model', 'model'],
            [$name, Str::kebab($name), Str::kebab(Str::plural($name)), $model, Str::kebab($model)],
            $relativePath
        );

        if (in_array(basename($sourceFile), config('saucebase.ignore_files'), true)) {
            $targetFile = dirname($targetFile).'/'.basename($sourceFile);
        }

        $targetFile = str_replace('Entities', 'Models', $targetFile);

        // prevent "Modules" directory from being renamed to "{Name}s"
        if (Str::contains($targetFile, $name.'s')) {
            $targetFile = str_replace($name.'s', 'Modules', $targetFile);
        }

        $targetFile = str_replace($name.'_plural', Str::plural($name), $targetFile);

        $dir = dirname($targetFile);
        if (! is_dir($dir) && ! mkdir($dir, 0777, true) && ! is_dir($dir)) {
            throw new RuntimeException("Directory \"{$dir}\" could not be created.");
        }

        if ($this->fs->exists($sourceFile)) {
            $this->fs->rename($sourceFile, $type === 'migration' ? $this->prependTimestamp($targetFile) : $targetFile, true);
        }
    }

    protected function prependTimestamp(string $path): string
    {
        return str_replace(basename($path), date('Y_m_d_his_').basename($path), $path);
    }

    protected function updateFilesContent(Finder $finder): void
    {
        foreach ($finder as $file) {
            $this->replaceInFile($file->getPathname());
        }
    }

    protected function replaceInFile(string $sourceFile): void
    {
        if (! file_exists($sourceFile)) {
            return;
        }

        $map = $this->placeholders($this->moduleName);
        $content = file_get_contents($sourceFile);
        file_put_contents($sourceFile, str_replace(array_keys($map), array_values($map), $content));
    }

    protected function placeholders(string $name): array
    {
        $model = Str::singular($name);
        $modelPlural = Str::plural($model);
        $namespace = config('app-modules.modules_namespace', 'Modules');

        return [
            // Module — base
            '{Module}' => $name,
            '{module}' => strtolower($name),

            // Module — case variants
            '{Module_}' => $this->splitStudlyWords($name, '_'),
            '{module_}' => Str::snake($name),
            '{Module-}' => $this->splitStudlyWords($name, '-'),
            '{module-}' => Str::kebab($name),

            // Module — camel / studly
            '{moduleCamel}' => lcfirst($name),
            '{ModuleStudly}' => $name,
            '{moduleStudly}' => $name,

            // Module — human readable
            '{Module }' => $this->spaceify($name),
            '{module }' => strtolower($this->spaceify($name)),
            '{ModuleTitle}' => $this->titleCase($name),
            '{module_title}' => $this->titleCase($name),

            // Module — plural
            '{module_plural}' => strtolower($this->spaceify(Str::plural($name))),
            '{module_plural_snake}' => Str::snake(Str::plural($name)),
            '{module_plural_kebab}' => Str::kebab(Str::plural($name)),

            // Model — base
            '{Model}' => $model,
            '{model}' => strtolower($model),

            // Model — case variants
            '{Model_}' => $this->splitStudlyWords($model, '_'),
            '{model_}' => Str::snake($model),
            '{Model-}' => $this->splitStudlyWords($model, '-'),
            '{model-}' => Str::kebab($model),

            // Model — camel / studly
            '{modelCamel}' => lcfirst($model),
            '{ModelStudly}' => $model,
            '{modelStudly}' => $model,

            // Model — human readable
            '{Model }' => $this->spaceify($model),
            '{model }' => strtolower($this->spaceify($model)),
            '{ModelTitle}' => $this->titleCase($model),
            '{model_title}' => $this->titleCase($model),

            // Model — plural
            '{model_plural}' => strtolower($this->spaceify($modelPlural)),
            '{model_plural_snake}' => Str::snake($modelPlural),
            '{model_plural_kebab}' => Str::kebab($modelPlural),

            // JS-friendly underscored variants (IDE-safe in stub files)
            '___Module___' => $name,
            '___module___' => strtolower($name),
            '___MODULE_NAMESPACE___' => $namespace,

            // Namespace
            '{MODULE_NAMESPACE}' => $namespace,

            // Composer vendor / type
            '{COMPOSER_VENDOR}' => $this->composerVendor,
            '{COMPOSER_TYPE}' => $this->composerType,
        ];
    }

    private function splitStudlyWords(string $value, string $separator, bool $lowercase = false): string
    {
        $parts = preg_split('/(?=[A-Z])/', $value, -1, PREG_SPLIT_NO_EMPTY);

        return implode($separator, $lowercase ? array_map('strtolower', $parts) : $parts);
    }

    private function spaceify(string $value): string
    {
        return trim((string) preg_replace('/(?<! )[A-Z]/', ' $0', $value));
    }

    private function titleCase(string $value): string
    {
        return ucwords(str_replace('_', ' ', Str::snake($value)));
    }

    protected function registerInTaskfile(): void
    {
        $taskfile = base_path('Taskfile.yml');

        if (! file_exists($taskfile)) {
            return;
        }

        $content = file_get_contents($taskfile);

        if ($content === false) {
            throw new RuntimeException("Unable to read Taskfile at [{$taskfile}].");
        }

        $entry = "    {$this->moduleFolder}:\n        taskfile: ./modules/{$this->moduleFolder}/Taskfile.yml\n        optional: true\n";
        $marker = '    # ── END MODULES';

        if (str_contains($content, "taskfile: ./modules/{$this->moduleFolder}/Taskfile.yml")) {
            return;
        }

        $markerPosition = strpos($content, $marker);

        if ($markerPosition === false) {
            return;
        }

        file_put_contents($taskfile, substr($content, 0, $markerPosition).$entry.substr($content, $markerPosition));
    }

    private function deleteDir(string $path): void
    {
        if ($this->fs->exists($path)) {
            $this->fs->remove($path);
        }
    }

    private function copyDir(string $source, string $target): void
    {
        if ($this->fs->exists($source)) {
            $this->fs->mirror($source, $target);
        }
    }
}
