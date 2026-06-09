<?php

namespace Modules\Billing\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_database_seeder_runs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(true);
    }
}
