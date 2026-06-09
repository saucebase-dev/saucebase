<?php

namespace Modules\Demo\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoDatabaseSeeder::class);
    }
}
