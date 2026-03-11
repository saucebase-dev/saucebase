<?php

namespace Modules\Auth\Tests\Feature;

use App\Notifications\PasswordChangedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_password_with_correct_current_password(): void
    {
        Notification::fake();

        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->getAuthPassword()));
    }

    public function test_password_changed_notification_is_sent(): void
    {
        Notification::fake();

        $user = $this->createUser();

        $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        Notification::assertSentTo($user, PasswordChangedNotification::class);
    }

    public function test_update_fails_with_wrong_current_password(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'wrong-password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertInvalid('current_password');
    }

    public function test_update_fails_when_password_not_confirmed(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertInvalid('password');
    }

    public function test_guest_cannot_update_password(): void
    {
        $response = $this->put(route('password.update'), [
            'current_password' => 'password',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
    }
}
