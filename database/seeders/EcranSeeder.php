<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EcranSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'screen')->exists()) {
            return;
        }

        $password = config('seeding.screen_password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->command?->warn('Account screen skipped: set its SEED password to at least 12 characters.');

            return;
        }

        User::forceCreate([
            'name' => 'Écran Salle d’attente',
            'username' => 'screen',
            'password' => Hash::make($password),
            'role' => 'screen',
            'is_admin' => false,
        ]);
    }
}
