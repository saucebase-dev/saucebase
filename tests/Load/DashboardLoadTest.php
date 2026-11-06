<?php

namespace Tests\Load;

use App\Filament\Admin\Widgets\UsersWidget;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Filament\Widgets\BillingAttentionWidget;
use Modules\Billing\Filament\Widgets\BillingHealthWidget;
use Modules\Billing\Filament\Widgets\BillingSaasStatsWidget;
use Modules\Billing\Filament\Widgets\ConversionChartWidget;
use Modules\Billing\Filament\Widgets\RevenueByPlanChartWidget;
use Modules\Billing\Filament\Widgets\RevenueChartWidget;
use Modules\Billing\Filament\Widgets\SubscriptionsChartWidget;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Price;
use Modules\Packages\Filament\Widgets\PackageInstallsWidget;
use Modules\Packages\Models\Package;
use Modules\Tenancy\Filament\Widgets\WorkspacesWidget;
use Tests\TestCase;

/**
 * Times every dashboard widget over 12 months of a busy app, with the
 * dashboard indexes and then without them. Skipped unless LOAD_TEST=1:
 *
 *   LOAD_TEST=1 php artisan test tests/Load
 *
 * Runs on the test database (SQLite in memory): compare the two columns, not
 * the absolute numbers, with a production MySQL or PostgreSQL.
 */
class DashboardLoadTest extends TestCase
{
    use RefreshDatabase;

    private const USERS = 20_000;

    private const SUBSCRIPTIONS = 50_000;

    private const PAYMENTS = 200_000;

    private const CHECKOUTS = 50_000;

    private const INSTALLS = 200_000;

    /** Each widget must render within this, with indexes, on the 12-month range. */
    private const BUDGET_MS = 1000;

    /** @var list<class-string> */
    private const WIDGETS = [
        UsersWidget::class, WorkspacesWidget::class, BillingSaasStatsWidget::class, PackageInstallsWidget::class,
        BillingAttentionWidget::class, BillingHealthWidget::class, RevenueChartWidget::class,
        SubscriptionsChartWidget::class, ConversionChartWidget::class, RevenueByPlanChartWidget::class,
    ];

    /** @var array<string, list<string>> the indexes this story added */
    private const INDEXES = [
        'payments' => ['payments_status_currency_created_at_index'],
        'subscriptions' => ['subscriptions_created_at_index', 'subscriptions_cancelled_at_index'],
        'checkout_sessions' => ['checkout_sessions_status_created_at_index'],
        'package_downloads' => ['package_downloads_event_created_at_index'],
    ];

    protected function setUp(): void
    {
        if (! getenv('LOAD_TEST')) {
            $this->markTestSkipped('Set LOAD_TEST=1 to run.');
        }

        parent::setUp();

        Carbon::setTestNow('2026-11-06 15:00:00');
        $admin = User::factory()->create(['created_at' => '2024-01-01']);
        $admin->assignRole('admin');
        $this->actingAs($admin);
    }

    public function test_the_dashboard_widgets_stay_fast_with_a_year_of_data(): void
    {
        $seconds = microtime(true);
        $this->seedYear();
        fwrite(STDERR, sprintf("\nSeeded in %.1fs\n", microtime(true) - $seconds));

        $indexed = $this->timeWidgets();
        foreach (self::INDEXES as $table => $indexes) {
            Schema::table($table, fn ($t) => array_map(fn ($index) => $t->dropIndex($index), $indexes));
        }
        $unindexed = $this->timeWidgets();

        fwrite(STDERR, sprintf("\n%-28s %10s %8s %10s %12s\n", 'Widget (12 months)', 'cold', 'queries', 'cached', 'no indexes'));
        foreach ($indexed as $widget => [$ms, $queries, $warm]) {
            fwrite(STDERR, sprintf("%-28s %8.0fms %8d %8.0fms %10.0fms\n", class_basename($widget), $ms, $queries, $warm, $unindexed[$widget][0]));
        }

        foreach ($indexed as $widget => [$ms]) {
            $this->assertLessThan(self::BUDGET_MS, $ms, class_basename($widget).' is over budget');
        }
    }

