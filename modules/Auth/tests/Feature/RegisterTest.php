<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Modules\Auth\Notifications\WelcomeNotification;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_renders_for_guests(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_user_can_register_with_valid_data(): void
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_registered_user_is_assigned_user_role(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_welcome_notification_is_sent_on_registration(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Notification::assertSentTo($user, WelcomeNotification::class);
    }

    public function test_password_is_hashed_on_registration(): void
    {
        Notification::fake();

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('password123', $user->getAuthPassword()));
        $this->assertNotEquals('password123', $user->getAuthPassword());
    }

    public function test_register_validates_name_is_required(): void
    {
        $response = $this->post(route('register'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertInvalid('name');
    }

    public function test_register_validates_email_is_required(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'password' => 'password123',
        ]);

        $response->assertInvalid('email');
    }

    public function test_register_validates_email_is_unique(): void
    {
        $existing = User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);

        $response->assertInvalid('email');
    }

    public function test_register_validates_email_format(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertInvalid('email');
    }

    public function test_register_validates_password_is_required(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response->assertInvalid('password');
    }
}
