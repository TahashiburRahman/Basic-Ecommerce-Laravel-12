<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateAdminUser extends Command
{
    protected $signature = 'create:admin {--name= : Admin name} {--email= : Admin email} {--password= : Admin password}';

    protected $description = 'Create or promote an administrator account';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Admin name');
        $email = $this->option('email') ?: $this->ask('Admin email');
        $password = $this->option('password') ?: $this->secret('Admin password');

        if (empty($name) || empty($email) || empty($password)) {
            $this->error('Name, email, and password are required.');

            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $user->name = $name;
        $user->password = Hash::make($password);
        $user->is_admin = true;
        $user->email_verified_at = now();
        $user->remember_token = Str::random(60);
        $user->save();

        $this->info('Administrator account created successfully.');
        $this->info('Email: ' . $user->email);

        return self::SUCCESS;
    }
}
