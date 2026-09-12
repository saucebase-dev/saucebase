<?php

namespace ___MODULE_NAMESPACE___\___Module___\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Saucebase\Core\Filament\ModulePlugin;

class ___Module___Plugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return '{Module}';
    }

    public function getId(): string
    {
        return '{module}';
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
