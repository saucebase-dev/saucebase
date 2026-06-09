<?php

namespace Modules\Announcements\Filament;

use App\Filament\ModulePlugin;
use Filament\Contracts\Plugin;
use Filament\Panel;

class AnnouncementsPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Announcements';
    }

    public function getId(): string
    {
        return 'announcements';
    }

    public function boot(Panel $panel): void {}
}