    /** @return array<class-string, array{float, int, float}> cold ms (best of three) and queries, then cached ms */
    private function timeWidgets(): array
    {
        $times = [];

        foreach (self::WIDGETS as $widget) {
            $runs = [];
            foreach (range(1, 3) as $run) {
                Cache::flush(); // cold: what the first visit in five minutes costs
                DB::flushQueryLog();
                DB::enableQueryLog();
                $start = hrtime(true);
                Livewire::test($widget, ['pageFilters' => ['period' => '12m']]);
                $runs[] = [(hrtime(true) - $start) / 1e6, count(DB::getQueryLog())];
                DB::disableQueryLog();
            }
            $start = hrtime(true);
            Livewire::test($widget, ['pageFilters' => ['period' => '12m']]); // warm: cached by the last cold run
            $times[$widget] = [...collect($runs)->sortBy(0)->first(), (hrtime(true) - $start) / 1e6];
        }

        return $times;
    }

    private function seedYear(): void
    {
        $at = fn (): string => Carbon::now()->subSeconds(random_int(0, 2 * 365 * 86400))->toDateTimeString();

        $this->bulk('users', self::USERS, fn (int $i) => ['name' => "User {$i}", 'email' => "load{$i}@example.test", 'password' => 'x', 'created_at' => $at(), 'updated_at' => now()]);
        $this->bulk('tenants', 2_000, fn (int $i) => ['id' => (string) str()->ulid(), 'name' => "Workspace {$i}", 'slug' => "load-{$i}", 'domain' => "load-{$i}.example.test", 'created_at' => $at(), 'updated_at' => now()]);

        $customer = Customer::factory()->create();
        $prices = collect([['month', 1900], ['month', 4900], ['year', 49000]])
            ->map(fn ($p) => Price::factory()->create(['interval' => $p[0], 'amount' => $p[1], 'currency' => Currency::default()])->id)->all();

        $this->bulk('subscriptions', self::SUBSCRIPTIONS, function (int $i) use ($customer, $prices, $at): array {
            $created = $at();
            $cancelled = random_int(1, 5) === 1 ? Carbon::parse($created)->addDays(random_int(1, 300))->min(now())->toDateTimeString() : null;

            return [
                'customer_id' => $customer->id, 'price_id' => $prices[$i % 3], 'provider' => 'stripe', 'provider_subscription_id' => "sub_load_{$i}",
                'status' => $cancelled ? 'cancelled' : (random_int(1, 50) === 1 ? 'past_due' : 'active'),
                'cancelled_at' => $cancelled, 'ends_at' => $cancelled, 'created_at' => $created, 'updated_at' => now(),
            ];
        });

        $this->bulk('payments', self::PAYMENTS, fn (int $i) => [
            'customer_id' => $customer->id, 'price_id' => $prices[$i % 3], 'provider' => 'stripe', 'provider_payment_id' => "pi_load_{$i}",
            'currency' => Currency::default()->value, 'amount' => 1900, 'amount_refunded' => $i % 40 === 0 ? 1900 : 0,
            'status' => match (true) {
                $i % 40 === 0 => 'refunded', $i % 25 === 0 => 'failed', default => 'succeeded'
            },
            'created_at' => $at(), 'updated_at' => now(),
        ]);

        $this->bulk('checkout_sessions', self::CHECKOUTS, fn (int $i) => [
            'uuid' => (string) str()->uuid(), 'price_id' => $prices[$i % 3], 'status' => ['completed', 'abandoned', 'expired', 'pending'][$i % 4],
            'expires_at' => now(), 'created_at' => $at(), 'updated_at' => now(),
        ]);

        $package = Package::factory()->create();
        $this->bulk('package_downloads', self::INSTALLS, fn (int $i) => [
            'event' => $i % 3 ? 'install' : 'download', 'package_id' => $package->id, 'version' => '1.0.0', 'created_at' => $at(),
        ]);
    }

    /** @param  callable(int): array<string, mixed>  $row */
    private function bulk(string $table, int $count, callable $row): void
    {
        foreach (array_chunk(range(1, $count), 500) as $chunk) {
            DB::table($table)->insert(array_map($row, $chunk));
        }
    }
}
