<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\Technician;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        // التأكد من وجود تقنيين
        if (Technician::count() === 0) {
            $this->call(TechnicianSeeder::class);
        }

        // 1. فحوصات Libre (بدون تقني ولا ممر)
        Inspection::factory()
            ->count(5)
            ->create([
                'status' => 'libre',
                'started_at' => null,
                'technician_id' => null,
                'technician_name' => null,
                'lane' => null,
            ]);

        // 2. فحوصات En cours (مع تقني وممر)
        Inspection::factory()
            ->count(3)
            ->create([
                'status' => 'en_cours',
                'started_at' => Carbon::now()->subMinutes(5),
            ]);

        // 3. فحوصات Valider (نتائج متنوعة)
        Inspection::factory()
            ->count(4)
            ->create([
                'status' => 'valider',
                'started_at' => Carbon::now()->subMinutes(30),
            ]);

        // 4. فحوصات Imprimer (مؤرشفة)
        Inspection::factory()
            ->count(3)
            ->create([
                'status' => 'imprimer',
                'started_at' => Carbon::now()->subMinutes(45),
                'archived_at' => Carbon::now()->subMinutes(10),
            ]);

        // 5. فحوصات مع تقني محدد (للاختبار)
        $tech = Technician::inRandomOrder()->first();
        if ($tech) {
            Inspection::factory()
                ->count(2)
                ->create([
                    'status' => 'en_cours',
                    'technician_id' => $tech->id,
                    'technician_name' => $tech->name,
                    'lane' => 'VL2',
                ]);
        }
    }
}