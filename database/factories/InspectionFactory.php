<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $category  = $this->faker->randomElement(['VL', 'PL']);
        $duration  = $category === 'VL' ? 20 : 30; // دقائق الفحص حسب الفئة

        $status = $this->faker->randomElement(['libre', 'en_cours', 'valider', 'imprimer']);

        // started_at — مطلوب بعد libre (منطق updateStatus: يُضاف عند en_cours)
        $startedAt = null;
        if (in_array($status, ['en_cours', 'valider', 'imprimer'])) {
            $startedAt = $status === 'en_cours'
                ? now()->subMinutes($this->faker->numberBetween(1, $duration - 1))   // الفحص جارٍ
                : now()->subMinutes($this->faker->numberBetween($duration, $duration + 60)); // الفحص انتهى
        }

        // result — مطلوب فقط عند valider (منطق updateStatus)
        $result = null;
        if ($status === 'valider') {
            $result = $this->faker->randomElement(['favorable', 'defavorable']);
        }

        // archived_at — يُضاف عند imprimer فقط (منطق updateStatus: $archivedAt = now())
        $archivedAt = null;
        if ($status === 'imprimer') {
            $archivedAt = now()->subMinutes($this->faker->numberBetween(1, 120));
        }

        return [
            'plate_number' => $this->faker->numberBetween(1000, 99999)
                . ' | ' . $this->faker->randomElement(['أ', 'ب', 'د', 'هـ'])
                . ' | ' . $this->faker->numberBetween(1, 88),

            'owner_name'  => $this->faker->name(),

            'category'    => $category,

            'status'      => $status,

            'result'      => $result,

            'started_at'  => $startedAt,

            'archived_at' => $archivedAt,
        ];
    }
}