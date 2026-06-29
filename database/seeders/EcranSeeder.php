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
            'username' => 'screen',  // نستخدم username كمعرف
            'password' => Hash::make('vitecma2026'),
            'role'     => 'screen',
            'is_admin' => false,
        ]);
    }
}