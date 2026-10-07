<?php

namespace Database\Factories;

use App\Models\TimelinePhase;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelinePhase>
 */
class TimelinePhaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'days' => fake()->randomElement(['Days 01–04', 'Days 05–08', 'Days 09–12']),
            'title' => fake()->unique()->words(3, true),
            'description' => fake()->paragraph(2),
            'sort_order' => 0,
            'publish_status' => TimelinePhase::STATUS_PUBLISHED,
        ];
    }
}
