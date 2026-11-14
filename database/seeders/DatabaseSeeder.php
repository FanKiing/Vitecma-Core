<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Add missing accounts without deleting data or resetting passwords.
        $this->call([AdminSeeder::class, EcranSeeder::class]);

        if (app()->environment('local') && config('seeding.demo_data')) {
            $this->call([TechnicianSeeder::class, InspectionSeeder::class]);
        }
    }
}
