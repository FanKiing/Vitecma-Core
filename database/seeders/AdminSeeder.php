<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'admin')->exists()) {
            return;
        }

        $password = config('seeding.admin_password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->command?->warn('Account admin skipped: set its SEED password to at least 12 characters.');

            return;
        }

        User::forceCreate([
            'name' => 'Admin Vitecma',
            'username' => 'admin',
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_admin' => true,
        ]);
    }
}
