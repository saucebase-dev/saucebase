<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\SocialAccount;
use Tests\TestCase;

class SocialiteDisconnectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_disconnect_a_provider(): void
    {
        $user = $this->createUser();

        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'google']);
        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'github']);

        $response = $this->actingAs($user)
            ->delete(route('auth.socialite.disconnect', ['provider' => 'google']));

        $response->assertRedirect();
        $this->assertDatabaseMissing('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
        ]);
    }

    public function test_user_can_disconnect_when_they_have_a_password(): void
    {
        $user = $this->createUser(); // has hashed 'password'

        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'google']);

        $response = $this->actingAs($user)
            ->delete(route('auth.socialite.disconnect', ['provider' => 'google']));

        $response->assertRedirect();
        $this->assertDatabaseMissing('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);
    }

    public function test_cannot_disconnect_only_auth_method_without_password(): void
    {
        // User with no password (social-only login)
        $user = User::factory()->create(['password' => null]);
        $user->assignRole('user');

        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'google']);

        $response = $this->actingAs($user)
            ->delete(route('auth.socialite.disconnect', ['provider' => 'google']));

        $response->assertRedirect();
        // Social account should still exist
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
        ]);
    }

    public function test_cannot_disconnect_unconnected_provider(): void
    {
        $user = $this->createUser();

        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'github']);

        $response = $this->actingAs($user)
            ->delete(route('auth.socialite.disconnect', ['provider' => 'google']));

        $response->assertRedirect();
        // Github account should still be intact
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'github',
        ]);
    }

    public function test_guest_cannot_disconnect(): void
    {
        $response = $this->delete(route('auth.socialite.disconnect', ['provider' => 'google']));

        $response->assertRedirect(route('login'));
    }
}
