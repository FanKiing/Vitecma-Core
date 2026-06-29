<?php

namespace Database\Seeders;

use App\Models\Inspection;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Inspection::factory(3)->create([
            'status' => 'libre',
            'started_at' => null,
        ]);


        Inspection::factory()->create([
            'category' => 'VL',
            'status' => 'en_cours',
            'started_at' => now()->subMinutes(5), 
        ]);

        Inspection::factory()->create([
            'category' => 'PL',
            'status' => 'en_cours',
            'started_at' => now()->subMinutes(10), 
        ]);

        Inspection::factory(2)->create([
            'status' => 'valider',
            'started_at' => now()->subMinutes(30),
        ]);

        Inspection::factory(2)->create([
            'status' => 'imprimer',
            'started_at' => now()->subMinutes(60),
        ]);
    }
}