<?php

namespace Modules\Billing\Providers;

use App\Providers\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Billing\Console\ExpireCheckoutSessionsCommand;
use Modules\Billing\Contracts\PaymentGatewayInterface;
use Modules\Billing\Services\BillingService;
use Modules\Billing\Services\PaymentGatewayManager;

class BillingServiceProvider extends ModuleServiceProvider
{
    protected array $commands = [
        ExpireCheckoutSessionsCommand::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(PaymentGatewayManager::class);

        $this->app->bind(PaymentGatewayInterface::class, function ($app) {
            return $app->make(PaymentGatewayManager::class)->driver();
        });

        $this->app->singleton(BillingService::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadViewsFrom(module_path('billing', 'resources/views'), 'billing');
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        parent::registerConfig();

        $this->mergeConfigFrom(module_path('billing', 'config/services.php'), 'services');
    }

    protected function configureSchedules(Schedule $schedule): void
    {
        $schedule->command('billing:expire-checkout-sessions')->everyThirtyMinutes();
    }
}
