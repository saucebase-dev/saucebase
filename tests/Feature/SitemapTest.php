<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_the_public_pages(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.route('index').'</loc>', false)
            ->assertSee('<loc>'.route('privacy').'</loc>', false)
            ->assertSee('<loc>'.route('terms').'</loc>', false);
    }

    public function test_robots_points_crawlers_at_the_sitemap(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'));
    }
}
