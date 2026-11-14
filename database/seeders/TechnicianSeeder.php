<?php

namespace Database\Seeders;

use App\Models\Technician;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TechnicianSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('seeding.technician_password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->command?->warn('Demo technicians skipped: set SEED_TECHNICIAN_PASSWORD (12 characters minimum).');

            return;
        }

        $technicians = [
            [
                'name' => 'ABDELKABIR ISKAF',
                'identifier' => '1551/17',
                'password' => Hash::make($password),
                'is_active' => true,
            ],
            [
                'name' => 'YOUSSEF BOUSSOSSINE',
                'identifier' => '2101/22',
                'password' => Hash::make($password),
                'is_active' => true,
            ],
            [
                'name' => 'TAWFIK KHADIR',
                'identifier' => '657/10',
                'password' => Hash::make($password),
                'is_active' => true,
            ],
            [
                'name' => 'AYOUB JADDI',
                'identifier' => '1550/17',
                'password' => Hash::make($password),
                'is_active' => true,
            ],

        ];

        foreach ($technicians as $tech) {
            Technician::firstOrCreate(
                ['identifier' => $tech['identifier']],
                $tech
            );
        }
    }
}
