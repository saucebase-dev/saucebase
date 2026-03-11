<?php

namespace Modules\Auth\Tests\Unit;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Modules\Auth\Exceptions\SocialiteException;
use Modules\Auth\Models\SocialAccount;
use Modules\Auth\Services\SocialiteService;
use Tests\TestCase;

class SocialiteServiceTest extends TestCase
{
    use RefreshDatabase;

    private SocialiteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SocialiteService::class);
    }

    /**
     * Build a fake SocialiteUser with the given attributes.
     */
    private function makeSocialiteUser(
        string $id = 'provider-123',
        string $email = 'socialuser@example.com',
        string $name = 'Social User',
        string $token = 'access-token',
        string $refreshToken = 'refresh-token',
        ?string $avatar = 'https://example.com/avatar.jpg',
    ): SocialiteUser {
        $socialiteUser = new SocialiteUser;
        $socialiteUser->id = $id;
        $socialiteUser->email = $email;
        $socialiteUser->name = $name;
        $socialiteUser->token = $token;
        $socialiteUser->refreshToken = $refreshToken;
        $socialiteUser->avatar = $avatar;

        return $socialiteUser;
    }

    // -----------------------------------------------------------------------
    // linkAccountToUser
    // -----------------------------------------------------------------------

    public function test_link_account_creates_social_account(): void
    {
        $user = $this->createUser();
        $socialiteUser = $this->makeSocialiteUser();

        $this->service->linkAccountToUser($user, 'google', $socialiteUser);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'provider-123',
        ]);
    }

    public function test_link_account_prevents_takeover_when_provider_id_owned_by_other_user(): void
    {
        $otherUser = User::factory()->create();
        SocialAccount::factory()->create([
            'user_id' => $otherUser->id,
            'provider' => 'google',
            'provider_id' => 'provider-123',
        ]);

        $user = $this->createUser();
        $socialiteUser = $this->makeSocialiteUser(id: 'provider-123');

        $this->expectException(SocialiteException::class);

        $this->service->linkAccountToUser($user, 'google', $socialiteUser);
    }

    public function test_link_account_updates_tokens_when_same_user_re_links(): void
    {
        $user = $this->createUser();
        SocialAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'provider-123',
            'provider_token' => 'old-token',
        ]);

        $socialiteUser = $this->makeSocialiteUser(id: 'provider-123', token: 'new-token');

        $this->service->linkAccountToUser($user, 'google', $socialiteUser);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'provider-123',
            'provider_token' => 'new-token',
        ]);
        $this->assertDatabaseCount('social_accounts', 1);
    }

    // -----------------------------------------------------------------------
    // disconnectProvider
    // -----------------------------------------------------------------------

    public function test_disconnect_deletes_provider_account(): void
    {
        $user = $this->createUser();
        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'google']);
        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'github']);

        $user->load('socialAccounts');

        $this->service->disconnectProvider($user, 'google');

        $this->assertDatabaseMissing('social_accounts', ['user_id' => $user->id, 'provider' => 'google']);
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'github']);
    }

    public function test_disconnect_throws_when_only_auth_method_and_no_password(): void
    {
        $user = User::factory()->create(['password' => null]);
        $user->assignRole('user');
        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'google']);

        $user->load('socialAccounts');

        $this->expectException(SocialiteException::class);

        $this->service->disconnectProvider($user, 'google');
    }

    public function test_disconnect_allows_when_user_has_password(): void
    {
        $user = $this->createUser(); // factory sets a password
        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'google']);

        $user->load('socialAccounts');

        $this->service->disconnectProvider($user, 'google');

        $this->assertDatabaseMissing('social_accounts', ['user_id' => $user->id, 'provider' => 'google']);
    }

    public function test_disconnect_throws_when_provider_not_connected(): void
    {
        $user = $this->createUser();
        SocialAccount::factory()->create(['user_id' => $user->id, 'provider' => 'github']);

        $user->load('socialAccounts');

        $this->expectException(SocialiteException::class);

        $this->service->disconnectProvider($user, 'google');
    }

    // -----------------------------------------------------------------------
    // handleCallback
    // -----------------------------------------------------------------------

    public function test_handle_callback_creates_new_user_when_no_match(): void
    {
        Event::fake([Registered::class]);

        $socialiteUser = $this->makeSocialiteUser(
            id: 'new-provider-id',
            email: 'newuser@example.com',
        );

        Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

        $user = $this->service->handleCallback('google');

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'provider_id' => 'new-provider-id',
        ]);
        Event::assertDispatched(Registered::class);
    }

    public function test_handle_callback_returns_existing_user_by_provider_id(): void
    {
        Event::fake([Registered::class]);

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        SocialAccount::factory()->create([
            'user_id' => $existingUser->id,
            'provider' => 'google',
            'provider_id' => 'existing-provider-id',
            'provider_token' => 'old-token',
        ]);

        $socialiteUser = $this->makeSocialiteUser(
            id: 'existing-provider-id',
            email: 'existing@example.com',
            token: 'updated-token',
        );

        Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

        $user = $this->service->handleCallback('google');

        $this->assertEquals($existingUser->id, $user->id);
        $this->assertDatabaseHas('social_accounts', [
            'provider_id' => 'existing-provider-id',
            'provider_token' => 'updated-token',
        ]);
        Event::assertNotDispatched(Registered::class);
    }

    public function test_handle_callback_links_new_social_account_to_existing_email_user(): void
    {
        Event::fake([Registered::class]);

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $socialiteUser = $this->makeSocialiteUser(
            id: 'brand-new-provider-id',
            email: 'existing@example.com',
        );

        Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

        $user = $this->service->handleCallback('google');

        $this->assertEquals($existingUser->id, $user->id);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $existingUser->id,
            'provider' => 'google',
            'provider_id' => 'brand-new-provider-id',
        ]);
        Event::assertNotDispatched(Registered::class);
    }

    public function test_handle_callback_throws_for_unsupported_provider(): void
    {
        $this->expectException(SocialiteException::class);

        $this->service->handleCallback('unsupported-provider');
    }

    public function test_handle_callback_throws_for_invalid_social_user(): void
    {
        $invalidUser = $this->makeSocialiteUser(email: ''); // empty email

        Socialite::shouldReceive('driver->user')->andReturn($invalidUser);

        $this->expectException(SocialiteException::class);

        $this->service->handleCallback('google');
    }
}
