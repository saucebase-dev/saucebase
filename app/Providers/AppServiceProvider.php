<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Saucebase\Core\Sitemap\SitemapRegistry;
use Spatie\Sitemap\Sitemap;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * // TODO: change this to a decorator in each route?
     */
    public function boot(SitemapRegistry $sitemap): void
    {
        $sitemap->add(fn (Sitemap $sitemap) => $sitemap
            ->add(route('index'))
            ->add(route('privacy'))
            ->add(route('terms')));
    }
}
