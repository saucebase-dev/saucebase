<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\TestFixtures;
use Tests\TestCase;

class E2eFixturesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Playwright asks for credentials over HTTP, so every call is a new PHP process and
     * Faker's `unique()` remembers nothing between them. Reseeding Faker stands in for
     * two processes that happen to draw the same name.
     */
    public function test_credentials_never_collide_across_requests(): void
    {
        fake()->seed(1);
        $first = TestFixtures::credentials();

        fake()->seed(1);
        fake()->unique(reset: true);
        $second = TestFixtures::credentials();

        $this->assertNotSame($first['user']['email'], $second['user']['email']);
        $this->assertNotSame($first['admin']['email'], $second['admin']['email']);
    }
}
