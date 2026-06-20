<?php

namespace Modules\Auth\Tests\Feature;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MakeAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_promotes_existing_user_to_admin(): void
    {
        $user = $this->createUser();

        $this->artisan('auth:make-admin', ['email' => $user->email])
            ->assertSuccessful()
            ->expectsOutputToContain('Promoted admin');

        $this->assertTrue($user->fresh()->hasRole(Role::ADMIN));
    }

    public function test_fails_when_user_not_found(): void
    {
        $this->artisan('auth:make-admin', ['email' => 'nobody@example.com'])
            ->assertFailed()
            ->expectsOutputToContain('No user found');
    }

    public function test_replaces_existing_role_with_admin(): void
    {
        $user = $this->createUser();
        $user->assignRole(Role::USER);

        $this->artisan('auth:make-admin', ['email' => $user->email])
            ->assertSuccessful();

        $this->assertTrue($user->fresh()->hasRole(Role::ADMIN));
        $this->assertFalse($user->fresh()->hasRole(Role::USER));
    }

    public function test_is_idempotent(): void
    {
        $user = $this->createUser();

        $this->artisan('auth:make-admin', ['email' => $user->email])->assertSuccessful();
        $this->artisan('auth:make-admin', ['email' => $user->email])->assertSuccessful();

        $this->assertCount(1, $user->fresh()->roles);
        $this->assertTrue($user->fresh()->hasRole(Role::ADMIN));
    }
}
