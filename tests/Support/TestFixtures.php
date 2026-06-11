<?php

namespace Tests\Support;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestFixtures
{
    public static function credentials(): array
    {
        $password = Str::password(12, symbols: false);

        $admin = User::factory()->create([
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles([Role::ADMIN->value]);

        $user = User::factory()->create([
            'password'          => Hash::make($password),
            'email_verified_at' => now(),
        ]);
        $user->syncRoles([Role::USER->value]);

        // Fixed emails — BillingTestHelper resolves these same users via firstOrCreate
        $subscriber = User::firstOrCreate(
            ['email' => 'subscriber@example.com'],
            ['name' => 'Subscriber User', 'email_verified_at' => now()]
        );
        $subscriber->update(['password' => Hash::make($password)]);
        $subscriber->syncRoles([Role::USER->value]);

        $cancelled = User::firstOrCreate(
            ['email' => 'cancelled@example.com'],
            ['name' => 'Cancelled User', 'email_verified_at' => now()]
        );
        $cancelled->update(['password' => Hash::make($password)]);
        $cancelled->syncRoles([Role::USER->value]);

        return [
            'admin'      => ['email' => $admin->email,       'password' => $password],
            'user'       => ['email' => $user->email,         'password' => $password],
            'subscriber' => ['email' => $subscriber->email,   'password' => $password],
            'cancelled'  => ['email' => $cancelled->email,    'password' => $password],
        ];
    }
}
