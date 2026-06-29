<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        \App\Models\User::truncate();
        \App\Models\Technician::truncate();
        \App\Models\Inspection::truncate();

        $this->call([
            AdminSeeder::class,
            EcranSeeder::class,
            TechnicianSeeder::class,
            InspectionSeeder::class,
        ]);
    }
}