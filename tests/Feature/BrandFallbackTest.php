<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Saucebase\Core\Settings\GeneralSettings;
use Tests\TestCase;

/**
 * Core stores only what the owner uploaded; the app decides what stands in for the
 * rest. The favicon links are rendered by the root view, so its fallback is tested
 * here — the logo component's lives in resources/js/lib/settings.ts.
 */
class BrandFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_favicons_fall_back_to_the_apps_artwork_when_nothing_is_uploaded(): void
    {
        $this->get('/')
            ->assertSee('<link rel="icon" href="/images/icon-on-light.svg">', false)
            ->assertSee('<link rel="icon" href="/images/icon-on-dark.svg" media="(prefers-color-scheme: dark)">', false)
            ->assertSee('<link rel="apple-touch-icon" href="/images/icon-on-light.svg">', false);
    }

    public function test_an_uploaded_icon_replaces_the_fallback(): void
    {
        $brand = app(GeneralSettings::class);
        $brand->site_icon_on_light = 'site-branding/acme.svg';
        $brand->save();

        $this->get('/')
            ->assertSee('<link rel="icon" href="'.Storage::disk('public')->url('site-branding/acme.svg').'">', false)
            ->assertSee('<link rel="icon" href="/images/icon-on-dark.svg" media="(prefers-color-scheme: dark)">', false);
    }
}
