<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Saucebase\Core\Filament\Admin\Pages\GeneralSettings;
use Saucebase\Core\Filament\Admin\Pages\LocalizationSettings;
use Saucebase\Core\Filament\Admin\Pages\SeoSettings;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_default_role_is_kept_out_of_the_admin_panel(): void
    {
        $user = User::factory()->create()->assignRole('user');

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_the_admin_role_is_granted_every_ability(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->assertTrue($admin->can('access admin panel'));
        $this->assertTrue($admin->can('an ability nobody declared'));
    }

    public function test_any_role_granted_the_ability_reaches_the_admin_panel(): void
    {
        Role::create(['name' => 'support'])->givePermissionTo('access admin panel');
        $support = User::factory()->create()->assignRole('support');

        $this->assertTrue($support->canAccessPanel(Filament::getPanel('admin')));
        $this->actingAs($support)->get('/admin')->assertOk();
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function siteSettings(): array
    {
        return [
            'general' => [GeneralSettings::class],
            'localization' => [LocalizationSettings::class],
            'seo' => [SeoSettings::class],
        ];
    }

    /** Site-wide settings are their own permission: panel access alone does not open them. */
    #[DataProvider('siteSettings')]
    public function test_site_settings_need_their_own_permission(string $page): void
    {
        Role::create(['name' => 'support'])->givePermissionTo('access admin panel');
        Role::create(['name' => 'site manager'])->givePermissionTo(['access admin panel', 'manage settings']);

        $this->actingAs(User::factory()->create()->assignRole('support'))->get($page::getUrl())->assertForbidden();
        $this->actingAs(User::factory()->create()->assignRole('site manager'))->get($page::getUrl())->assertOk();
    }
}
