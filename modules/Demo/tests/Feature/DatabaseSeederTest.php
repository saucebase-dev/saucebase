<?php

namespace Modules\Demo\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Demo\Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_module_database_seeder_runs(): void
    {
        $this->expectNotToPerformAssertions();

        $this->seed(DatabaseSeeder::class);
    }
}
