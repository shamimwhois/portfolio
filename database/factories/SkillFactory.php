<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'level' => fake()->numberBetween(30, 100),
            'icon' => strtoupper(fake()->lexify('??')),
            'sort_order' => 0,
            'publish_status' => Skill::STATUS_PUBLISHED,
        ];
    }
}
