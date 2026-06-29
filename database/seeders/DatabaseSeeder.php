<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::truncate();
        // استدعاء ملفات الـ Seeders بالترتيب الاحترافي
        $this->call([
            AdminSeeder::class,      // أولاً: إنشاء حساب الأدمن وتنظيف الجدول
            EcranSeeder::class,      // ثانياً: إضافة حساب الشاشة
            InspectionSeeder::class, // ثالثاً: حقن بيانات الفحوصات والسيارات
        ]);
    }
}