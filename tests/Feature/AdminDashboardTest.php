<?php

namespace Tests\Feature;

use App\Filament\Admin\Widgets\UsersWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Saucebase\Core\Filament\Pages\Dashboard;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-06 15:00:00');
        $admin = User::factory()->create(['created_at' => '2025-01-01']);
        $admin->assignRole('admin');
        $this->actingAs($admin);
    }

    public function test_the_dashboard_has_the_period_picker_and_no_filament_branding(): void
    {
        $this->get(Dashboard::getUrl())->assertOk()->assertDontSee('filamentphp.com');

        $widgets = Filament::getPanel('admin')->getWidgets();
        $this->assertNotContains(FilamentInfoWidget::class, $widgets);
        $this->assertNotContains(AccountWidget::class, $widgets);
        $this->assertContains(UsersWidget::class, $widgets);
    }

    public function test_the_period_starts_at_30_days(): void
    {
        Livewire::test(Dashboard::class)->assertActionExists('filter', fn ($action): bool => $action->getLabel() === 'Last 30 days');
    }

    /** Widgets are ordered by band (10 stats, 20 attention, 30 charts); users open the stats. */
    public function test_users_come_first(): void
    {
        $sorts = collect(Filament::getPanel('admin')->getWidgets())
            ->mapWithKeys(fn (string $widget) => [$widget => $widget::getSort()]);

        $this->assertSame(10, $sorts[UsersWidget::class]);
        $this->assertSame($sorts->count(), $sorts->unique()->count(), 'Two widgets share a sort: '.$sorts->duplicates()->keys()->implode(', '));
    }

    /** Like every other widget, it asks for the permission behind its own admin page. */
    public function test_users_show_only_to_user_managers(): void
    {
        Permission::findOrCreate('access admin panel');
        Permission::findOrCreate('manage users');

        $this->actingAs(User::factory()->create()->givePermissionTo('access admin panel'));
        $this->assertFalse(UsersWidget::canView());

        $this->actingAs(User::factory()->create()->givePermissionTo('access admin panel', 'manage users'));
        $this->assertTrue(UsersWidget::canView());
    }

    public function test_users_show_signups_against_the_previous_period(): void
    {
        User::factory()->count(3)->create(['created_at' => '2026-11-05']);
        User::factory()->create(['created_at' => '2026-10-28']); // previous 7 days

        Livewire::test(UsersWidget::class, ['pageFilters' => ['period' => '7d']])
            ->assertSee('New users')
            ->assertSee('+200% vs previous period')
            ->assertSee('Total users');
    }
}
