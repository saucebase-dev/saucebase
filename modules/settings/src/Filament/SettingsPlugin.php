<?php

namespace Modules\Settings\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;

class SettingsPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Settings';
    }

    public function getId(): string
    {
        return 'settings';
    }

    public static function getNavigationGroupSort(): int
    {
        return 3;
    }

    public function boot(Panel $panel): void
    {
        $panel->navigationGroups([
            NavigationGroup::make()
                ->label(__('settings::filament.navigation.group'))
                ->icon(Heroicon::OutlinedCog6Tooth)
                ->collapsible(),
        ]);
    }
}
