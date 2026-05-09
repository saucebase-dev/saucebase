<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    use RefreshDatabase;

    private string $frontendJson;

    protected function setUp(): void
    {
        parent::setUp();
        $this->frontendJson = base_path('frontend.json');
    }

    protected function tearDown(): void
    {
        // Restore frontend.json to null state after each test
        file_put_contents($this->frontendJson, json_encode(['framework' => null], JSON_PRETTY_PRINT).PHP_EOL);
        parent::tearDown();
    }

    public function test_renders_setup_blade_when_framework_is_null(): void
    {
        file_put_contents($this->frontendJson, json_encode(['framework' => null]));

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('setup');
        $response->assertSeeText('Choose your frontend framework');
    }

    public function test_renders_setup_blade_when_frontend_json_missing(): void
    {
        rename($this->frontendJson, $this->frontendJson.'.bak');

        try {
            $response = $this->get('/');
            $response->assertStatus(200);
            $response->assertViewIs('setup');
        } finally {
            rename($this->frontendJson.'.bak', $this->frontendJson);
        }
    }

    public function test_renders_inertia_when_framework_is_set(): void
    {
        file_put_contents($this->frontendJson, json_encode(['framework' => 'vue']));

        $response = $this->get('/');

        $response->assertStatus(200);
        // The setup view is not rendered — Inertia took over
        $response->assertViewMissing('setup');
    }

    public function test_setup_page_shows_vue_and_react_commands(): void
    {
        file_put_contents($this->frontendJson, json_encode(['framework' => null]));

        $response = $this->get('/');

        $response->assertSeeText('saucebase:stack vue');
        $response->assertSeeText('saucebase:stack react');
    }
}
