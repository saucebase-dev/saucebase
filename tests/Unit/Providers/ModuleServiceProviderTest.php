<?php

namespace Tests\Unit\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Announcements\Providers\AnnouncementsServiceProvider;
use Modules\Blog\Providers\BlogServiceProvider;
use Tests\TestCase;

class ModuleServiceProviderTest extends TestCase
{
    // --- registerTranslations ---

    public function test_loads_translation_namespace_when_lang_directory_exists(): void
    {
        $namespaces = app('translator')->getLoader()->namespaces();

        $this->assertArrayHasKey('announcements', $namespaces);
        $this->assertEquals(module_path('announcements', 'lang'), $namespaces['announcements']);
    }

    public function test_skips_translation_namespace_when_lang_directory_missing(): void
    {
        $namespaces = app('translator')->getLoader()->namespaces();

        $this->assertArrayNotHasKey('blog', $namespaces);
    }

    // --- registerConfig ---

    public function test_merges_module_config_when_config_file_exists(): void
    {
        $this->assertNotNull(config('blog'));
        $this->assertEquals('Blog', config('blog.name'));
    }

    public function test_registers_config_for_publishing(): void
    {
        $paths = ServiceProvider::pathsToPublish(BlogServiceProvider::class, 'config/config.php');

        $this->assertArrayHasKey(module_path('blog', 'config/config.php'), $paths);
        $this->assertEquals(config_path('blog.php'), $paths[module_path('blog', 'config/config.php')]);
    }

    // --- registerPublicAssets ---

    public function test_registers_assets_publish_path_when_resources_assets_exists(): void
    {
        $paths = ServiceProvider::pathsToPublish(BlogServiceProvider::class, 'module-assets');

        $this->assertArrayHasKey(module_path('blog', 'resources/assets'), $paths);
        $this->assertEquals(public_path('modules/blog'), $paths[module_path('blog', 'resources/assets')]);
    }

    public function test_skips_assets_registration_when_resources_assets_missing(): void
    {
        $paths = ServiceProvider::pathsToPublish(AnnouncementsServiceProvider::class, 'module-assets');

        $this->assertEmpty($paths);
    }
}
