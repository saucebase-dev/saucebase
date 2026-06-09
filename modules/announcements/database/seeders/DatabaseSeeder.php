<?php

namespace Modules\Announcements\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(AnnouncementsDatabaseSeeder::class);
    }
}
