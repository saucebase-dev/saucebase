<?php

namespace Modules\Announcements\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Announcements\Models\Announcement;

class AnnouncementsDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Announcement::exists()) {
            return;
        }

        Announcement::create([
            'text' => '🎉 <strong>Saucebase 2.0 is here!</strong> Much faster, better DX, and now ships with Vue 3 and React 19. <a href="/blog/vue-or-react-what-about-both">Learn more →</a>',
            'is_active' => true,
            'is_dismissable' => true,
            'show_on_frontend' => true,
            'show_on_dashboard' => true,
        ]);
    }
}
