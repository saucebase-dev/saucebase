<?php

use Illuminate\Support\Facades\Auth;
use Saucebase\Core\Facades\Navigation;
use Saucebase\Core\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Core Navigation
|--------------------------------------------------------------------------
|
| Define core application navigation items here.
| These items will be loaded automatically by the Navigation service.
|
*/

Navigation::add('Welcome', fn () => route('dashboard'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'dashboard',
        'icon' => 'dashboard',
        'order' => 0,
    ]);
});

Navigation::add(
    'Star us on Github',
    'https://github.com/saucebase-dev/saucebase',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'github',
            'icon' => 'github',
            'external' => true,
            'newPage' => true,
            'order' => 0,
        ]);
    }
);

Navigation::add(
    'Documentation',
    'https://saucebase-dev.github.io/docs/',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'documentation',
            'icon' => 'documentation',
            'external' => true,
            'newPage' => true,
            'order' => 0,
        ]);
    }
);

Navigation::add(
    'Discord',
    'https://discord.gg/hCajBky39t',
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'discord',
            'icon' => 'discord',
            'external' => true,
            'newPage' => true,
            'order' => 5,
            'class' => 'bg-[#5865F2]/10 text-[#4752C4] hover:bg-[#5865F2]/20 hover:text-[#3C45A5] dark:text-[#949CF7] dark:hover:text-[#B5BAFA]',
        ]);
    }
);

Navigation::add(
    'Discord',
    'https://discord.gg/hCajBky39t',
    function (Section $section) {
        $section->attributes([
            'group' => 'landing',
            'slug' => 'discord',
            'external' => true,
            'newPage' => true,
            'order' => 99,
            'class' => 'text-[#4752C4] hover:text-[#3C45A5] dark:text-[#949CF7] dark:hover:text-[#B5BAFA]',
        ]);
    }
);

Navigation::addWhen(
    fn () => Auth::user()?->can('access admin panel') ?? false,
    'Admin',
    fn () => route('filament.admin.pages.dashboard'),
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'admin',
            'icon' => 'admin',
            'order' => 10,
            'external' => true,
            'newPage' => true,
            'class' => 'bg-yellow-500/10 text-yellow-600 hover:bg-yellow-500/20 hover:text-yellow-700 dark:hover:text-yellow-400',
        ]);
    }
);
