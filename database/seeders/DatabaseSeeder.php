<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ✅ تعطيل فحص المفاتيح الخارجية مؤقتاً
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // مسح الجداول بالترتيب (الأبناء أولاً)
        \App\Models\Inspection::truncate();
        \App\Models\Technician::truncate();
        \App\Models\User::truncate();

        // ✅ إعادة تفعيل فحص المفاتيح الخارجية
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // تشغيل الـ Seeders
        $this->call([
            AdminSeeder::class,
            EcranSeeder::class,
            TechnicianSeeder::class,
            InspectionSeeder::class,
        ]);
    }
}