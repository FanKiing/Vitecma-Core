<?php

namespace Database\Seeders;

use App\Models\Technician;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TechnicianSeeder extends Seeder
{
    public function run(): void
    {
        $technicians = [
            [
                'name' => 'ABDELKABIR ISKAF',
                'identifier' => '1551/17',
                'password' => Hash::make('155'),
                'is_active' => true,
            ],
            [
                'name' => 'YOUSSEF BOUSSOSSINE',
                'identifier' => '2101/22',
                'password' => Hash::make('101'),
                'is_active' => true,
            ],
            [
                'name' => 'TAWFIK KHADIR',
                'identifier' => '657/10',
                'password' => Hash::make('/10'),
                'is_active' => true,
            ],
        ];

        foreach ($technicians as $tech) {
            Technician::create($tech);
        }
    }
}