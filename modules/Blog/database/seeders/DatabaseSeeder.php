<?php

namespace Modules\Blog\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BlogDatabaseSeeder::class);
    }
}
