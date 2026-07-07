<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class CreateAdminCommand extends Command
{
    protected $signature   = 'it-ticketing:create-admin';
    protected $description = 'Create an admin user interactively';

    public function handle(): int
    {
        $this->info('Creating a new admin user...');

        // Ensure roles exist
        foreach (['admin', 'agent', 'user'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $name  = $this->ask('Full name');
        $email = $this->ask('Email address');

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email [{$email}] already exists.");
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 8 chars)');

        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters.');
            return self::FAILURE;
        }

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => Hash::make($password),
        ]);

        $user->assignRole('admin');

        $this->info("✓ Admin user [{$email}] created successfully.");

        return self::SUCCESS;
    }
}