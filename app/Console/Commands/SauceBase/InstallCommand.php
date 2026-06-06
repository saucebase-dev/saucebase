<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class InstallCommand extends Command
{
    protected $signature = 'saucebase:install
                            {stack? : The frontend stack to install (vue or react)}
                            {--fresh : Run migrate:fresh instead of migrate (destructive)}
                            {--all-modules : Enable and migrate all available modules without prompting}
                            {--modules= : Comma-separated list of modules to enable (e.g. Auth,Settings)}
                            {--dev : Dev environment}
                            {--force : Skip confirmations}';

    protected $description = 'Install and configure Saucebase';

    protected ?string $selectedStack = null;

    /** @var string[] */
    protected array $selectedModules = [];

    /** @var string[] */
    protected array $availableModules = [];

    /** @var array<string, string[]> */
    protected array $moduleFrameworks = [];

    public function handle(): int
    {
        $this->displayWelcome();
        $this->captureStack();

        if ($this->isCI()) {
            return $this->handleCIInstallation();
        }

        $this->promptForModules();

        return $this->install();
    }

    protected function captureStack(): void
    {
        $config = @json_decode((string) @file_get_contents(base_path('frontend.json')), true);

        if (! empty($config['framework'])) {
            $this->selectedStack = $config['framework'];

            return;
        }

        $stack = $this->argument('stack');

        if (! $stack) {
            $stack = $this->isCI()
                ? 'vue'
                : select(
                    label: 'Which frontend stack would you like to use?',
                    options: ['vue' => 'Vue', 'react' => 'React'],
                    default: 'vue',
                );
        }

        $this->selectedStack = $stack;
    }

    protected function runStack(): void
    {
        if ($this->selectedStack) {
            $isDev = $this->option('dev') ? ['--dev' => true] : [];
            $this->call('saucebase:stack', array_merge(['stack' => $this->selectedStack], $isDev));
        }
    }

    protected function promptForModules(): void
    {
        if ($this->option('all-modules') || $this->option('modules') || $this->option('dev') || $this->isCI()) {
            return;
        }

        $available = $this->fetchAvailableModules();

        if (empty($available)) {
            return;
        }

        if ($this->selectedStack) {
            $available = $this->filterModulesByFramework($available, $this->selectedStack);
        }

        if (empty($available)) {
            return;
        }

        $options = collect($available)
            ->mapWithKeys(fn (string $package) => [
                $package => Str::studly(Str::after($package, '/')),
            ])
            ->all();

        $this->selectedModules = multiselect(
            label: 'Which modules would you like to install?',
            options: $options,
            default: [],
        );
    }

    /**
     * @param  string[]  $packages
     * @return string[]
     */
    protected function filterModulesByFramework(array $packages, string $framework): array
    {
        return array_values(array_filter(
            $packages,
            fn (string $pkg) => in_array($framework, $this->fetchPackageFrameworks($pkg), true)
        ));
    }

    /**
     * @return string[]
     */
    protected function fetchPackageFrameworks(string $package): array
    {
        if (isset($this->moduleFrameworks[$package])) {
            return $this->moduleFrameworks[$package];
        }

        $name = Str::after($package, '/');
        $localManifest = base_path("modules/{$name}/composer.json");

        if (file_exists($localManifest)) {
            $local = json_decode((string) file_get_contents($localManifest), true);
            $frameworks = data_get($local, 'extra.saucebase.frameworks');

            if (is_array($frameworks) && ! empty($frameworks)) {
                return $this->moduleFrameworks[$package] = $frameworks;
            }
        }

        $response = Http::timeout(5)->get("https://raw.githubusercontent.com/saucebase-dev/{$name}/main/composer.json");

        if ($response->ok()) {
            $frameworks = data_get($response->json(), 'extra.saucebase.frameworks');

            if (is_array($frameworks) && ! empty($frameworks)) {
                return $this->moduleFrameworks[$package] = $frameworks;
            }
        }

        return $this->moduleFrameworks[$package] = ['vue'];
    }

    protected function install(): int
    {
        if (! $this->ensureEnvFile()) {
            return self::FAILURE;
        }

        $this->generateApplicationKey();
        $this->setupDatabase();
        $this->runStack();
        $this->setupModules();
        $this->createStorageLink();
        $this->clearCaches();
        $this->displaySuccess();

        return self::SUCCESS;
    }

    protected function handleCIInstallation(): int
    {
        $this->info('CI environment detected - running minimal setup...');

        $this->components->task('Verifying .env', fn () => file_exists(base_path('.env')));
        $this->components->task('Verifying app key', fn () => ! empty(config('app.key')));

        $this->info('CI setup complete');

        return self::SUCCESS;
    }

    protected function ensureEnvFile(): bool
    {
        if (file_exists(base_path('.env'))) {
            return true;
        }

        if (file_exists(base_path('.env.example'))) {
            if (! copy(base_path('.env.example'), base_path('.env'))) {
                $this->error('Failed to copy .env.example to .env. Check directory permissions.');

                return false;
            }

            return true;
        }

        $this->error('.env file not found. Copy .env.example to .env and configure it before running the installer.');

        return false;
    }

    protected function generateApplicationKey(): void
    {
        $this->components->task('Generating application key', function () {
            $env = file_get_contents(base_path('.env'));
            if (preg_match('/^APP_KEY=base64:.+$/m', $env)) {
                return true;
            }

            return Artisan::call('key:generate', ['--force' => true]) === 0;
        });
    }

    protected function setupDatabase(): void
    {
        if ($this->option('fresh')) {
            $this->components->task('Running migrate:fresh --seed', function () {
                return Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]) === 0;
            });
        } else {
            $this->components->task('Running migrations', function () {
                return Artisan::call('migrate', ['--seed' => true, '--force' => true]) === 0;
            });
        }
    }

    protected function setupModules(): void
    {
        $available = $this->fetchAvailableModules();

        if (empty($available)) {
            $this->components->warn('Could not fetch module list from Packagist.');

            return;
        }

        $selected = $this->resolveModuleSelection($available);

        if (empty($selected)) {
            return;
        }

        $this->newLine();

        // Phase 1: require all selected packages
        foreach ($selected as $package) {
            $this->components->task("Requiring {$package}", function () use ($package) {
                $process = new Process(['composer', 'require', $package, '--no-interaction']);
                $process->setTimeout(300);
                $process->run();

                return $process->isSuccessful();
            });
        }

        // Phase 2: regenerate autoload once for all new modules
        $this->components->task('Dumping autoload', function () {
            $process = new Process(['composer', 'dump-autoload', '--no-interaction']);
            $process->setTimeout(120);
            $process->run();

            return $process->isSuccessful();
        });

        // Phase 3: sync module configs, then migrate all in one pass
        $this->components->task('Syncing modules', function () {
            $process = new Process([PHP_BINARY, base_path('artisan'), 'modules:sync']);
            $process->setTimeout(30);
            $process->run();

            return $process->isSuccessful();
        });

        $this->components->task('Running module migrations', function () {
            $process = new Process([PHP_BINARY, base_path('artisan'), 'migrate', '--seed', '--force']);
            $process->setTimeout(120);
            $process->run();

            return $process->isSuccessful();
        });
    }

    /**
     * @return string[]
     */
    protected function fetchAvailableModules(): array
    {
        if (! empty($this->availableModules)) {
            return $this->availableModules;
        }

        $response = Http::timeout(10)
            ->get('https://packagist.org/packages/list.json?type=saucebase-module&fields[]=abandoned');

        if (! $response->ok()) {
            return [];
        }

        $packages = $response->json('packages', []);

        return $this->availableModules = array_keys(array_filter(
            $packages,
            fn (array $p) => empty($p['abandoned'])
        ));
    }

    /**
     * @param  string[]  $available
     * @return string[]
     */
    protected function resolveModuleSelection(array $available): array
    {
        // 1. Select all modules
        if ($this->option('all-modules')) {
            return $available;
        }

        // 2. Modules passed via --modules option
        if ($modules = $this->option('modules')) {
            $requested = collect(explode(',', $modules))
                ->map(fn ($m) => strtolower(trim($m)))
                ->filter()
                ->values();

            return collect($available)
                ->filter(function (string $package) use ($requested) {
                    $name = strtolower(Str::after($package, '/'));

                    return $requested->contains($name);
                })
                ->values()
                ->all();
        }

        return $this->selectedModules;
    }

    protected function createStorageLink(): void
    {
        $this->components->task('Creating storage link', function () {
            return Artisan::call('storage:link') === 0;
        });
    }

    protected function clearCaches(): void
    {
        $this->components->task('Clearing caches', function () {
            return Artisan::call('optimize:clear') === 0;
        });
    }

    protected function isCI(): bool
    {
        return ! empty(getenv('CI'))
            || ! empty(getenv('GITHUB_ACTIONS'))
            || ! empty(getenv('GITLAB_CI'))
            || ! empty(getenv('CIRCLECI'))
            || ! empty(getenv('TRAVIS'));
    }

    protected function displayWelcome(): void
    {
        $primary = '#5455c4';
        $secondary = '#26b9d9';
        $split = 48;

        $lines = [
            '                                                888                                 ',
            '                                                888                                 ',
            '                                                888                                 ',
            '    .d8888b   8888b.  888  888  .d8888b .d88b.  88888b.   8888b.  .d8888b   .d88b.  ',
            '    88K          "88b 888  888 d88P"   d8P  Y8b 888 "88b     "88b 88K      d8P  Y8b ',
            '    "Y8888b. .d888888 888  888 888     88888888 888  888 .d888888 "Y8888b. 88888888 ',
            '         X88 888  888 Y88b 888 Y88b.   Y8b.     888 d88P 888  888      X88 Y8b.     ',
            '     88888P\' "Y888888  "Y88888  "Y8888P "Y8888  88888P"  "Y888888  88888P\'  "Y8888  ',
        ];

        $this->newLine();

        foreach ($lines as $line) {
            $sauce = substr($line, 0, $split);
            $base = substr($line, $split);
            $this->line("<fg={$secondary}>{$sauce}</><fg={$primary}>{$base}</>");
        }

        $this->displayTagline();
    }

    protected function displayTagline(): void
    {
        $primary = '#5455c4';
        $logoWidth = 84;
        $slogan = 'With Saucebase • Your foundation is ready!';

        $padding = '<fg=white;bg='.$primary.'>'.str_repeat(' ', $logoWidth).'</>';
        $tagline = '<fg=white;bg='.$primary.';options=bold>'.mb_str_pad($slogan, $logoWidth, ' ', STR_PAD_BOTH).'</>';

        $this->newLine(2);
        $this->line($padding);
        $this->line($tagline);
        $this->line($padding);
        $this->newLine();
        $this->newLine();
    }

    protected function displaySuccess(): void
    {
        $this->newLine();
        $this->info('Installation complete!');
        $this->newLine();
        $this->line('Next steps:');
        $this->line('  1. Ensure <fg=yellow>APP_URL</> is set correctly in <fg=yellow>.env</>');
        $this->line('  2. Run: <fg=yellow>npm install && composer dev</>');
        $this->line('  3. Open your app in the browser');
        $this->newLine();
        $this->line('Learn more: <fg=cyan>https://github.com/saucebase-dev/saucebase</>');
    }
}
