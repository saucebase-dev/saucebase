<?php

namespace Modules\Demo\Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Billing\Database\Seeders\BillingDatabaseSeeder;
use Modules\Billing\Models\CheckoutSession;
use Modules\Billing\Models\Customer;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Payment;
use Modules\Billing\Models\PaymentMethod;
use Modules\Billing\Models\Price;
use Modules\Billing\Models\Subscription;
use Modules\Roadmap\Database\Seeders\RoadmapDatabaseSeeder;
use Modules\Roadmap\Enums\VoteType;
use Modules\Roadmap\Models\RoadmapItem;
use Modules\Roadmap\Models\RoadmapVote;

class DemoDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            BillingDatabaseSeeder::class,
            RoadmapDatabaseSeeder::class,
        ]);

        $proMonthly = Price::where('provider_price_id', 'price_1SyadREx2sHJcHgwCt0ReZEJ')->firstOrFail();
        $proYearly = Price::where('provider_price_id', 'price_1SyaCQEx2sHJcHgwEC9VmwSZ')->firstOrFail();
        $teamMonthly = Price::where('provider_price_id', 'price_1SyaL0Ex2sHJcHgwWaaTGLgo')->firstOrFail();
        $teamYearly = Price::where('provider_price_id', 'price_1SyaLWEx2sHJcHgw3fQdYV0J')->firstOrFail();

        $users = User::factory()->count(30)->create();
        foreach ($users as $user) {
            $user->assignRole(Role::USER->value);
        }

        $customers = collect();
        $paymentMethods = collect();
        foreach ($users->take(25) as $user) {
            $customer = Customer::factory()->withAddress()->create(['user_id' => $user->id]);
            $paymentMethod = PaymentMethod::factory()->default()->visa()->create(['customer_id' => $customer->id]);
            $customers->push($customer);
            $paymentMethods->push($paymentMethod);
        }

        // 12 month-start dates, oldest first, for backdating
        $months = collect(range(11, 0))->map(fn ($i) => now()->subMonths($i)->startOfMonth());

        // [customerIndex, price, state]
        $assignments = [
            [0,  $proMonthly,  'active'],
            [1,  $proMonthly,  'active'],
            [2,  $proMonthly,  'active'],
            [3,  $proMonthly,  'active'],
            [4,  $proMonthly,  'active'],
            [5,  $proMonthly,  'active'],
            [6,  $proMonthly,  'active'],
            [7,  $proMonthly,  'active'],
            [8,  $proMonthly,  'active'],
            [9,  $proMonthly,  'active'],
            [10, $proMonthly,  'active'],
            [11, $proMonthly,  'active'],
            [12, $proYearly,   'active'],
            [13, $proYearly,   'active'],
            [14, $proYearly,   'active'],
            [15, $proYearly,   'active'],
            [16, $proYearly,   'active'],
            [17, $teamMonthly, 'active'],
            [18, $teamMonthly, 'active'],
            [19, $teamMonthly, 'active'],
            [20, $teamMonthly, 'active'],
            [21, $teamYearly,  'active'],
            [22, $teamYearly,  'active'],
            [23, $proMonthly,  'onTrial'],
            [24, $proMonthly,  'onTrial'],
            // Cancelled prior subscriptions reusing the same customers
            [0,  $proMonthly,  'cancelled'],
            [1,  $teamMonthly, 'cancelled'],
            [2,  $proYearly,   'cancelled'],
            // Past due
            [3,  $teamMonthly, 'pastDue'],
            [4,  $proMonthly,  'pastDue'],
        ];

        $activeSubscriptions = collect();
        $monthIndex = 0;

        foreach ($assignments as [$customerIndex, $price, $state]) {
            $customer = $customers->get($customerIndex);
            $paymentMethod = $paymentMethods->get($customerIndex);
            $createdAt = $months[$monthIndex % 12];
            $monthIndex++;

            $factory = Subscription::factory()->state([
                'customer_id' => $customer->id,
                'price_id' => $price->id,
                'payment_method_id' => $paymentMethod->id,
            ]);

            $factory = match ($state) {
                'cancelled' => $factory->cancelled(),
                'pastDue' => $factory->pastDue(),
                'onTrial' => $factory->onTrial(),
                default => $factory,
            };

            $subscription = $factory->create(['created_at' => $createdAt]);

            if (in_array($state, ['active', 'onTrial', 'pastDue'])) {
                $customer->user->assignRole(Role::SUBSCRIBER->value);
            }

            if ($state === 'active') {
                $activeSubscriptions->push($subscription);
            }
        }

        // Payments: one per month per active subscription since its creation.
        // This produces a naturally growing monthly revenue trend.
        $activeSubscriptions = Subscription::whereIn('id', $activeSubscriptions->pluck('id'))->with('price')->get();

        foreach ($activeSubscriptions as $subscription) {
            $cursor = Carbon::parse($subscription->created_at)->startOfMonth();
            $nowMonth = now()->startOfMonth();

            while ($cursor <= $nowMonth) {
                $succeeded = fake()->boolean(90);

                $paymentFactory = Payment::factory()->state([
                    'customer_id' => $subscription->customer_id,
                    'subscription_id' => $subscription->id,
                    'price_id' => $subscription->price_id,
                    'amount' => $subscription->price->amount,
                ]);

                if (! $succeeded) {
                    $paymentFactory = $paymentFactory->failed();
                }

                $payment = $paymentFactory->create(['created_at' => $cursor->copy()]);

                if ($succeeded) {
                    Invoice::factory()->create([
                        'customer_id' => $subscription->customer_id,
                        'subscription_id' => $subscription->id,
                        'payment_id' => $payment->id,
                        'subtotal' => $subscription->price->amount,
                        'total' => $subscription->price->amount,
                        'paid_at' => $cursor->copy(),
                        'created_at' => $cursor->copy(),
                    ]);
                }

                $cursor->addMonth();
            }
        }

        // Checkout sessions spread across 12 months for the conversion chart.
        // ~2-3 completed, 1 abandoned, 0-1 expired per month → ~67% conversion rate.
        foreach ($months as $month) {
            CheckoutSession::factory()
                ->completed()
                ->count(fake()->numberBetween(2, 3))
                ->create(['customer_id' => null, 'price_id' => $proMonthly->id, 'created_at' => $month]);

            CheckoutSession::factory()
                ->abandoned()
                ->create(['customer_id' => null, 'price_id' => $proMonthly->id, 'created_at' => $month]);

            if (fake()->boolean(50)) {
                CheckoutSession::factory()
                    ->expired()
                    ->create(['customer_id' => null, 'price_id' => $proMonthly->id, 'created_at' => $month]);
            }
        }

        // Roadmap votes: each demo user votes on 3-6 random items.
        $roadmapItems = RoadmapItem::all();
        foreach ($users as $user) {
            $itemsToVote = $roadmapItems->random(fake()->numberBetween(3, 6));
            foreach ($itemsToVote as $item) {
                RoadmapVote::firstOrCreate(
                    ['roadmap_item_id' => $item->id, 'user_id' => $user->id],
                    ['type' => fake()->randomElement([VoteType::Up, VoteType::Down])]
                );
            }
        }
    }
}
