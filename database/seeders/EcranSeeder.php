<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class EcranSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'     => "Ecran Salle d'attente",
            'email'    => 'ecran@vitecma.com',
            'password' => Hash::make('vitecma2026'), // 💡 التشفير مهم جداً هنا
            'role'     => 'screen',
            'is_admin' => false,
        ]);
    }
}