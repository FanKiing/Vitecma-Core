<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class TechnicianFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'identifier' => 'TECH' . $this->faker->unique()->numberBetween(100, 999),
            'password' => Hash::make('password'), // كلمة مرور افتراضية
            'is_active' => $this->faker->boolean(90), // 90% نشط
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}