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
            'email'    => 'admin@vitecma.ma',
            'password' => Hash::make('admin2026'), // 💡 التشفير مهم جداً هنا
            'role'     => 'admin',
            'is_admin' => true,
        ]);
    }
}