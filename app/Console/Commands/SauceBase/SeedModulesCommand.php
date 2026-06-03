<?php

namespace App\Console\Commands\SauceBase;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InterNACHI\Modular\Support\ModuleRegistry;

class SeedModulesCommand extends Command
{
    protected $signature = 'modules:seed {--module= : Seed a specific module (case-insensitive). Omit to seed all.}';

    protected $description = 'Seed all installed modules that have a DatabaseSeeder';

    public function handle(ModuleRegistry $registry): int
    {
        $filter = $this->option('module') !== null
            ? strtolower($this->option('module'))
            : null;

        if ($filter !== null) {
            $module = $registry->modules()->first(fn ($m) => strtolower($m->name) === $filter);

            if ($module === null) {
                $this->components->error("Module \"{$filter}\" not found.");

                return self::FAILURE;
            }

            $modules = collect([$module]);
        } else {
            $modules = $registry->modules();
        }

        foreach ($modules as $module) {
            $seeder = 'Modules\\'.Str::studly($module->name).'\\Database\\Seeders\\DatabaseSeeder';

            if (! class_exists($seeder)) {
                continue;
            }

            $this->components->task($module->name, fn () => $this->call('db:seed', ['--module' => strtolower($module->name)]) === self::SUCCESS);
        }

        return self::SUCCESS;
    }
}
