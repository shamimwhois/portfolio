<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => fake()->unique()->sentence(6).'?',
            'answer' => fake()->paragraph(2),
            'sort_order' => 0,
            'publish_status' => Faq::STATUS_PUBLISHED,
        ];
    }
}
