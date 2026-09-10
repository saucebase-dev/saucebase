<?php

namespace App\Providers\Filament;

use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Openplain\FilamentShadcnTheme\Color;
use Saucebase\Core\Filament\Admin\GeneralSettings;
use Saucebase\Core\Filament\Admin\LocalizationSettings;
use Saucebase\Core\Filament\ModulesPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth('full')
            ->unsavedChangesAlerts()
            ->userMenuItems([
                Action::make('site')
                    ->label('Site')
                    ->url(fn (): string => route('index'))
                    ->icon('heroicon-o-globe-alt')
                    ->openUrlInNewTab(),
                Action::make('dashboard')
                    ->label('User Dashboard')
                    ->url(fn (): string => route('dashboard'))
                    ->icon('heroicon-o-home')
                    ->openUrlInNewTab(),
            ])
            ->colors([
                'primary' => Color::Default,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            // Core's own settings pages are named rather than discovered: they live in
            // vendor/, where Filament's directory scan does not reach. Removing one is a
            // deleted line here, which is the escape hatch for anybody rebranding.
            // TODO: Consider adding a mechanism to allow discovery of core settings pages if needed.
            ->pages([
                Dashboard::class,
                GeneralSettings::class,
                LocalizationSettings::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                'role:admin',
            ])
            ->plugins([
                ModulesPlugin::make(),
            ])
            ->default();
    }
}
