<?php

namespace Modules\Auth\Console\Commands;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

class MakeAdminCommand extends Command
{
    protected $signature = 'auth:make-admin
                            {email? : Email address for the admin user}
                            {--name= : Display name}
                            {--password= : Password (plain text, will be hashed)}';

    protected $description = 'Create or promote a user to admin';

    public function handle(): int
    {
        $email = $this->argument('email') ?? text('Email address', required: true);
        $name  = $this->option('name')    ?? text('Display name', required: true);

        $plainPassword = $this->option('password');
        $generated = false;

        if (! $plainPassword) {
            if ($this->input->isInteractive()) {
                $plainPassword = password('Password (leave blank to auto-generate)');
            }
            if (! $plainPassword) {
                $plainPassword = Str::password(12, symbols: false);
                $generated = true;
            }
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'password'          => Hash::make($plainPassword),
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([Role::ADMIN->value]);

        $verb = $user->wasRecentlyCreated ? 'Created' : 'Promoted';
        $this->info("{$verb} admin: {$user->name} <{$user->email}>");

        if ($generated && $user->wasRecentlyCreated) {
            $this->line("  Password: {$plainPassword}");
        }

        return self::SUCCESS;
    }
}
