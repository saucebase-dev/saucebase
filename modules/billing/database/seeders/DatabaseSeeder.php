<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BillingDatabaseSeeder::class);
    }
}
