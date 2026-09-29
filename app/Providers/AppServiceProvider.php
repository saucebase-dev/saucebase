<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
     */
    public function boot(SitemapRegistry $sitemap): void
    {
        // Admins pass every can()/policy check. Others get null, so their own
        // permissions decide (false would deny everyone). hasPermissionTo() skips this.
        Gate::before(fn (User $user): ?bool => $user->hasRole('admin') ? true : null);

        $sitemap->add(fn (Sitemap $sitemap) => $sitemap
            ->add(route('index'))
            ->add(route('privacy'))
            ->add(route('terms')));
    }
}
