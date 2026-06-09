<?php

namespace Modules\Roadmap\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoadmapDatabaseSeeder::class);
    }
}
