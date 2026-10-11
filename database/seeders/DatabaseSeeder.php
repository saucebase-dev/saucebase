<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Saucebase\Core\Database\Seeders\RolesDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesDatabaseSeeder::class,
        ]);
    }
}
