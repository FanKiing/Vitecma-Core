<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => 'Admin Vitecma',
            'username' => 'admin',  // نستخدم username كمعرف
            'password' => Hash::make('admin2026'),
            'role'     => 'admin',
            'is_admin' => true,
        ]);
    }
}