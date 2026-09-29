<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesDatabaseSeeder extends Seeder
{
    /**
     * The platform roles and the permissions each is granted.
     *
     * `admin` needs none: it passes every check (see AppServiceProvider). `user` is the
     * role every sign-up gets. Add a role here, or at runtime with
     * `php artisan permission:create-role`.
     *
     * @var array<string, list<string>>
     */
    private const array ROLES = [
        'admin' => [],
        'user' => [],
    ];

    /** @var list<string> */
    private const array PERMISSIONS = [
        'access admin panel',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (self::ROLES as $role => $permissions) {
            Role::findOrCreate($role)->syncPermissions($permissions);
        }
    }
}
