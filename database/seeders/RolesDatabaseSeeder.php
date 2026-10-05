<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesDatabaseSeeder extends Seeder
{
    /**
     * The platform roles and permissions.
     *
     * `admin` needs no permissions: it passes every check (see AppServiceProvider). `user`
     * is the role every sign-up gets. Modules create their own permissions (`manage blog`,
     * …) when `modules:seed` runs, so grant those after it, e.g.
     * `Role::findOrCreate('blog admin')->syncPermissions(['access admin panel', 'manage blog'])`.
     */
    public function run(): void
    {
        Permission::findOrCreate('access admin panel');
        Permission::findOrCreate('manage settings');

        Role::findOrCreate('admin');
        Role::findOrCreate('user');
    }
}
